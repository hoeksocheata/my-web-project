<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'book_id', 'title', 'price', 'quantity'];

    protected $casts = ['price' => 'decimal:2', 'quantity' => 'integer'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }
}
