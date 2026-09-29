<?php

namespace App\Models;

use Database\Factories\TrainerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trainer extends Member
{
    /** @use HasFactory<TrainerFactory> */
    use HasFactory;

    protected $table = 'members';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * Booted model event to filter only trainers and set defaults.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('trainer', function (Builder $query) {
            $query->where('is_trainer', true);
        });

        static::creating(function (Trainer $trainer) {
            $trainer->is_trainer = true;
            if (empty($trainer->trainer_status)) {
                $trainer->trainer_status = $trainer->attributes['status'] ?? 'active';
            }
            if (empty($trainer->member_code)) {
                $trainer->member_code = $trainer->attributes['trainer_code'] ?? Member::generateUniqueMemberCode();
            }
        });

        static::saving(function (Trainer $trainer) {
            if (isset($trainer->attributes['status'])) {
                $trainer->trainer_status = $trainer->attributes['status'];
                unset($trainer->attributes['status']);
            }
            if (isset($trainer->attributes['trainer_code'])) {
                $trainer->member_code = $trainer->attributes['trainer_code'];
                unset($trainer->attributes['trainer_code']);
            }
        });
    }

    /**
     * Scope query untuk trainer berstatus aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('trainer_status', 'active');
    }

    /**
     * Alias accessor for trainer_code (uses member_code).
     */
    public function getTrainerCodeAttribute(): string
    {
        return $this->member_code;
    }

    /**
     * Mutator for trainer_code (sets member_code).
     */
    public function setTrainerCodeAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['member_code'] = $value;
        }
    }

    /**
     * Alias accessor for status (uses trainer_status).
     */
    public function getStatusAttribute(): string
    {
        return $this->trainer_status ?? 'active';
    }

    /**
     * Mutator for status (sets trainer_status).
     */
    public function setStatusAttribute(string $value): void
    {
        $this->attributes['trainer_status'] = $value;
    }

    /**
     * Relasi ke User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
