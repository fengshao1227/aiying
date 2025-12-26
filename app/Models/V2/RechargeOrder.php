<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeOrder extends Model
{
    use HasFactory;

    protected $table = 'recharge_orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'wallet_id',
        'amount',
        'status',
        'transaction_id',
        'prepay_id',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'wallet_id' => 'integer',
            'amount' => 'decimal:2',
            'status' => 'integer',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isExpired(): bool
    {
        if (!$this->expired_at) {
            return false;
        }
        return now()->gt($this->expired_at);
    }

    public static function generateOrderNo(): string
    {
        return 'RC' . date('YmdHis') . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
