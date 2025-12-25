<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'selected',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'selected' => 'boolean',
        ];
    }

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 关联商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * 获取选中的购物车项
     */
    public function scopeSelected($query)
    {
        return $query->where('selected', true);
    }

    /**
     * 按用户筛选
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 计算小计
     */
    public function getSubtotal(): float
    {
        if (!$this->product) {
            return 0;
        }
        return (float)$this->product->price * $this->quantity;
    }

    /**
     * 计算积分小计
     */
    public function getPointsSubtotal(): ?int
    {
        if (!$this->product || !$this->product->points_price) {
            return null;
        }
        return $this->product->points_price * $this->quantity;
    }

    /**
     * 检查商品是否有效
     */
    public function isProductValid(): bool
    {
        return $this->product
            && $this->product->status === Product::STATUS_ON
            && $this->product->hasStock($this->quantity);
    }
}
