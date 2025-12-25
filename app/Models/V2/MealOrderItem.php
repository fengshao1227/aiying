<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealOrderItem extends Model
{
    protected $table = 'meal_order_items';

    protected $fillable = [
        'meal_order_id',
        'meal_date',
        'meal_type',
        'meal_name',
        'quantity',
        'unit_price',
        'subtotal',
        'delivery_status',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'meal_order_id' => 'integer',
            'meal_date' => 'date',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'delivery_status' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }

    const DELIVERY_PENDING = 0;
    const DELIVERY_COMPLETED = 1;

    public function mealOrder(): BelongsTo
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id', 'id');
    }

    public function markAsDelivered(): bool
    {
        $this->delivery_status = self::DELIVERY_COMPLETED;
        $this->delivered_at = now();
        return $this->save();
    }
}
