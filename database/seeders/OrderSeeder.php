<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

/** ការកម្មង់ និងបញ្ជីចូលចិត្តគំរូ សម្រាប់បង្ហាញពេល demo */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'user@example.com')->first();
        $books = Book::where('stock', '>', 3)->get();
        if (! $customer || $books->count() < 3 || Order::exists()) {
            return;
        }

        $customer->wishlist()->syncWithoutDetaching($books->take(3)->pluck('id'));

        foreach ($books->take(3) as $i => $book) {
            \App\Models\Review::firstOrCreate(['book_id' => $book->id, 'user_id' => $customer->id], [
                'rating' => [5, 4, 5][$i], 'comment' => ['សៀវភៅល្អណាស់!', 'មាតិកាងាយយល់', 'ណែនាំឱ្យអាន'][$i],
            ]);
        }

        foreach ([[Order::PENDING, 2], [Order::PENDING, 1], [Order::REJECTED, 1]] as [$status, $n]) {
            $order = Order::create([
                'user_id' => $customer->id, 'status' => $status, 'total' => 0,
                'note' => $status === Order::PENDING ? 'សូមរៀបចំឱ្យរួចក្នុងសប្តាហ៍នេះ' : null,
                'reject_reason' => $status === Order::REJECTED ? 'អស់ស្តុកបណ្តោះអាសន្ន' : null,
            ]);
            $total = 0;
            foreach ($books->random($n) as $book) {
                $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'price' => $book->price, 'quantity' => 1]);
                $total += (float) $book->price;
            }
            $order->update(['total' => $total]);
        }
    }
}
