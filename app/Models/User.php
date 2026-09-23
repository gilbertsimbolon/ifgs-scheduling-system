<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'slug', 'user_code', 'status', 'qr_code', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->slug)) {
                $user->slug = static::generateUniqueSlug($user->name);
            }
            if (empty($user->user_code)) {
                $user->user_code = static::generateUniqueUserCode();
            }
            if (empty($user->qr_code)) {
                $user->qr_code = $user->user_code;
            }
        });
    }

    /**
     * Generate a unique user code for official identification.
     * Format: IFGS-YYYYMM-XXXX (e.g. IFGS-202609-0001)
     */
    public static function generateUniqueUserCode(): string
    {
        $prefix = 'IFGS-'.now()->format('Ym').'-';

        $last = static::where('user_code', 'like', "{$prefix}%")
            ->orderByDesc('user_code')
            ->first();

        $nextSequence = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last->user_code, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        do {
            $code = $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
            $nextSequence++;
        } while (static::where('user_code', $code)->exists() || Member::where('member_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique slug based on user name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'user';
        }

        $slug = $baseSlug;
        $count = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Generate a unique QR code string for gym attendance.
     * Defaults to user_code format: IFGS-YYYYMM-XXXX
     */
    public static function generateUniqueQrCode(): string
    {
        return static::generateUniqueUserCode();
    }

    /**
     * Get SVG vector markup for the user's QR code.
     */
    public function getQrCodeSvg(int $size = 200): string
    {
        $code = $this->qr_code ?: ($this->member?->member_code ?: ($this->user_code ?: "IFGS-UID-{$this->id}"));

        return QrCode::size($size)->generate($code);
    }

    /**
     * Find a user by their unique QR code, user code, member code, phone, email, or ID.
     */
    public static function findByQrCode(string $code): ?User
    {
        $code = trim($code);

        return static::where('qr_code', $code)
            ->orWhere('user_code', $code)
            ->orWhere('email', $code)
            ->when(is_numeric($code), fn ($q) => $q->orWhere('id', (int) $code))
            ->orWhereHas('member', fn ($q) => $q->where('member_code', $code)->orWhere('phone', $code))
            ->when(is_numeric($code), fn ($q) => $q->orWhere('id', (int) $code))
            ->orWhereHas('member', fn ($q) => $q->where('member_code', $code)->orWhere('phone', $code))
            ->first();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the member profile associated with the user.
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Get the trainer profile associated with the user.
     */
    public function trainer(): HasOne
    {
        return $this->hasOne(Trainer::class);
    }

    /**
     * Get all transactions processed by this staff user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get phone number from related member or trainer profile.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->member?->phone ?? $this->trainer?->phone;
    }

    /**
     * Get avatar image public URL if exists.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::url($this->avatar);
        }

        return null;
    }

    /**
     * Get user initials (uppercase 1-2 letters from name).
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
        if (empty($name)) {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);
        if (count($parts) >= 2 && ! empty($parts[1])) {
            return strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * Kirim notifikasi reset kata sandi kustom bertema Indo Fitness Gym Sport®.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
