<?php

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'status', 'type', 'account_number', 'account_name', 'qr_image'])]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    public const TYPE_CASH = 'cash';

    public const TYPE_BANK_TRANSFER = 'bank_transfer';

    public const TYPE_EWALLET = 'ewallet';

    public const TYPE_QRIS = 'qris';

    public const TYPES = [
        self::TYPE_CASH,
        self::TYPE_BANK_TRANSFER,
        self::TYPE_EWALLET,
        self::TYPE_QRIS,
    ];

    /**
     * Get all memberships paid using this payment method.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all transactions paid using this payment method.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Determine if this payment method is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Badge CSS class for Sneat theme.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-label-success',
            self::STATUS_INACTIVE => 'bg-label-secondary',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Human-friendly status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Non-Aktif',
            default => ucfirst($this->status),
        };
    }

    /**
     * Human-friendly payment method type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CASH => 'Tunai',
            self::TYPE_BANK_TRANSFER => 'Transfer Bank',
            self::TYPE_EWALLET => 'E-Wallet',
            self::TYPE_QRIS => 'QRIS',
            default => ucfirst($this->type ?? 'Tunai'),
        };
    }

    /**
     * Get the accessible public URL for the QRIS image.
     */
    public function getQrImageUrlAttribute(): ?string
    {
        if (! $this->qr_image) {
            return null;
        }

        if (str_starts_with($this->qr_image, 'http://') || str_starts_with($this->qr_image, 'https://')) {
            return $this->qr_image;
        }

        if (str_starts_with($this->qr_image, 'img/')) {
            return asset($this->qr_image);
        }

        return asset('storage/'.$this->qr_image);
    }
}
