<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['product_id', 'duration_value', 'duration_unit', 'price', 'is_active'])]
class ProductDuration extends Model
{
    use HasFactory;

    public const DURATION_DAY = 'day';

    public const DURATION_WEEK = 'week';

    public const DURATION_MONTH = 'month';

    public const DURATION_YEAR = 'year';

    public const DURATION_LIFETIME = 'lifetime';

    public const DURATION_UNITS = [
        self::DURATION_DAY,
        self::DURATION_WEEK,
        self::DURATION_MONTH,
        self::DURATION_YEAR,
        self::DURATION_LIFETIME,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_value' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get memberships associated with this specific duration option.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'product_duration_id');
    }

    /**
     * Get transaction items associated with this specific duration option.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'product_duration_id');
    }

    /**
     * Format unit label in Indonesian.
     */
    public function getDurationUnitLabelAttribute(): string
    {
        return match ($this->duration_unit) {
            self::DURATION_DAY => 'Hari',
            self::DURATION_WEEK => 'Minggu',
            self::DURATION_MONTH => 'Bulan',
            self::DURATION_YEAR => 'Tahun',
            self::DURATION_LIFETIME => 'Seumur Hidup',
            default => ucfirst($this->duration_unit),
        };
    }

    /**
     * Get duration in total days based on duration_value and duration_unit.
     */
    public function getDurationDaysAttribute(): int
    {
        return match ($this->duration_unit) {
            self::DURATION_DAY => (int) $this->duration_value,
            self::DURATION_WEEK => (int) ($this->duration_value * 7),
            self::DURATION_MONTH => (int) ($this->duration_value * 30),
            self::DURATION_YEAR => (int) ($this->duration_value * 365),
            self::DURATION_LIFETIME => 36500,
            default => (int) ($this->duration_value * 30),
        };
    }

    /**
     * Format duration label (e.g. "1 Bulan", "1 Hari (Visit)", "Seumur Hidup").
     */
    public function getDurationFormattedAttribute(): string
    {
        if ($this->duration_unit === self::DURATION_LIFETIME) {
            return 'Seumur Hidup';
        }

        if ($this->duration_unit === self::DURATION_DAY && $this->duration_value === 1) {
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
     * Untuk paket visit 1 hari, berlaku sampai jam tutup gym pada hari yang sama (end_date = start_date).
     * Untuk seumur hidup, berlaku hingga 99 tahun mendatang.
     */
    public function calculateEndDate(string|CarbonInterface $startDate): Carbon
    {
        $start = Carbon::parse($startDate);

        return match ($this->duration_unit) {
            self::DURATION_DAY => $this->duration_value <= 1
                ? $start->copy()
                : $start->copy()->addDays($this->duration_value - 1),
            self::DURATION_WEEK => $start->copy()->addWeeks($this->duration_value),
            self::DURATION_MONTH => $start->copy()->addMonths($this->duration_value),
            self::DURATION_YEAR => $start->copy()->addYears($this->duration_value),
            self::DURATION_LIFETIME => $start->copy()->addYears(99),
            default => $start->copy()->addMonths($this->duration_value),
        };
    }
}
