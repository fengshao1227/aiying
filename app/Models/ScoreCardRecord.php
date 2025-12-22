<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreCardRecord extends Model
{
    /**
     * 表名
     */
    protected $table = 'score_card_records';

    /**
     * 主键
     */
    protected $primaryKey = 'record_id';

    /**
     * 关闭 updated_at 字段
     */
    const UPDATED_AT = null;

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'customer_id',
        'card_number',
        'record_date',
        'score_data',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'customer_id' => 'integer',
        'card_number' => 'integer',
        'record_date' => 'date',
        'score_data' => 'array',  // JSON自动转为数组
    ];

    /**
     * 关联客户
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
