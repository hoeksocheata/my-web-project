<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

/** បង្កើតទិន្នន័យលក់គំរូ ៦០ ថ្ងៃចុងក្រោយ ដើម្បីឱ្យរបាយការណ៍មានទិន្នន័យ (មិនកាត់ស្តុកពិតទេ) */
class SaleSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::exists()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();
        $books = Book::all();
        if ($books->isEmpty()) {
            return;
        }

        $customers = [null, null, 'សុខា', 'ដាវី', 'វិសាល', 'លីណា', 'ចាន់ថន'];

        for ($i = 0; $i < 45; $i++) {
            $when = now()->subDays(random_int(0, 59))->setTime(random_int(8, 19), random_int(0, 59));
            $sale = Sale::create([
                'user_id' => $admin?->id,
                'customer_name' => $customers[array_rand($customers)],
                'total' => 0,
                'created_at' => $when,
                'updated_at' => $when,
            ]);

            $total = 0;
            foreach ($books->random(random_int(1, 3)) as $book) {
                $qty = random_int(1, 3);
                $sale->items()->create([
                    'book_id' => $book->id, 'title' => $book->title, 'price' => $book->price,
                    'quantity' => $qty, 'created_at' => $when, 'updated_at' => $when,
                ]);
                $total += (float) $book->price * $qty;
            }
            $sale->update(['total' => $total]);
        }
    }
}
