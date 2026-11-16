<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'customer_name', 'total'];

    protected $casts = ['total' => 'decimal:2'];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getInvoiceNoAttribute(): string
    {
        return 'INV-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
