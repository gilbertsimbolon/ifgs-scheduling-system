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
     * Format duration label (e.g. "1 Bulan", "3 Hari").
     */
    public function getDurationFormattedAttribute(): string
    {
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
     */
    public function calculateEndDate(string|CarbonInterface $startDate): Carbon
    {
        $start = Carbon::parse($startDate);

        return match ($this->duration_unit) {
            'day' => $start->copy()->addDays($this->duration_value),
            'week' => $start->copy()->addWeeks($this->duration_value),
            'month' => $start->copy()->addMonths($this->duration_value),
            'year' => $start->copy()->addYears($this->duration_value),
            default => $start->copy()->addMonths($this->duration_value),
        };
    }
}
