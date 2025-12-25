<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'name',
        'cover_image',
        'images',
        'delivery_type',
        'original_price',
        'price',
        'points_price',
        'stock',
        'sales',
        'unit',
        'summary',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'images' => 'array',
            'original_price' => 'decimal:2',
            'price' => 'decimal:2',
            'points_price' => 'integer',
            'stock' => 'integer',
            'sales' => 'integer',
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    protected $hidden = [
        'deleted_at',
    ];

    const STATUS_OFF = 0;      // 下架
    const STATUS_ON = 1;       // 上架

    const DELIVERY_EXPRESS = 'express';  // 快递
    const DELIVERY_ROOM = 'room';        // 送到房间

    /**
     * 关联分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * 获取上架商品
     */
    public function scopeOnSale($query)
    {
        return $query->where('status', self::STATUS_ON);
    }

    /**
     * 按排序获取
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'desc')->orderBy('id', 'desc');
    }

    /**
     * 按分类筛选
     */
    public function scopeInCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * 按配送类型筛选
     */
    public function scopeByDeliveryType($query, $deliveryType)
    {
        if ($deliveryType) {
            return $query->where('delivery_type', $deliveryType);
        }
        return $query;
    }

    /**
     * 检查库存是否充足
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * 扣减库存
     */
    public function deductStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }
        $this->stock -= $quantity;
        $this->sales += $quantity;
        return $this->save();
    }

    /**
     * 恢复库存
     */
    public function restoreStock(int $quantity): bool
    {
        $this->stock += $quantity;
        if ($this->sales >= $quantity) {
            $this->sales -= $quantity;
        }
        return $this->save();
    }

    /**
     * 是否支持积分兑换
     */
    public function supportsPoints(): bool
    {
        return !is_null($this->points_price) && $this->points_price > 0;
    }

    /**
     * 是否支持现金购买
     */
    public function supportsCash(): bool
    {
        return $this->price > 0;
    }
}
