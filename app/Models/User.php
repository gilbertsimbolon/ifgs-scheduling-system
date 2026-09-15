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
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'slug', 'status', 'qr_code'])]
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
            if (empty($user->qr_code)) {
                $user->qr_code = static::generateUniqueQrCode();
            }
        });
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
     * Format: IFGS-QR-XXXXXXXXXX
     */
    public static function generateUniqueQrCode(): string
    {
        do {
            $code = 'IFGS-QR-'.strtoupper(Str::random(10));
        } while (static::where('qr_code', $code)->exists());

        return $code;
    }

    /**
     * Get SVG vector markup for the user's QR code.
     */
    public function getQrCodeSvg(int $size = 200): string
    {
        return QrCode::size($size)->generate($this->qr_code ?? $this->id);
    }

    /**
     * Find a user by their unique QR code or member code.
     */
    public static function findByQrCode(string $code): ?User
    {
        return static::where('qr_code', $code)
            ->orWhereHas('member', fn ($q) => $q->where('member_code', $code))
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
     * Kirim notifikasi reset kata sandi kustom bertema Indo Fitness Gym Sport®.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
