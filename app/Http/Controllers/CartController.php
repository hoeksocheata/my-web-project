<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

/** កន្ត្រករបស់អតិថិជន រក្សាទុកក្នុង session: [book_id => quantity] */
class CartController extends Controller
{
    private function cart(): array
    {
        return session('cart', []);
    }

    public function index()
    {
        $cart  = $this->cart();
        $books = Book::with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $lines = [];
        $total = 0;
        foreach ($cart as $id => $qty) {
            if (! isset($books[$id])) {
                continue; // សៀវភៅត្រូវបានលុប
            }
            $book = $books[$id];
            $qty  = min($qty, max($book->stock, 0));
            if ($qty < 1) {
                continue;
            }
            $lines[] = ['book' => $book, 'qty' => $qty, 'subtotal' => (float) $book->price * $qty];
            $total  += (float) $book->price * $qty;
        }

        return view('cart.index', compact('lines', 'total'));
    }

    public function add(Request $request, Book $book)
    {
        if ($book->stock < 1) {
            return back()->with('error', "«{$book->title}» អស់ស្តុក");
        }

        $qty  = max(1, (int) $request->input('quantity', 1));
        $cart = $this->cart();
        $new  = min(($cart[$book->id] ?? 0) + $qty, $book->stock);

        $cart[$book->id] = $new;
        session(['cart' => $cart]);

        return back()->with('success', "បានដាក់ «{$book->title}» ចូលកន្ត្រក ({$new})");
    }

    /** ទិញភ្លាម: កម្មង់សៀវភៅនេះតែមួយក្បាលភ្លាម រួចបង្ហាញ QR (មិនប៉ះកន្ត្រកចាស់) */
    public function buyNow(Request $request, Book $book)
    {
        if ($book->stock < 1) {
            return back()->with('error', "«{$book->title}» អស់ស្តុក");
        }

        $saved = session('cart', []);
        session(['cart' => [$book->id => 1]]);
        $response = app()->call([app(OrderController::class), 'store']);
        session(['cart' => $saved]);

        return $response;
    }

    public function update(Request $request, Book $book)
    {
        $qty  = (int) $request->input('quantity', 1);
        $cart = $this->cart();

        if ($qty < 1) {
            unset($cart[$book->id]);
        } else {
            $cart[$book->id] = min($qty, max($book->stock, 1));
        }

        session(['cart' => $cart]);

        return back();
    }

    public function remove(Book $book)
    {
        $cart = $this->cart();
        unset($cart[$book->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'បានដកចេញពីកន្ត្រក');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'បានសម្អាតកន្ត្រក');
    }
}
