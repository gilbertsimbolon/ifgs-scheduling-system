<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
                $member->member_code = static::generateUniqueMemberCode();
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
}
