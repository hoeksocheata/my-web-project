<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** ទិញចូល / បំពេញស្តុក */
class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with('book', 'supplier', 'user')->latest()->latest('id')->paginate(10);
        $books     = Book::orderBy('title')->get(['id', 'title', 'stock']);
        $suppliers = Supplier::orderBy('name')->get();
        $selected  = (int) $request->input('book');

        return view('purchases.index', compact('purchases', 'books', 'suppliers', 'selected'));
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity'    => 'required|integer|min:1|max:100000',
            'unit_cost'   => 'required|numeric|min:0|max:99999.99',
        ], [
            'book_id.required'  => 'សូមជ្រើសរើសសៀវភៅ',
            'quantity.required' => 'សូមបញ្ចូលចំនួន',
            'unit_cost.required' => 'សូមបញ្ចូលតម្លៃដើម',
        ]);

        DB::transaction(function () use ($data, $request, $stock) {
            $book = Book::whereKey($data['book_id'])->lockForUpdate()->firstOrFail();

            $purchase = Purchase::create($data + ['user_id' => $request->user()->id]);

            $stock->change($book, $data['quantity'], 'restock', $request->user()->id, 'ទិញចូល #'.$purchase->id);
        });

        return redirect()->route('purchases.index')->with('success', 'បានបំពេញស្តុករួចរាល់!');
    }
}
