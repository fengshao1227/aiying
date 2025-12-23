<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMealOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_no',
        'user_id',
        'package_id',
        'room_name',
        'customer_phone',
        'meal_dates',
        'meal_times',
        'quantity',
        'unit_price',
        'total_amount',
        'order_status',
        'payment_status',
        'remarks',
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'meal_dates' => 'array',
            'meal_times' => 'array',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'quantity' => 'integer',
            'order_status' => 'integer',
            'payment_status' => 'integer',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联套餐
     */
    public function package()
    {
        return $this->belongsTo(FamilyMealPackage::class, 'package_id');
    }

    /**
     * 检查是否可以支付
     */
    public function canPay(): bool
    {
        return $this->order_status === 0 && $this->payment_status === 0;
    }

    /**
     * 标记为已支付
     */
    public function markAsPaid(string $transactionId = null): void
    {
        $this->update([
            'order_status' => 1,
            'payment_status' => 1,
            'paid_at' => now(),
        ]);
    }

    /**
     * 获取支付描述
     */
    public function getPaymentDescription(): string
    {
        return "家属订餐-{$this->room_name}";
    }

    /**
     * 计算总份数
     */
    public function getTotalPortionsAttribute(): int
    {
        if (!$this->meal_times) {
            return $this->quantity;
        }

        $total = 0;
        foreach ($this->meal_times as $portions) {
            $total += (int) $portions;
        }
        return $total;
    }

    /**
     * 计算总天数
     */
    public function getTotalDaysAttribute(): int
    {
        return is_array($this->meal_dates) ? count($this->meal_dates) : 0;
    }
}
