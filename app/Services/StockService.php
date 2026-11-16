<?php

namespace App\Services;

use App\Models\Book;
use App\Models\StockMovement;

/**
 * ប្តូរចំនួនស្តុក + កត់ត្រាប្រវត្តិចលនាស្តុក (audit trail) ក្នុងកន្លែងតែមួយ។
 * ហៅក្នុង transaction ដែលមាន lock សៀវភៅរួចហើយ។
 */
class StockService
{
    public function change(Book $book, int $delta, string $type, ?int $userId = null, ?string $note = null): StockMovement
    {
        if ($delta > 0) {
            $book->increment('stock', $delta);
        } elseif ($delta < 0) {
            $book->decrement('stock', -$delta);
        }

        return StockMovement::create([
            'book_id' => $book->id,
            'user_id' => $userId,
            'type'    => $type,
            'change'  => $delta,
            'balance' => max($book->stock, 0),
            'note'    => $note,
        ]);
    }
}
