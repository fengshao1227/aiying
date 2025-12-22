<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'openid',
        'phone',
        'name',
        'avatar',
        'gender',
        'points_balance',
        'last_login_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender' => 'integer',
            'points_balance' => 'integer',
            'status' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    // 关联关系
    public function shoppingCart()
    {
        return $this->hasMany(ShoppingCart::class);
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function pointsHistory()
    {
        return $this->hasMany(PointsHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
