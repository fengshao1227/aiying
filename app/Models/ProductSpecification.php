<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku_code',
        'spec_values',
        'price',
        'stock',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'spec_values' => 'array',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems()
    {
        return $this->hasMany(ShoppingCart::class, 'specification_id');
    }
}
