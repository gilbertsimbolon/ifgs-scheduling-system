<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'days', 'start_time', 'end_time', 'capacity', 'reservation_quota', 'status'])]
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

    public const CATEGORY_FITNESS = 'fitness';

    public const CATEGORY_AEROBIC_ZUMBA = 'aerobic_zumba';

    public const CATEGORIES = [
        self::CATEGORY_FITNESS,
        self::CATEGORY_AEROBIC_ZUMBA,
    ];

    /**
     * Label representasi kategori layanan.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_AEROBIC_ZUMBA => 'Aerobic / Zumba',
            default => 'Fitness',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'reservation_quota' => 'integer',
        ];
    }

    /**
     * Kuota efektif yang dialokasikan untuk reservasi online.
     * Jika belum disetel manual, default menggunakan 70% dari kapasitas fisik.
     */
    public function getEffectiveReservationQuotaAttribute(): int
    {
        if ($this->reservation_quota !== null) {
            return (int) $this->reservation_quota;
        }

        return (int) round($this->capacity * 0.7);
    }

    /**
     * Kuota yang dicadangkan khusus untuk pengunjung langsung (walk-in).
     */
    public function getWalkinQuotaAttribute(): int
    {
        return max(0, $this->capacity - $this->effective_reservation_quota);
    }

    /**
     * Alias kuota reservasi efektif.
     */
    public function getQuotaAttribute(): int
    {
        return $this->effective_reservation_quota;
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
     * Hitung sisa kuota reservasi online untuk tanggal tertentu.
     */
    public function getRemainingReservationQuotaForDate(string $date): int
    {
        $occupied = $this->getOccupiedCountForDate($date);

        return max(0, $this->effective_reservation_quota - $occupied);
    }

    /**
     * Cek apakah kuota reservasi online sudah penuh untuk tanggal tertentu.
     */
    public function isReservationFullForDate(string $date): bool
    {
        return $this->getRemainingReservationQuotaForDate($date) <= 0;
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

    /**
     * Dapatkan daftar hari operasional dalam format integer CarbonInterface (0 = Minggu, 1 = Senin, ..., 6 = Sabtu).
     *
     * @return array<int>
     */
    public function getOperationalDaysOfWeek(): array
    {
        if ($this->category === self::CATEGORY_AEROBIC_ZUMBA || str_contains(strtolower($this->days ?? ''), 'kamis')) {
            return [CarbonInterface::MONDAY, CarbonInterface::THURSDAY];
        }

        // Default untuk Fitness atau slot gym standar: Senin s.d. Sabtu (Minggu tutup)
        return [
            CarbonInterface::MONDAY,
            CarbonInterface::TUESDAY,
            CarbonInterface::WEDNESDAY,
            CarbonInterface::THURSDAY,
            CarbonInterface::FRIDAY,
            CarbonInterface::SATURDAY,
        ];
    }

    /**
     * Periksa apakah slot ini beroperasi pada tanggal yang diberikan.
     */
    public function operatesOnDate(CarbonInterface|string $date): bool
    {
        $carbon = is_string($date) ? Carbon::parse($date) : $date;

        return in_array($carbon->dayOfWeek, $this->getOperationalDaysOfWeek(), true);
    }

    /**
     * Menghasilkan N tanggal operasional mendatang untuk slot ini dimulai dari hari ini.
     * Dapat dibatasi oleh endDate agar tidak melebihi masa aktif membership.
     *
     * @return array<CarbonInterface>
     */
    public function getUpcomingOperationalDates(int $count = 10, ?CarbonInterface $startDate = null, ?CarbonInterface $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfDay();
        $operationalDays = $this->getOperationalDaysOfWeek();
        $dates = [];
        $cursor = $startDate->copy();
        $end = $endDate ? $endDate->copy()->startOfDay() : null;

        // Cari hingga $count hari operasional tercapai (maksimal batas 60 hari ke depan)
        for ($i = 0; $i < 60 && count($dates) < $count; $i++) {
            if ($end && $cursor->gt($end)) {
                break;
            }
            if (in_array($cursor->dayOfWeek, $operationalDays, true)) {
                $dates[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $dates;
    }
}
