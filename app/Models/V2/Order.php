<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'customer_id',
        'delivery_type',
        'room_id',
        'room_name',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'total_amount',
        'freight_amount',
        'points_used',
        'points_discount',
        'actual_amount',
        'payment_type',
        'payment_status',
        'transaction_id',
        'paid_at',
        'order_status',
        'shipping_no',
        'shipping_company',
        'shipped_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
        'refund_status',
        'refund_reason',
        'refund_amount',
        'refund_points',
        'refund_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'customer_id' => 'integer',
            'room_id' => 'integer',
            'total_amount' => 'decimal:2',
            'freight_amount' => 'decimal:2',
            'points_used' => 'integer',
            'points_discount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'payment_status' => 'integer',
            'order_status' => 'integer',
            'refund_status' => 'integer',
            'refund_amount' => 'decimal:2',
            'refund_points' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_at' => 'datetime',
        ];
    }

    protected $hidden = ['deleted_at'];

    const DELIVERY_EXPRESS = 'express';
    const DELIVERY_ROOM = 'room';

    const PAYMENT_CASH = 'cash';
    const PAYMENT_POINTS = 'points';
    const PAYMENT_MIXED = 'mixed';

    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;

    const PAYMENT_STATUS_UNPAID = 0;
    const PAYMENT_STATUS_PAID = 1;

    const REFUND_NONE = 0;
    const REFUND_APPLYING = 1;
    const REFUND_SUCCESS = 2;
    const REFUND_REJECTED = 3;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'customer_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, int $status)
    {
        return $query->where('order_status', $status);
    }

    public function scopeByPaymentStatus($query, int $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function canPay(): bool
    {
        return $this->order_status === self::STATUS_PENDING
            && $this->payment_status === self::PAYMENT_STATUS_UNPAID;
    }

    public function canCancel(): bool
    {
        return in_array($this->order_status, [self::STATUS_PENDING, self::STATUS_PAID]);
    }

    public function canShip(): bool
    {
        return $this->order_status === self::STATUS_PAID
            && $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function canConfirm(): bool
    {
        return $this->order_status === self::STATUS_SHIPPED;
    }

    public function markAsPaid(string $transactionId): bool
    {
        $this->payment_status = self::PAYMENT_STATUS_PAID;
        $this->order_status = self::STATUS_PAID;
        $this->transaction_id = $transactionId;
        $this->paid_at = now();
        return $this->save();
    }

    public function markAsCancelled(?string $reason = null): bool
    {
        $this->order_status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancel_reason = $reason;
        return $this->save();
    }

    public function markAsShipped(string $shippingNo, string $shippingCompany): bool
    {
        $this->order_status = self::STATUS_SHIPPED;
        $this->shipping_no = $shippingNo;
        $this->shipping_company = $shippingCompany;
        $this->shipped_at = now();
        return $this->save();
    }

    public function markAsCompleted(): bool
    {
        $this->order_status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        return $this->save();
    }
}
