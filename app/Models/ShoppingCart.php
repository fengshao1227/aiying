<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $table = 'shopping_cart';

    protected $fillable = [
        'user_id',
        'product_id',
        'sku_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'product_id' => 'integer',
            'sku_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function specification()
    {
        return $this->belongsTo(ProductSpecification::class, 'sku_id');
    }
}
