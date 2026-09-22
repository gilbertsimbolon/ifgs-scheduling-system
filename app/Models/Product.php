<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'description', 'price', 'duration_value', 'duration_unit', 'status'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    public const DURATION_DAY = 'day';

    public const DURATION_WEEK = 'week';

    public const DURATION_MONTH = 'month';

    public const DURATION_YEAR = 'year';

    public const DURATION_UNITS = [
        self::DURATION_DAY,
        self::DURATION_WEEK,
        self::DURATION_MONTH,
        self::DURATION_YEAR,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_value' => 'integer',
        ];
    }

    /**
     * Get the memberships purchased under this product package.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all transaction items for this product.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Format unit label in Indonesian.
     */
    public function getDurationUnitLabelAttribute(): string
    {
        return match ($this->duration_unit) {
            'day' => 'Hari',
            'week' => 'Minggu',
            'month' => 'Bulan',
            'year' => 'Tahun',
            default => ucfirst($this->duration_unit),
        };
    }

    /**
     * Format duration label (e.g. "1 Bulan", "1 Hari (Visit)").
     */
    public function getDurationFormattedAttribute(): string
    {
        if ($this->duration_unit === 'day' && $this->duration_value === 1) {
            return '1 Hari (Visit)';
        }

        return "{$this->duration_value} {$this->duration_unit_label}";
    }

    /**
     * Format price to Rupiah currency string.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Calculate end date given a start date and product duration.
     * Untuk paket visit (1 hari), durasi berlaku sampai jam tutup gym pada hari yang sama (end_date = start_date).
     */
    public function calculateEndDate(string|CarbonInterface $startDate): Carbon
    {
        $start = Carbon::parse($startDate);

        return match ($this->duration_unit) {
            'day' => $this->duration_value <= 1
                ? $start->copy()
                : $start->copy()->addDays($this->duration_value - 1),
            'week' => $start->copy()->addWeeks($this->duration_value),
            'month' => $start->copy()->addMonths($this->duration_value),
            'year' => $start->copy()->addYears($this->duration_value),
            default => $start->copy()->addMonths($this->duration_value),
        };
    }

    /**
     * Memeriksa apakah paket produk merupakan paket visit harian (24 jam / 1 hari).
     */
    public function isDailyVisit(): bool
    {
        return ($this->duration_unit === self::DURATION_DAY && $this->duration_value <= 1)
            || str_contains(strtolower($this->name), 'visit');
    }

    /**
     * Mengetahui apakah paket produk ini mencakup kategori layanan time slot tertentu.
     */
    public function supportsCategory(string $category): bool
    {
        $nameLower = strtolower($this->name);

        if ($category === TimeSlot::CATEGORY_FITNESS) {
            if ((str_contains($nameLower, 'aerobic') || str_contains($nameLower, 'zumba'))
                && ! str_contains($nameLower, 'fitness')
                && ! str_contains($nameLower, 'gym')
            ) {
                return false;
            }

            return str_contains($nameLower, 'fitness')
                || str_contains($nameLower, 'gym')
                || ! (str_contains($nameLower, 'aerobic') || str_contains($nameLower, 'zumba'));
        }

        if ($category === TimeSlot::CATEGORY_AEROBIC_ZUMBA) {
            return str_contains($nameLower, 'aerobic') || str_contains($nameLower, 'zumba');
        }

        return false;
    }

    /**
     * Mengambil daftar kategori time slot yang dicakup oleh produk ini.
     *
     * @return array<int, string>
     */
    public function supportedCategories(): array
    {
        $categories = [];

        if ($this->supportsCategory(TimeSlot::CATEGORY_FITNESS)) {
            $categories[] = TimeSlot::CATEGORY_FITNESS;
        }

        if ($this->supportsCategory(TimeSlot::CATEGORY_AEROBIC_ZUMBA)) {
            $categories[] = TimeSlot::CATEGORY_AEROBIC_ZUMBA;
        }

        if (empty($categories)) {
            $categories[] = TimeSlot::CATEGORY_FITNESS;
        }

        return $categories;
    }
}
