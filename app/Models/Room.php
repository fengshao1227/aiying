<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /**
     * 表名
     */
    protected $table = 'rooms';

    /**
     * 主键
     */
    protected $primaryKey = 'room_id';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'room_name',
        'floor',
        'room_type',
        'color_code',
        'ac_group_id',
        'display_order',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'floor' => 'integer',
        'ac_group_id' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * 关联房态记录
     */
    public function roomStatuses(): HasMany
    {
        return $this->hasMany(RoomStatus::class, 'room_id', 'room_id');
    }
}
