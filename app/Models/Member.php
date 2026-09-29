<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Fillable(['user_id', 'member_code', 'phone', 'is_trainer', 'specialization', 'bio', 'trainer_status'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_trainer' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->member_code)) {
                $member->member_code = $member->user?->user_code ?? static::generateUniqueMemberCode();
            }
        });
    }

    /**
     * Scope untuk mengambil member yang terdaftar sebagai trainer.
     */
    public function scopeTrainers(Builder $query): Builder
    {
        return $query->where('is_trainer', true);
    }

    /**
     * Scope untuk mengambil trainer yang berstatus aktif.
     */
    public function scopeActiveTrainers(Builder $query): Builder
    {
        return $query->where('is_trainer', true)
            ->where('trainer_status', 'active');
    }

    /**
     * Get the user account that owns the member profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all membership packages purchased by this member.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all transactions performed by this member.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all visit reservations made by this member.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get all scheduled visits for this member.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get all gym attendance records for this member.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the active check-in record for today (currently in gym, not checked out yet).
     */
    public function currentAttendanceToday(): ?Attendance
    {
        return $this->attendances()
            ->today()
            ->currentlyInGym()
            ->latest('check_in_at')
            ->first();
    }

    /**
     * Get the current active membership for this member.
     * Mendukung sistem akumulasi: Jika member aktif hari ini, mengembalikan membership aktif dengan masa berlaku terpanjang.
     */
    public function activeMembership(): ?Membership
    {
        $hasCurrentActive = $this->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();

        if (! $hasCurrentActive) {
            return null;
        }

        return $this->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();
    }

    /**
     * Get the active membership for a specific product, taking accumulations into account.
     */
    public function activeMembershipForProduct(int $productId): ?Membership
    {
        return $this->memberships()
            ->where('product_id', $productId)
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();
    }

    /**
     * Check whether the member has an active membership subscription.
     */
    public function hasActiveMembership(): bool
    {
        return $this->activeMembership() !== null;
    }

    /**
     * Memeriksa apakah seluruh paket membership aktif member hanyalah paket visit (24 jam).
     */
    public function hasOnlyDailyVisitMembership(): bool
    {
        $activeMemberships = $this->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->with('product')
            ->get();

        if ($activeMemberships->isEmpty()) {
            return false;
        }

        return $activeMemberships->every(fn (Membership $m) => $m->isDailyVisit());
    }

    /**
     * Memeriksa apakah member memenuhi syarat untuk membuat reservasi kunjungan.
     * Seluruh member yang memiliki paket membership aktif dapat melakukan reservasi.
     */
    public function canMakeReservation(): bool
    {
        return $this->hasActiveMembership();
    }

    /**
     * Generate a unique member code for gym identification.
     * Format: IFGS-YYYYMM-XXXX (e.g. IFGS-202609-0001)
     */
    public static function generateUniqueMemberCode(): string
    {
        $prefix = 'IFGS-'.now()->format('Ym').'-';

        $lastMember = static::where('member_code', 'like', "{$prefix}%")
            ->orderByDesc('member_code')
            ->first();

        $nextSequence = 1;
        if ($lastMember && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $lastMember->member_code, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        do {
            $code = $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
            $nextSequence++;
        } while (static::where('member_code', $code)->exists());

        return $code;
    }

    /**
     * Get the member's unique QR code string (from associated user or member code).
     */
    public function getQrCodeAttribute(): string
    {
        return $this->user?->qr_code ?? $this->member_code;
    }

    /**
     * Get SVG vector markup for the member's QR code.
     */
    public function getQrCodeSvg(int $size = 200): string
    {
        return QrCode::size($size)->generate($this->qr_code);
    }

    /**
     * Accessor alias for phone number.
     */
    public function getPhoneNumberAttribute(): ?string
    {
        return $this->phone;
    }

    /**
     * Format nomor WhatsApp dengan kode negara 62.
     */
    public function getFormattedWhatsAppNumber(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($cleaned, '0')) {
            return '62'.substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Link direct chat WhatsApp untuk menghubungi trainer.
     */
    public function getWhatsAppUrlAttribute(): ?string
    {
        $number = $this->getFormattedWhatsAppNumber();
        if (! $number) {
            return null;
        }

        $trainerName = $this->user?->name ?? 'Coach';
        $message = urlencode("Halo {$trainerName}, saya ingin konsultasi mengenai program latihan di Indo Fitness Gym Sport.");

        return "https://wa.me/{$number}?text={$message}";
    }
}
