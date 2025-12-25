<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'icon',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    protected $hidden = [
        'deleted_at',
    ];

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 关联商品
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /**
     * 获取启用的分类
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 按排序获取
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'desc')->orderBy('id', 'asc');
    }
}
