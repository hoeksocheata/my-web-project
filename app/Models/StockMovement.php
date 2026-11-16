<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['book_id', 'user_id', 'type', 'change', 'balance', 'note'];

    protected $casts = ['change' => 'integer', 'balance' => 'integer'];

    public const LABELS = [
        'initial' => 'ស្តុកដំបូង',
        'sale'    => 'លក់',
        'void'    => 'បោះបង់ការលក់',
        'restock' => 'ទិញចូល',
        'adjust'  => 'កែតម្រូវ',
    ];

    public function book(): BelongsTo { return $this->belongsTo(Book::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getTypeLabelAttribute(): string
    {
        return self::LABELS[$this->type] ?? $this->type;
    }
}
