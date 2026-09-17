<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_code',
    'member_id',
    'membership_id',
    'reservation_id',
    'trainer_booking_id',
    'date',
    'check_in_at',
    'check_out_at',
    'status',
    'scan_method',
    'notes',
    'created_by',
])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_CHECKED_IN,
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
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    /**
     * Booted event to auto-generate attendance code.
     */
    protected static function booted(): void
    {
        static::creating(function (Attendance $attendance) {
            if (empty($attendance->attendance_code)) {
                $attendance->attendance_code = static::generateAttendanceCode();
            }
            if (empty($attendance->date)) {
                $attendance->date = now()->toDateString();
            }
        });
    }

    /**
     * Generate unique attendance code format: ATT-YYYYMMDD-XXXX
     */
    public static function generateAttendanceCode(): string
    {
        $prefix = 'ATT-'.date('Ymd').'-';
        $last = static::where('attendance_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last->attendance_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        do {
            $code = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::where('attendance_code', $code)->exists());

        return $code;
    }

    /**
     * Member yang hadir.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Membership aktif yang digunakan saat kunjungan.
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Reservasi terkait (jika ada).
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Sesi personal trainer terkait (jika ada).
     */
    public function trainerBooking(): BelongsTo
    {
        return $this->belongsTo(TrainerBooking::class);
    }

    /**
     * Petugas / admin yang memvalidasi presensi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk member yang saat ini sedang aktif di gym (belum check-out).
     */
    public function scopeCurrentlyInGym(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CHECKED_IN)
            ->whereNull('check_out_at');
    }

    /**
     * Scope untuk presensi hari ini.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    /**
     * Label status bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CHECKED_IN => 'Sedang di Gym',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Class badge status Sneat / Bootstrap.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CHECKED_IN => 'bg-label-primary',
            self::STATUS_COMPLETED => 'bg-label-success',
            self::STATUS_CANCELLED => 'bg-label-secondary',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Durasi latihan yang telah berlangsung atau selesai.
     */
    public function getDurationFormattedAttribute(): string
    {
        if (! $this->check_in_at) {
            return '-';
        }

        $endTime = $this->check_out_at ?? now();
        $diffMinutes = $this->check_in_at->diffInMinutes($endTime);

        if ($diffMinutes < 1) {
            return 'Baru saja';
        }

        $hours = intdiv($diffMinutes, 60);
        $minutes = $diffMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} mnt";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }
}
