<?php

namespace App\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['transaction_id', 'member_id', 'product_id', 'payment_method_id', 'start_date', 'end_date', 'price', 'status'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
        self::STATUS_PENDING,
        self::STATUS_REJECTED,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the transaction that generated this membership.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the member that owns this membership record.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the master product package of this membership.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the payment method used for this membership transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get all visit reservations under this membership.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Determine if the membership is currently active based on status and dates.
     */
    public function isCurrentlyActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $today = now()->startOfDay();

        return $today->between(
            $this->start_date->copy()->startOfDay(),
            $this->end_date->copy()->endOfDay()
        );
    }

    /**
     * Get the status computed strictly by system based on end_date and cancellation.
     */
    public function getStatusAttribute($value): string
    {
        if (in_array($value, [self::STATUS_CANCELLED, self::STATUS_PENDING, self::STATUS_REJECTED])) {
            return $value;
        }

        if ($this->end_date && $this->end_date->isPast() && ! $this->end_date->isToday()) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_ACTIVE;
    }

    /**
     * Nama kasir / admin yang memproses transaksi, atau nama pelanggan jika melakukan sendiri.
     */
    public function getCashierNameAttribute(): string
    {
        if ($this->transaction && $this->transaction->user) {
            return $this->transaction->user->name;
        }

        if ($this->member?->user) {
            return $this->member->user->name;
        }

        return 'Sistem';
    }

    /**
     * Format price to Rupiah currency string.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Status badge CSS class for Sneat / Bootstrap 5.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-label-success',
            self::STATUS_EXPIRED => 'bg-label-secondary',
            self::STATUS_CANCELLED, self::STATUS_REJECTED => 'bg-label-danger',
            self::STATUS_PENDING => 'bg-label-warning',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Scope a query to only include active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    /**
     * Scope a query to only include expired memberships.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
            ->orWhere(function ($q) {
                $q->where('status', self::STATUS_ACTIVE)
                    ->whereDate('end_date', '<', now());
            });
    }

    /**
     * Status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PENDING => 'Menunggu Validasi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the public URL for uploaded payment proof screenshot (from transaction).
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->transaction?->payment_proof_url;
    }
}
