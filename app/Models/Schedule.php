<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['schedule_code', 'reservation_id', 'member_id', 'time_slot_id', 'scheduled_date', 'status', 'notes'])]
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ATTENDED = 'attended';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_ATTENDED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
        ];
    }

    /**
     * Generate unique schedule code format: SCH-YYYYMMDD-XXXX
     */
    public static function generateScheduleCode(): string
    {
        $todayStr = now()->format('Ymd');
        $prefix = "SCH-{$todayStr}-";

        $last = self::where('schedule_code', 'LIKE', "{$prefix}%")
            ->orderByDesc('id')
            ->value('schedule_code');

        if (! $last) {
            return "{$prefix}0001";
        }

        $lastNum = (int) substr($last, -4);
        $nextNum = str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextNum}";
    }

    /**
     * Get the reservation that originated this schedule.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the scheduled member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the assigned time slot.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Scope query to a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }

    /**
     * Status badge CSS class for Sneat / Bootstrap 5.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'bg-label-primary',
            self::STATUS_ATTENDED => 'bg-label-success',
            self::STATUS_NO_SHOW => 'bg-label-warning',
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
            self::STATUS_ATTENDED => 'Check-in',
            self::STATUS_NO_SHOW => 'Tidak Hadir',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
