<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use App\Notifications\PaymentSubmitted;
use App\Services\OwnerAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/** ការកម្មង់ — ផ្នែកអតិថិជន */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')->withSum('items as qty', 'quantity')
            ->latest()->latest('id')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items', 'sale');

        return view('orders.show', compact('order'));
    }

    public function store(Request $request, OwnerAlert $alert)
    {
        $data = $request->validate(['note' => 'nullable|string|max:500'], [
            'note.max' => 'កំណត់ចំណាំវែងពេក (អតិបរមា 500 តួអក្សរ)',
        ]);

        $cart = session('cart', []);
        if (! $cart) {
            return redirect()->route('cart.index')->with('error', 'កន្ត្រករបស់អ្នកទទេ');
        }

        $books = Book::whereIn('id', array_keys($cart))->get()->keyBy('id');

        // ពិនិត្យស្តុកជាមុន (ស្តុកនឹងត្រូវកាត់ពិតប្រាកដ នៅពេល Admin អនុម័ត)
        foreach ($cart as $id => $qty) {
            $book = $books[$id] ?? null;
            if (! $book || $book->stock < $qty) {
                return redirect()->route('cart.index')
                    ->with('error', '«'.($book->title ?? 'សៀវភៅមួយក្បាល').'» មានស្តុកមិនគ្រប់គ្រាន់ សូមកែចំនួនក្នុងកន្ត្រក');
            }
        }

        $order = DB::transaction(function () use ($cart, $books, $data, $request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status'  => Order::PENDING,
                'note'    => $data['note'] ?? null,
                'total'   => 0,
            ]);

            $total = 0;
            foreach ($cart as $id => $qty) {
                $book = $books[$id];
                $order->items()->create([
                    'book_id' => $book->id, 'title' => $book->title, 'price' => $book->price, 'quantity' => $qty,
                ]);
                $total += (float) $book->price * $qty;
            }
            $order->update(['total' => $total]);

            return $order;
        });

        session()->forget('cart');

        // ជូនដំណឹងម្ចាស់ហាងភ្លាមៗ (កណ្ដឹងក្នុងប្រព័ន្ធ + អ៊ីមែល + Telegram)
        $alert->send(
            new NewOrderPlaced($order),
            "🛎️ ការកម្មង់ថ្មី {$order->order_no}\nអតិថិជន៖ {$request->user()->name}\nសរុប៖ $".number_format($order->total, 2)."\n".route('admin.orders.show', $order)
        );

        return redirect()->route('orders.show', $order)
            ->with('success', 'បានបង្កើតការកម្មង់ '.$order->order_no.' ហើយ! សូមស្កេន QR ខាងក្រោមដើម្បីទូទាត់ភ្លាម។');
    }

    /** អតិថិជនបោះបង់ការកម្មង់ ដែលនៅមិនទាន់អនុម័ត */
    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPending()) {
            return back()->with('error', 'អាចបោះបង់បានតែការកម្មង់ដែលកំពុងរង់ចាំប៉ុណ្ណោះ។');
        }

        if ($order->payment_status !== 'unpaid') {
            return back()->with('error', 'អ្នកបានបង់ប្រាក់ហើយ សូមទាក់ទងហាងប្រសិនបើចង់បោះបង់ និងសុំសងប្រាក់វិញ។');
        }

        $order->update(['status' => Order::CANCELLED]);

        return back()->with('success', 'បានបោះបង់ការកម្មង់ '.$order->order_no);
    }

    /** អតិថិជនបញ្ជូនភស្តុតាងទូទាត់ KHQR (លេខប្រតិបត្តិការ និង/ឬ រូបថតអេក្រង់) */
    public function pay(Request $request, Order $order, OwnerAlert $alert)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->canPay()) {
            return back()->with('error', 'ការកម្មង់នេះមិនអាចទូទាត់បានទេ។');
        }

        $data = $request->validate([
            'payment_ref'   => 'nullable|string|max:100',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'payment_proof.image' => 'ឯកសារត្រូវតែជារូបភាព',
            'payment_proof.max'   => 'រូបភាពមានទំហំធំជាង 4MB',
        ]);

        $update = ['payment_status' => 'submitted', 'payment_ref' => $data['payment_ref'] ?? null];

        if ($request->hasFile('payment_proof')) {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $update['payment_proof'] = $request->file('payment_proof')->store('payments', 'public');
        }

        $order->update($update);

        $alert->send(
            new PaymentSubmitted($order),
            "💰 មានអ្នកបង់លុយ {$order->order_no}\nអតិថិជន៖ {$request->user()->name}\nចំនួន៖ $".number_format($order->total, 2)."\n".route('admin.orders.show', $order)
        );

        return back()->with('success', 'អរគុណ! ហាងបានទទួលដំណឹងថាអ្នកបានបង់ហើយ ហើយនឹងរៀបចំការកម្មង់ជូនអ្នកក្នុងពេលឆាប់ៗ។');
    }
}
