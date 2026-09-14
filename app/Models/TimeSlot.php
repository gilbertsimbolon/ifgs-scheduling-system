<?php

namespace App\Models;

use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'start_time', 'end_time', 'capacity', 'status'])]
class TimeSlot extends Model
{
    /** @use HasFactory<TimeSlotFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * Get all reservations requested for this time slot.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get all confirmed schedules assigned to this time slot.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Scope a query to only include active time slots.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Format time range as HH:mm - HH:mm.
     */
    public function getTimeRangeAttribute(): string
    {
        return "{$this->start_time} - {$this->end_time}";
    }

    /**
     * Display label combining slot name and time range.
     */
    public function getLabelWithTimeAttribute(): string
    {
        return "{$this->name} ({$this->time_range})";
    }

    /**
     * Count confirmed visitors scheduled for a specific date.
     */
    public function getOccupiedCountForDate(string $date): int
    {
        return $this->schedules()
            ->where('scheduled_date', $date)
            ->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])
            ->count();
    }

    /**
     * Calculate remaining capacity for a specific date.
     */
    public function getRemainingCapacityForDate(string $date): int
    {
        $occupied = $this->getOccupiedCountForDate($date);

        return max(0, $this->capacity - $occupied);
    }

    /**
     * Check if slot is fully booked for a specific date.
     */
    public function isFullForDate(string $date): bool
    {
        return $this->getRemainingCapacityForDate($date) <= 0;
    }

    /**
     * Calculate occupancy rate (0.0 to 1.0) for a specific date.
     */
    public function getOccupancyRateForDate(string $date): float
    {
        if ($this->capacity <= 0) {
            return 1.0;
        }

        return round($this->getOccupiedCountForDate($date) / $this->capacity, 4);
    }

    /**
     * Status badge CSS class for Sneat / Bootstrap 5.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE
            ? 'bg-label-success'
            : 'bg-label-secondary';
    }

    /**
     * Status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'Aktif' : 'Non-Aktif';
    }
}
