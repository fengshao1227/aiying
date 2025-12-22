<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_no',
        'user_id',
        'order_type',
        'room_number',
        'receiver_name',
        'receiver_phone',
        'receiver_province',
        'receiver_city',
        'receiver_district',
        'receiver_detail',
        'goods_amount',
        'shipping_fee',
        'points_used',
        'points_discount',
        'total_amount',
        'order_status',
        'payment_status',
        'payment_method',
        'payment_time',
        'transaction_id',
        'remark',
        'paid_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'goods_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'points_used' => 'integer',
            'points_discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'order_status' => 'integer',
            'payment_status' => 'integer',
            'payment_time' => 'datetime',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * 检查订单是否可以支付
     */
    public function canPay(): bool
    {
        // 订单状态为待支付(0) 且 支付状态为未支付(0)
        return $this->order_status === 0 && $this->payment_status === 0;
    }

    /**
     * 标记订单为已支付
     */
    public function markAsPaid(string $transactionId, array $paymentData): void
    {
        $this->update([
            'payment_status' => 1,
            'order_status' => 1, // 更新为待发货
            'paid_at' => now(),
        ]);
    }

    /**
     * 获取支付描述
     */
    public function getPaymentDescription(): string
    {
        if ($this->order_type === 'goods') {
            // 商品订单
            $itemCount = $this->items->sum('quantity');
            return sprintf('商品订单-%d件商品', $itemCount);
        } else {
            // 套餐订单
            return '家属套餐订单';
        }
    }
}
