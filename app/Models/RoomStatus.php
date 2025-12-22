<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomStatus extends Model
{
    /**
     * 表名
     */
    protected $table = 'room_status';

    /**
     * 主键
     */
    protected $primaryKey = 'record_id';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'room_id',
        'customer_id',
        'check_in_date',
        'check_out_date',
        'status',
        'record_month',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'room_id' => 'integer',
        'customer_id' => 'integer',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'status' => 'integer',
    ];

    /**
     * 状态常量
     */
    const STATUS_VACANT = 0;       // 空闲
    const STATUS_OCCUPIED = 1;     // 已入住
    const STATUS_MAINTENANCE = 2;  // 维护中

    /**
     * 关联房间
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * 关联客户
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
