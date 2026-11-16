<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // មុខងារពិនិត្យសិទ្ធិ Admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** សៀវភៅក្នុងបញ្ជីចូលចិត្ត */
    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'wishlists')->withTimestamps();
    }

    /** ធ្លាប់ទិញសៀវភៅនេះឬទេ (ការកម្មង់ដែលបានអនុម័ត) */
    public function hasPurchased(Book $book): bool
    {
        return OrderItem::where('book_id', $book->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $this->id)->where('status', Order::APPROVED))
            ->exists();
    }
}
