<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use App\Services\SaleService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** ការកម្មង់ — ផ្នែក Admin (អនុម័ត / បដិសេធ) */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected', 'cancelled'], true)
            ? $request->input('status') : null;

        $orders = Order::with('user')->withCount('items')->withSum('items as qty', 'quantity')
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->latest('id')->paginate(10)->withQueryString();

        $counts = Order::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.orders.index', compact('orders', 'status', 'counts'));
    }

    public function show(Order $order)
    {
        $order->load('items.book', 'user', 'sale');

        return view('admin.orders.show', compact('order'));
    }

    /** អនុម័តការកម្មង់៖ កាត់ស្តុក + បង្កើតវិក្កយបត្រ ក្នុង transaction តែមួយ */
    private function approveOrder(Order $order, Request $request, SaleService $service): void
    {
        DB::transaction(function () use ($order, $service, $request) {
            // lock ការកម្មង់ ដើម្បីកុំឱ្យអនុម័តពីរដង
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isPending()) {
                throw ValidationException::withMessages(['items' => 'ការកម្មង់នេះត្រូវបានដោះស្រាយរួចហើយ']);
            }

            $items = $locked->items()->get();
            if ($items->contains(fn ($i) => $i->book_id === null)) {
                throw ValidationException::withMessages(['items' => 'មានសៀវភៅក្នុងការកម្មង់នេះត្រូវបានលុបពីប្រព័ន្ធ']);
            }

            $wanted = [];
            foreach ($items as $i) {
                $wanted[$i->book_id] = ($wanted[$i->book_id] ?? 0) + $i->quantity;
            }

            $sale = $service->create($request->user(), $locked->user->name, $wanted, $locked->user_id);

            $locked->update(['status' => Order::APPROVED, 'sale_id' => $sale->id]);
        });
    }

    public function approve(Request $request, Order $order, SaleService $service)
    {
        try {
            $this->approveOrder($order, $request, $service);
        } catch (ValidationException $e) {
            return back()->with('error', $e->errors()['items'][0] ?? 'មិនអាចអនុម័តបានទេ');
        }

        rescue(fn () => $order->user->notify(new OrderStatusChanged($order->fresh(), 'approved')));

        return back()->with('success', 'បានអនុម័ត '.$order->order_no.' ហើយស្តុកត្រូវបានកាត់ + បង្កើតវិក្កយបត្រ។');
    }

    public function reject(Request $request, Order $order)
    {
        $data = $request->validate(['reject_reason' => 'nullable|string|max:300']);

        if (! $order->isPending()) {
            return back()->with('error', 'ការកម្មង់នេះត្រូវបានដោះស្រាយរួចហើយ');
        }

        $order->update(['status' => Order::REJECTED, 'reject_reason' => $data['reject_reason'] ?? null]);

        rescue(fn () => $order->user->notify(new OrderStatusChanged($order->fresh(), 'rejected')));

        return back()->with('success', 'បានបដិសេធ '.$order->order_no);
    }

    /**
     * បញ្ជាក់ថាបានទទួលលុយ។ បើការកម្មង់នៅរង់ចាំ (អតិថិជនបង់ភ្លាមបន្ទាប់ពីកម្មង់)
     * ប្រព័ន្ធនឹងអនុម័តឱ្យតែម្តង៖ កាត់ស្តុក + បង្កើតវិក្កយបត្រ។
     */
    public function markPaid(Request $request, Order $order, SaleService $service)
    {
        if (! in_array($order->status, [Order::PENDING, Order::APPROVED], true)) {
            return back()->with('error', 'មិនអាចបញ្ជាក់ការទូទាត់សម្រាប់ការកម្មង់នេះបានទេ។');
        }
        if ($order->payment_status === 'paid') {
            return back()->with('error', 'ការកម្មង់នេះបានទូទាត់រួចហើយ។');
        }

        $wasPending = $order->isPending();

        if ($wasPending) {
            try {
                $this->approveOrder($order, $request, $service);
            } catch (ValidationException $e) {
                return back()->with('error', ($e->errors()['items'][0] ?? 'មិនអាចអនុម័តបានទេ').' — មិនទាន់បញ្ជាក់ទូទាត់ទេ សូមបន្ថែមស្តុក ឬបដិសេធ ហើយសងប្រាក់អតិថិជនវិញ។');
            }
        }

        $order->update(['payment_status' => 'paid', 'paid_at' => now()]);

        rescue(fn () => $order->user->notify(new OrderStatusChanged($order->fresh(), 'paid')));

        return back()->with('success', $wasPending
            ? 'បានបញ្ជាក់ការទូទាត់ '.$order->order_no.' ហើយអនុម័តដោយស្វ័យប្រវត្តិ (ស្តុកត្រូវកាត់ + បង្កើតវិក្កយបត្រ)។'
            : 'បានបញ្ជាក់ការទូទាត់ '.$order->order_no);
    }

    /** ភស្តុតាងមិនត្រឹមត្រូវ → ត្រឡប់ទៅ «មិនទាន់ទូទាត់» ដើម្បីឱ្យអតិថិជនបញ្ជូនម្តងទៀត */
    public function markUnpaid(Order $order)
    {
        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $order->update(['payment_status' => 'unpaid', 'payment_ref' => null, 'payment_proof' => null, 'paid_at' => null]);

        return back()->with('success', 'បានកំណត់ជា «មិនទាន់ទូទាត់»។ អតិថិជនអាចបញ្ជូនភស្តុតាងម្តងទៀត។');
    }
}
