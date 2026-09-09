<?php

namespace App\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['member_id', 'product_id', 'start_date', 'end_date', 'price', 'status'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
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
            self::STATUS_EXPIRED => 'bg-label-warning',
            self::STATUS_CANCELLED => 'bg-label-danger',
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
            default => ucfirst($this->status),
        };
    }
}
