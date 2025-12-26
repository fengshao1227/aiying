<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'user_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'status',
        'payment_password',
        'password_set_at',
        'password_fail_count',
        'password_locked_until',
        'frozen_at',
        'frozen_by',
        'frozen_reason',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'balance' => 'decimal:2',
            'status' => 'integer',
            'password_set_at' => 'datetime',
            'password_fail_count' => 'integer',
            'password_locked_until' => 'datetime',
            'frozen_at' => 'datetime',
            'frozen_by' => 'integer',
            'version' => 'integer',
        ];
    }

    protected $hidden = [
        'payment_password',
    ];

    const STATUS_NORMAL = 0;
    const STATUS_FROZEN = 1;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'id');
    }

    public function rechargeOrders(): HasMany
    {
        return $this->hasMany(RechargeOrder::class, 'wallet_id', 'id');
    }

    public function isFrozen(): bool
    {
        return $this->status === self::STATUS_FROZEN;
    }

    public function hasPassword(): bool
    {
        return !empty($this->payment_password);
    }

    public function isPasswordLocked(): bool
    {
        if (!$this->password_locked_until) {
            return false;
        }
        return now()->lt($this->password_locked_until);
    }

    public function canPay(float $amount): bool
    {
        return !$this->isFrozen() && $this->balance >= $amount;
    }
}
