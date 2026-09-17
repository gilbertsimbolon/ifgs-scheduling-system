<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'invoice_number',
    'member_id',
    'user_id',
    'payment_method_id',
    'total_amount',
    'paid_amount',
    'change_amount',
    'status',
    'notes',
    'payment_proof',
    'rejection_reason',
    'approved_at',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_PENDING,
        self::STATUS_REJECTED,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the member that owns the transaction.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the staff user (cashier/admin) who processed the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the line items belonging to this transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get the memberships generated from this transaction.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get the primary membership record linked to this transaction.
     */
    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class);
    }

    /**
     * Format total amount to Rupiah string.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_amount, 0, ',', '.');
    }

    /**
     * Format paid amount to Rupiah string.
     */
    public function getFormattedPaidAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->paid_amount, 0, ',', '.');
    }

    /**
     * Format change amount to Rupiah string.
     */
    public function getFormattedChangeAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->change_amount, 0, ',', '.');
    }

    /**
     * Status badge CSS class for Sneat / Bootstrap 5.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-label-success',
            self::STATUS_CANCELLED, self::STATUS_REJECTED => 'bg-label-danger',
            self::STATUS_PENDING => 'bg-label-warning',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PENDING => 'Menunggu Validasi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the public URL for uploaded payment proof screenshot.
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        if ($this->payment_proof) {
            return asset('storage/'.$this->payment_proof);
        }

        return null;
    }

    /**
     * Generate unique sequential invoice number for transactions.
     * Format: TRX-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "TRX-{$dateStr}-";

        $lastTransaction = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->invoice_number, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
