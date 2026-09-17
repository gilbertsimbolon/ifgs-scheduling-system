<?php

namespace App\Models;

use Database\Factories\TrainerBookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_code',
    'trainer_id',
    'member_id',
    'time_slot_id',
    'session_date',
    'status',
    'training_focus',
    'notes',
    'rejection_reason',
    'approved_at',
    'completed_at',
])]
class TrainerBooking extends Model
{
    /** @use HasFactory<TrainerBookingFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
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
            'session_date' => 'date',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Booted event to auto-generate booking code.
     */
    protected static function booted(): void
    {
        static::creating(function (TrainerBooking $booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = static::generateBookingCode();
            }
        });
    }

    /**
     * Generate unique booking code: TRB-YYYYMM-XXXX.
     */
    public static function generateBookingCode(): string
    {
        $prefix = 'TRB-'.date('Ym').'-';
        $lastBooking = static::where('booking_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastBooking) {
            $lastNumber = (int) substr($lastBooking->booking_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        do {
            $code = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Trainer yang ditugaskan / dipilih.
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    /**
     * Member yang memesan sesi latihan.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Time slot / sesi jam latihan.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Label status dalam Bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Belum Disetujui',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_IN_PROGRESS => 'Sedang Berjalan',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS class Bootstrap 5 / Sneat.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-label-warning',
            self::STATUS_APPROVED => 'bg-label-primary',
            self::STATUS_IN_PROGRESS => 'bg-label-info',
            self::STATUS_COMPLETED => 'bg-label-success',
            self::STATUS_REJECTED => 'bg-label-danger',
            self::STATUS_CANCELLED => 'bg-label-secondary',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Generate format pesan konfirmasi kesediaan trainer via WhatsApp.
     */
    public function getWhatsAppConfirmationMessage(): string
    {
        $memberName = $this->member?->user?->name ?? 'Member';
        $trainerName = $this->trainer?->user?->name ?? 'Trainer';
        $trainerSpec = $this->trainer?->specialization ?? 'General Fitness';
        $trainerPhone = $this->trainer?->phone ?? '-';
        $trainerBio = $this->trainer?->bio ? "\n• Profil: {$this->trainer->bio}" : '';

        $sessionDate = $this->session_date ? $this->session_date->translatedFormat('l, d F Y') : '-';
        $bookingCode = $this->booking_code;

        return "Halo {$memberName}! 👋\n\n"
            ."Saya Coach *{$trainerName}* dari *Indo Fitness Gym Sport*.\n\n"
            ."Menindaklanjuti permohonan pendampingan latihan Anda (*{$bookingCode}*), dengan ini saya menyatakan *BERSEDIA* mendampingi latihan Anda pada:\n"
            ."📅 *Hari & Tanggal:* {$sessionDate}\n"
            ."⏰ *Jam Operasional Gym:* 08:00 - 20:00 WITA\n\n"
            ."👤 *Data Diri Personal Trainer:*\n"
            ."• Nama: Coach {$trainerName}\n"
            ."• Spesialisasi: {$trainerSpec}\n"
            ."• No. HP/WA: {$trainerPhone}"
            ."{$trainerBio}\n\n"
            ."Sampai jumpa di gym! Silakan lakukan scan kehadiran saat tiba di gym. Tetap semangat! 💪🔥\n"
            .'*Indo Fitness Gym Sport Tondano*';
    }

    /**
     * Dapatkan URL WhatsApp langsung untuk konfirmasi ke nomor member.
     * Generate format pesan penolakan / berhalangan via WhatsApp.
     */
    public function getWhatsAppRejectionMessage(?string $reason = null): string
    {
        $memberName = $this->member?->user?->name ?? 'Member';
        $trainerName = $this->trainer?->user?->name ?? 'Trainer';
        $sessionDate = $this->session_date ? $this->session_date->translatedFormat('l, d F Y') : '-';
        $bookingCode = $this->booking_code;
        $reasonText = $reason ?: ($this->rejection_reason ?: 'Ada jadwal khusus yang tidak dapat ditinggalkan.');

        return "Halo {$memberName}! 🙏\n\n"
            ."Saya Coach *{$trainerName}* dari *Indo Fitness Gym Sport*.\n\n"
            ."Terkait permohonan pendampingan latihan Anda (*{$bookingCode}*) untuk hari *{$sessionDate}*, saya memohon maaf sebesar-besarnya karena *BERHALANGAN / TIDAK DAPAT MENDAMPINGI* pada tanggal tersebut.\n\n"
            ."📋 *Alasan:* {$reasonText}\n\n"
            ."Anda dapat memilih jadwal hari lain atau memilih personal trainer lainnya yang tersedia di sistem IFGS. Terima kasih atas pengertiannya! 🙏\n"
            .'*Indo Fitness Gym Sport Tondano*';
    }

    /**
     * Dapatkan URL WhatsApp langsung untuk konfirmasi persetujuan ke nomor member.
     */
    public function getWhatsAppUrlAttribute(): ?string
    {
        $phone = $this->member?->phone ?? $this->member?->phone_number;
        if (! $phone || $phone === '-') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        } elseif (! str_starts_with($cleaned, '62')) {
            $cleaned = '62'.$cleaned;
        }

        return 'https://wa.me/'.$cleaned.'?text='.rawurlencode($this->getWhatsAppConfirmationMessage());
    }

    /**
     * Dapatkan URL WhatsApp langsung untuk penolakan ke nomor member.
     */
    public function getWhatsAppRejectionUrl(?string $reason = null): ?string
    {
        $phone = $this->member?->phone ?? $this->member?->phone_number;
        if (! $phone || $phone === '-') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        } elseif (! str_starts_with($cleaned, '62')) {
            $cleaned = '62'.$cleaned;
        }

        return 'https://wa.me/'.$cleaned.'?text='.rawurlencode($this->getWhatsAppRejectionMessage($reason));
    }
}
