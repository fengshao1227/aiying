<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'wallet_transactions';

    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'source',
        'source_id',
        'transaction_id',
        'status',
        'operator_id',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'wallet_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'source_id' => 'integer',
            'operator_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    const TYPE_TOPUP = 'topup';
    const TYPE_CONSUME = 'consume';
    const TYPE_ADJUST = 'adjust';
    const TYPE_FREEZE = 'freeze';
    const TYPE_UNFREEZE = 'unfreeze';

    const SOURCE_WECHAT = 'wechat';
    const SOURCE_MALL_ORDER = 'mall_order';
    const SOURCE_MEAL_ORDER = 'meal_order';
    const SOURCE_ADMIN = 'admin';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_TOPUP => '充值',
            self::TYPE_CONSUME => '消费',
            self::TYPE_ADJUST => '调整',
            self::TYPE_FREEZE => '冻结',
            self::TYPE_UNFREEZE => '解冻',
            default => $this->type,
        };
    }

    public function getDirectionAttribute(): string
    {
        return $this->amount >= 0 ? 'in' : 'out';
    }
}
