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
}
