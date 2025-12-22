<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMealPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cover_image',
        'price',
        'duration_days',
        'description',
        'services',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'services' => 'array',
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    /**
     * 套餐包含的商品
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'family_meal_package_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
