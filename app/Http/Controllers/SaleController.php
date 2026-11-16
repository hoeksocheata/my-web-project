<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $from   = $request->input('from');
        $to     = $request->input('to');

        $query = Sale::query()
            ->withCount('items')
            ->withSum('items as qty', 'quantity')
            ->with('user')
            ->when($search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('customer_name', 'like', "%{$s}%");
                    // ស្វែងរកតាមលេខវិក្កយបត្រ ឧ. INV-00012 ឬ 12
                    $id = (int) preg_replace('/\D/', '', $s);
                    if ($id > 0) {
                        $q->orWhere('id', $id);
                    }
                });
            })
            ->when($from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        $summary = [
            'count'   => (clone $query)->count(),
            'revenue' => (float) (clone $query)->sum('total'),
        ];

        $sales = $query->latest()->latest('id')->paginate(10)->withQueryString();

        return view('sales.index', compact('sales', 'summary', 'search', 'from', 'to'));
    }

    public function create()
    {
        $books = Book::where('stock', '>', 0)->orderBy('title')->get()
            ->map(fn ($b) => [
                'id' => $b->id, 'title' => $b->title, 'author' => $b->author,
                'price' => (float) $b->price, 'stock' => $b->stock, 'image' => $b->image_url,
            ])->values();

        return view('sales.create', compact('books'));
    }

    public function store(Request $request, SaleService $service)
    {
        $data = $request->validate([
            'customer_name'      => 'nullable|string|max:100',
            'items'              => 'required|array|min:1',
            'items.*.book_id'    => 'required|integer|exists:books,id',
            'items.*.quantity'   => 'required|integer|min:1|max:1000',
        ], [
            'items.required' => 'សូមជ្រើសរើសសៀវភៅយ៉ាងហោចណាស់ ១ ក្បាល',
            'items.min'      => 'សូមជ្រើសរើសសៀវភៅយ៉ាងហោចណាស់ ១ ក្បាល',
        ]);

        // បូកបញ្ចូលគ្នា ប្រសិនបើសៀវភៅដូចគ្នាត្រូវបានបញ្ជូនមកច្រើនដង
        $wanted = [];
        foreach ($data['items'] as $row) {
            $wanted[$row['book_id']] = ($wanted[$row['book_id']] ?? 0) + (int) $row['quantity'];
        }

        $sale = $service->create($request->user(), $data['customer_name'] ?? null, $wanted);

        return redirect()->route('sales.show', $sale)->with('success', 'បានបង្កើតការលក់ '.$sale->invoice_no.' ដោយជោគជ័យ!');
    }

    public function show(Sale $sale)
    {
        $sale->load('items', 'user');
        return view('sales.show', compact('sale'));
    }

    /** បោះបង់ការលក់ ហើយបូកស្តុកត្រឡប់មកវិញ */
    public function destroy(Request $request, Sale $sale, StockService $stock)
    {
        DB::transaction(function () use ($sale, $stock, $request) {
            foreach ($sale->items()->get() as $item) {
                if ($item->book_id && $book = Book::whereKey($item->book_id)->lockForUpdate()->first()) {
                    $stock->change($book, $item->quantity, 'void', $request->user()->id, 'បោះបង់ '.$sale->invoice_no);
                }
            }
            $sale->delete(); // sale_items លុបតាមដោយ cascade
        });

        return redirect()->route('sales.index')->with('success', 'បានបោះបង់ការលក់ ហើយស្តុកត្រូវបានបូកត្រឡប់វិញ។');
    }
}
