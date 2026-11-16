<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * បង្កើតការលក់ + កាត់ស្តុក ក្នុង transaction តែមួយ។
 * ប្រើរួមគ្នាទាំងពេល Admin លក់ផ្ទាល់ និងពេល Admin អនុម័តការកម្មង់អនឡាញ។
 */
class SaleService
{
    public function __construct(private StockService $stock)
    {
    }

    /**
     * @param  array<int,int>  $wanted  [book_id => quantity]
     * @throws ValidationException ប្រសិនបើស្តុកមិនគ្រប់គ្រាន់
     */
    public function create(User $seller, ?string $customerName, array $wanted, ?int $customerId = null): Sale
    {
        return DB::transaction(function () use ($seller, $customerName, $wanted, $customerId) {
            // lockForUpdate ការពារការលក់ជាន់គ្នា (race condition)
            $books = Book::whereIn('id', array_keys($wanted))->lockForUpdate()->get()->keyBy('id');

            foreach ($wanted as $id => $qty) {
                $book = $books[$id] ?? null;
                if (! $book) {
                    throw ValidationException::withMessages(['items' => 'មានសៀវភៅមួយចំនួនលែងមានក្នុងប្រព័ន្ធ']);
                }
                if ($book->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "«{$book->title}» មានស្តុកត្រឹមតែ {$book->stock} ក្បាល (ត្រូវការ {$qty})",
                    ]);
                }
            }

            $sale = Sale::create([
                'user_id'       => $seller->id,
                'customer_id'   => $customerId,
                'customer_name' => $customerName,
                'total'         => 0,
            ]);

            $total = 0;
            foreach ($wanted as $id => $qty) {
                $book = $books[$id];
                $sale->items()->create([
                    'book_id'  => $book->id,
                    'title'    => $book->title,
                    'price'    => $book->price,
                    'quantity' => $qty,
                ]);
                $this->stock->change($book, -$qty, 'sale', $seller->id, $sale->invoice_no);
                $total += (float) $book->price * $qty;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }
}
