<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Fillable(['name', 'description', 'status'])]
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

    public const DURATION_DAY = ProductDuration::DURATION_DAY;

    public const DURATION_WEEK = ProductDuration::DURATION_WEEK;

    public const DURATION_MONTH = ProductDuration::DURATION_MONTH;

    public const DURATION_YEAR = ProductDuration::DURATION_YEAR;

    public const DURATION_LIFETIME = ProductDuration::DURATION_LIFETIME;

    public const DURATION_UNITS = ProductDuration::DURATION_UNITS;

    /**
     * Get all duration and price options for this product package.
     */
    public function durations(): HasMany
    {
        return $this->hasMany(ProductDuration::class, 'product_id');
    }

    /**
     * Get only active duration options for this product package.
     */
    public function activeDurations(): HasMany
    {
        return $this->hasMany(ProductDuration::class, 'product_id')->where('is_active', true);
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
     * Get lowest price among active durations.
     */
    public function getMinPriceAttribute(): float
    {
        $min = $this->relationLoaded('durations')
            ? $this->durations->min('price')
            : $this->durations()->min('price');

        return $min !== null ? (float) $min : 0.0;
    }

    /**
     * Get highest price among active durations.
     */
    public function getMaxPriceAttribute(): float
    {
        $max = $this->relationLoaded('durations')
            ? $this->durations->max('price')
            : $this->durations()->max('price');

        return $max !== null ? (float) $max : 0.0;
    }

    /**
     * Get fallback price from primary duration.
     */
    public function getPriceAttribute(): float
    {
        return $this->min_price;
    }

    /**
     * Get duration value fallback from primary duration.
     */
    public function getDurationValueAttribute(): ?int
    {
        $duration = $this->relationLoaded('durations')
            ? $this->durations->first()
            : $this->durations()->first();

        return $duration ? (int) $duration->duration_value : null;
    }

    /**
     * Get duration unit fallback from primary duration.
     */
    public function getDurationUnitAttribute(): ?string
    {
        $duration = $this->relationLoaded('durations')
            ? $this->durations->first()
            : $this->durations()->first();

        return $duration ? (string) $duration->duration_unit : null;
    }

    /**
     * Format starting price to Rupiah currency string.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->min_price, 0, ',', '.');
    }

    /**
     * Format duration summary text.
     */
    public function getDurationFormattedAttribute(): string
    {
        $count = $this->relationLoaded('durations')
            ? $this->durations->count()
            : $this->durations()->count();

        return $count > 0 ? "{$count} Pilihan Durasi" : '-';
    }

    /**
     * Calculate end date given a start date and product duration.
     * Delegates to the primary/first active duration if called on product model directly.
     */
    public function calculateEndDate(string|CarbonInterface $startDate): Carbon
    {
        $duration = $this->activeDurations()->first() ?? $this->durations()->first();

        if ($duration) {
            return $duration->calculateEndDate($startDate);
        }

        return Carbon::parse($startDate)->addMonth();
    }

    /**
     * Memeriksa apakah paket produk merupakan paket visit harian (24 jam / 1 hari).
     */
    public function isDailyVisit(): bool
    {
        return str_contains(strtolower($this->name), 'visit')
            || $this->durations->contains(fn ($d) => $d->duration_unit === ProductDuration::DURATION_DAY && $d->duration_value <= 1);
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

    /**
     * Kelompokkan produk aktif menjadi layanan utama (Services) dengan opsi durasi.
     *
     * @return Collection<int, object>
     */
    public static function groupedServices(?string $status = self::STATUS_ACTIVE): Collection
    {
        $query = static::with(['durations' => fn ($q) => $q->orderBy('duration_value')]);
        if ($status !== null) {
            $query->where('status', $status);
        }

        $products = $query->get();

        return $products->map(function ($product) {
            $nameLower = strtolower($product->name);

            $key = 'other';
            if ((str_contains($nameLower, 'aerobic') || str_contains($nameLower, 'zumba'))
                && (str_contains($nameLower, 'fitness') || str_contains($nameLower, 'gym') || str_contains($nameLower, '+'))
            ) {
                $key = 'combo';
            } elseif (str_contains($nameLower, 'aerobic') || str_contains($nameLower, 'zumba')) {
                $key = 'aerobic';
            } elseif (str_contains($nameLower, 'fitness') || str_contains($nameLower, 'gym')) {
                $key = 'fitness';
            }

            $badge = match ($key) {
                'fitness' => 'Gym & Beban',
                'aerobic' => 'Kelas Studio',
                'combo' => 'Paket Lengkap',
                default => 'Layanan Gym',
            };

            $icon = match ($key) {
                'fitness' => 'bx-dumbbell',
                'aerobic' => 'bx-run',
                'combo' => 'bx-layer',
                default => 'bx-package',
            };

            $benefits = match ($key) {
                'fitness' => [
                    'Akses Area Gym & Fasilitas Lengkap',
                    'Peralatan Cardio & Weight Training',
                    'Reservasi Kunjungan Terjadwal',
                    'Loker, Kamar Mandi & Fasilitas Gym',
                    'Presensi Digital QR',
                ],
                'aerobic' => [
                    'Akses Studio Aerobic & Zumba',
                    'Instruktur Berlisensi & Musik Energik',
                    'Jadwal Rutin (Senin & Kamis 19.00-21.00)',
                    'Reservasi Sesi Kelas Terjadwal',
                    'Presensi Digital QR',
                ],
                'combo' => [
                    'Akses Penuh Area Gym & Fitness',
                    'Bebas Mengikuti Seluruh Kelas Aerobic & Zumba',
                    'Pilihan Jadwal Fleksibel',
                    'Reservasi Kunjungan Gym & Sesi Kelas',
                    'Presensi Digital QR',
                ],
                default => [
                    'Akses Fasilitas Resmi IFGS',
                    'Reservasi Kunjungan Terjadwal',
                    'Presensi Digital QR',
                ],
            };

            $defaultDuration = $product->durations->first(function ($d) {
                return $d->duration_unit === ProductDuration::DURATION_MONTH && $d->duration_value === 1;
            }) ?? $product->durations->first();

            return (object) [
                'key' => (string) $key,
                'product' => $product,
                'title' => $product->name,
                'badge' => $badge,
                'icon' => $icon,
                'description' => $product->description ?: 'Paket layanan kebugaran resmi Indo Fitness Gym Sport.',
                'benefits' => $benefits,
                'durations' => $product->durations,
                'default_duration' => $defaultDuration,
                'products' => $product->durations,
                'default_product' => $defaultDuration,
            ];
        })->values();
    }
}
