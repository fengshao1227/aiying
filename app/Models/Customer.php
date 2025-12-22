<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /**
     * 表名
     */
    protected $table = 'customers';

    /**
     * 主键
     */
    protected $primaryKey = 'customer_id';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'customer_name',
        'phone',
        'package_name',
        'baby_name',
        'mother_birthday',
        'baby_birthday',
        'nanny_name',
        'due_date',
        'address',
        'check_in_date',
        'check_out_date',
        'remarks',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'mother_birthday' => 'date',
        'baby_birthday' => 'date',
        'due_date' => 'date',
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
    ];

    /**
     * 关联房态记录
     */
    public function roomStatuses(): HasMany
    {
        return $this->hasMany(RoomStatus::class, 'customer_id', 'customer_id');
    }

    /**
     * 关联评分卡记录
     */
    public function scoreCardRecords(): HasMany
    {
        return $this->hasMany(ScoreCardRecord::class, 'customer_id', 'customer_id');
    }
}
