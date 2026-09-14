<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'member_id', 'membership_id', 'visit_date', 'time_slot_id', 'status', 'notes', 'created_by'])]
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SCHEDULED,
        self::STATUS_COMPLETED,
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
            'visit_date' => 'date',
        ];
    }

    /**
     * Generate unique reservation code format: RSV-YYYYMMDD-XXXX
     */
    public static function generateCode(): string
    {
        $todayStr = now()->format('Ymd');
        $prefix = "RSV-{$todayStr}-";

        $last = self::where('code', 'LIKE', "{$prefix}%")
            ->orderByDesc('id')
            ->value('code');

        if (! $last) {
            return "{$prefix}0001";
        }

        $lastNum = (int) substr($last, -4);
        $nextNum = str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextNum}";
    }

    /**
     * Get the member that made this reservation.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the membership package linked to this reservation.
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Get the requested/assigned time slot.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Get the confirmed visit schedule created for this reservation.
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    /**
     * Get the user who recorded this reservation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to specific visit date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('visit_date', $date);
    }

    /**
     * Scope query to pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to scheduled reservations.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Determine if reservation can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_COMPLETED], true)) {
            return false;
        }

        return $this->visit_date->startOfDay()->isSameDay(now()->startOfDay()) || $this->visit_date->isFuture();
    }

    /**
     * Status badge CSS class for Sneat / Bootstrap 5.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'bg-label-success',
            self::STATUS_PENDING => 'bg-label-warning',
            self::STATUS_COMPLETED => 'bg-label-info',
            self::STATUS_CANCELLED => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PENDING => 'Menunggu Jadwal',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
