<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'users';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'openid',
        'unionid',
        'customer_id',
        'bind_phone',
        'nickname',
        'avatar',
        'gender',
        'phone',
        'points_balance',
        'status',
        'last_login_at',
    ];

    /**
     * 属性类型转换
     */
    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'gender' => 'integer',
            'points_balance' => 'integer',
            'status' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * 隐藏的属性
     */
    protected $hidden = [
        'openid',
        'unionid',
        'deleted_at',
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 关联客户
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * 关联商城订单
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    /**
     * 关联订餐订单
     */
    public function mealOrders(): HasMany
    {
        return $this->hasMany(MealOrder::class, 'user_id', 'id');
    }

    /**
     * 关联积分记录
     */
    public function pointsHistory(): HasMany
    {
        return $this->hasMany(PointsHistory::class, 'user_id', 'id');
    }

    /**
     * 关联购物车
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    /**
     * 检查用户是否已绑定客户
     */
    public function isBound(): bool
    {
        return !is_null($this->customer_id);
    }

    /**
     * 获取用户可用积分
     */
    public function getAvailablePoints(): int
    {
        return $this->points_balance ?? 0;
    }

    /**
     * 扣减积分
     */
    public function deductPoints(int $points, string $source, ?int $sourceId = null, ?string $description = null): bool
    {
        if ($points <= 0 || $this->points_balance < $points) {
            return false;
        }

        $balanceBefore = $this->points_balance;
        $this->points_balance -= $points;
        $this->save();

        // 记录积分变动
        PointsHistory::create([
            'user_id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => 'spend',
            'points' => -$points,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->points_balance,
            'source' => $source,
            'source_id' => $sourceId,
            'description' => $description ?? '积分消费',
        ]);

        return true;
    }

    /**
     * 增加积分
     */
    public function addPoints(int $points, string $type, string $source, ?int $sourceId = null, ?string $description = null, ?int $operatorId = null): bool
    {
        if ($points <= 0) {
            return false;
        }

        $balanceBefore = $this->points_balance;
        $this->points_balance += $points;
        $this->save();

        // 记录积分变动
        PointsHistory::create([
            'user_id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => $type,
            'points' => $points,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->points_balance,
            'source' => $source,
            'source_id' => $sourceId,
            'description' => $description ?? '积分获取',
            'operator_id' => $operatorId,
        ]);

        return true;
    }
}
