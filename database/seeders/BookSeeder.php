<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;
use App\Models\StockMovement;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['អប់រំ', 'បច្ចេកវិទ្យា', 'អភិវឌ្ឍខ្លួន', 'ប្រវត្តិសាស្ត្រ', 'ហិរញ្ញវត្ថុ'])
            ->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['name' => $name])->id]);

        $books = [
            ['title' => 'សិល្បៈនៃការរស់នៅ', 'author' => 'លោក ជុំ ម៉ៅ', 'cat' => 'អភិវឌ្ឍខ្លួន', 'price' => 15.00, 'stock' => 12],
            ['title' => 'មគ្គុទេសក៍ការសរសេរកូដ', 'author' => 'កញ្ញា លីណា', 'cat' => 'បច្ចេកវិទ្យា', 'price' => 20.00, 'stock' => 8],
            ['title' => 'អាថ៌កំបាំងនៃភាពជោគជ័យ', 'author' => 'លោក សុខា', 'cat' => 'អភិវឌ្ឍខ្លួន', 'price' => 25.00, 'stock' => 3],
            ['title' => 'ប្រវត្តិសាស្រ្តខ្មែរ', 'author' => 'សាស្ត្រាចារ្យ សោម', 'cat' => 'ប្រវត្តិសាស្ត្រ', 'price' => 18.00, 'stock' => 20],
            ['title' => 'រៀនប្រើប្រាស់ Laravel', 'author' => 'លោក វ៉ាន់ដា', 'cat' => 'បច្ចេកវិទ្យា', 'price' => 30.00, 'stock' => 0],
            ['title' => 'យុទ្ធសាស្រ្តគ្រប់គ្រងលុយ', 'author' => 'កញ្ញា ដាវី', 'cat' => 'ហិរញ្ញវត្ថុ', 'price' => 22.00, 'stock' => 15],
            ['title' => 'ពិភពនៃបច្ចេកវិទ្យា', 'author' => 'លោក កុសល', 'cat' => 'បច្ចេកវិទ្យា', 'price' => 28.00, 'stock' => 5],
            ['title' => 'គំនិតច្នៃប្រឌិត', 'author' => 'លោក ចាន់ថន', 'cat' => 'អប់រំ', 'price' => 12.00, 'stock' => 30],
            ['title' => 'ការគ្រប់គ្រងពេលវេលា', 'author' => 'កញ្ញា សុភី', 'cat' => 'អភិវឌ្ឍខ្លួន', 'price' => 10.00, 'stock' => 9],
            ['title' => 'បេះដូងអ្នកដឹកនាំ', 'author' => 'លោក វិសាល', 'cat' => 'អភិវឌ្ឍខ្លួន', 'price' => 35.00, 'stock' => 2],
        ];

        foreach ($books as $i => $b) {
            $book = Book::firstOrCreate(
                ['title' => $b['title'], 'author' => $b['author']],
                [
                    'isbn' => '978-9924-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'category_id' => $categories[$b['cat']],
                    'price' => $b['price'],
                    'stock' => $b['stock'],
                    'description' => null,
                ]
            );

            if ($book->wasRecentlyCreated && $book->stock > 0) {
                StockMovement::create([
                    'book_id' => $book->id, 'type' => 'initial', 'change' => $book->stock,
                    'balance' => $book->stock, 'note' => 'ស្តុកដំបូង (seed)',
                ]);
            }
        }
    }
}
