<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const PENDING   = 'pending';
    public const APPROVED  = 'approved';
    public const REJECTED  = 'rejected';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id', 'status', 'note', 'reject_reason', 'total', 'sale_id',
        'payment_status', 'payment_ref', 'payment_proof', 'paid_at',
    ];

    protected $casts = ['total' => 'decimal:2', 'paid_at' => 'datetime'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getOrderNoAttribute(): string
    {
        return 'ORD-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /** ['label' => ..., 'class' => ...] សម្រាប់ badge */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::APPROVED  => ['label' => 'បានអនុម័ត', 'class' => 'success'],
            self::REJECTED  => ['label' => 'ត្រូវបានបដិសេធ', 'class' => 'danger'],
            self::CANCELLED => ['label' => 'បានបោះបង់', 'class' => 'muted'],
            default         => ['label' => 'រង់ចាំការទូទាត់', 'class' => 'warning'],
        };
    }

    public function getPaymentBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            'paid'      => ['label' => 'បានទូទាត់', 'class' => 'success'],
            'submitted' => ['label' => 'កំពុងផ្ទៀងផ្ទាត់', 'class' => 'warning'],
            default     => ['label' => 'មិនទាន់ទូទាត់', 'class' => 'muted'],
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? asset('storage/'.$this->payment_proof) : null;
    }

    /** អតិថិជនអាចបង់ប្រាក់បានភ្លាមបន្ទាប់ពីកម្មង់ (មិនចាំបាច់រង់ចាំអនុម័ត) ដរាបណាមិនទាន់ទូទាត់ */
    public function canPay(): bool
    {
        return in_array($this->status, [self::PENDING, self::APPROVED], true) && $this->payment_status !== 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
