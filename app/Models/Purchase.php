<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'book_id', 'user_id', 'quantity', 'unit_cost'];

    protected $casts = ['unit_cost' => 'decimal:2', 'quantity' => 'integer'];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function book(): BelongsTo { return $this->belongsTo(Book::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->unit_cost * $this->quantity;
    }
}
