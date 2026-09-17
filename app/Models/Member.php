<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Fillable(['user_id', 'member_code', 'phone'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

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
     * Get the current active membership for this member.
     */
    public function activeMembership(): ?Membership
    {
        return $this->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest('end_date')
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
}
