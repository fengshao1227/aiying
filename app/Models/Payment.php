<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_no',
        'transaction_id',
        'payment_method',
        'amount',
        'status',
        'paid_at',
        'payment_data',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'decimal:2',
            'status' => 'integer',
            'paid_at' => 'datetime',
            'payment_data' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
