<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'cover_image',
        'original_price',
        'price',
        'stock',
        'sales',
        'unit',
        'summary',
        'description',
        'tech_params',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'original_price' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'sales' => 'integer',
            'sort_order' => 'integer',
            'status' => 'integer',
            'tech_params' => 'array',
        ];
    }

    // 关联关系
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function cartItems()
    {
        return $this->hasMany(ShoppingCart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function packages()
    {
        return $this->belongsToMany(FamilyMealPackage::class, 'family_meal_package_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
