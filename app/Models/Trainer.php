<?php

namespace App\Models;

use Database\Factories\TrainerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'trainer_code', 'phone', 'specialization', 'bio', 'status', 'daily_quota'])]
class Trainer extends Model
{
    /** @use HasFactory<TrainerFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * Attributes cast.
     */
    protected function casts(): array
    {
        return [
            'daily_quota' => 'integer',
        ];
    }

    /**
     * Hitung jumlah sesi terisi pada tanggal tertentu.
     */
    public function getOccupiedSlotsForDate(string $date): int
    {
        return $this->bookings()
            ->whereDate('session_date', $date)
            ->whereNotIn('status', [TrainerBooking::STATUS_CANCELLED, TrainerBooking::STATUS_REJECTED])
            ->count();
    }

    /**
     * Hitung sisa slot personal trainer pada tanggal tertentu.
     */
    public function getRemainingSlotsForDate(string $date): int
    {
        $quota = $this->daily_quota ?? 5;

        return max(0, $quota - $this->getOccupiedSlotsForDate($date));
    }

    /**
     * Boot model events.
     */
    protected static function booted(): void
    {
        static::creating(function (Trainer $trainer) {
            if (empty($trainer->trainer_code)) {
                $trainer->trainer_code = static::generateUniqueTrainerCode();
            }
        });
    }

    /**
     * Generate unique trainer code (e.g. TRN-001, TRN-002).
     */
    public static function generateUniqueTrainerCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $nextNumber = $last ? ($last->id + 1) : 1;

        do {
            $code = 'TRN-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::where('trainer_code', $code)->exists());

        return $code;
    }

    /**
     * Get user account for this trainer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active trainers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Daftar permohonan sesi latihan member dengan trainer ini.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(TrainerBooking::class);
    }
}
