<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /** ស្តុកដែលចាត់ទុកថាជិតអស់ */
    public const LOW_STOCK = 5; // ប្រើតម្លៃពី config('bookstore.low_stock') ក៏បាន

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'price',
        'stock',
        'description',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest()->latest('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /** ស្វែងរកតាមចំណងជើង ឬអ្នកនិពន្ធ (ដាក់ក្នុងវង់ក្រចក ដើម្បីកុំឱ្យច្រឡំនឹងតម្រងផ្សេង) */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $q, string $term) {
            $q->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('author', 'like', "%{$term}%")
                  ->orWhere('isbn', 'like', "%{$term}%");
            });
        });
    }

    public function scopeStockStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'out'  => $query->where('stock', 0),
            'low'  => $query->whereBetween('stock', [1, self::LOW_STOCK]),
            'in'   => $query->where('stock', '>', self::LOW_STOCK),
            default => $query,
        };
    }

    /** ['label' => ..., 'class' => ...] សម្រាប់បង្ហាញ badge ស្ថានភាពស្តុក */
    public function getStockBadgeAttribute(): array
    {
        if ($this->stock <= 0) {
            return ['label' => 'អស់ស្តុក', 'class' => 'danger'];
        }
        if ($this->stock <= self::LOW_STOCK) {
            return ['label' => 'ជិតអស់ ('.$this->stock.')', 'class' => 'warning'];
        }
        return ['label' => 'មាន ('.$this->stock.')', 'class' => 'success'];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
