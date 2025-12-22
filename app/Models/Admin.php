<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    /**
     * 表名
     */
    protected $table = 'admins';

    /**
     * 主键
     */
    protected $primaryKey = 'admin_id';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'username',
        'password',
        'real_name',
        'email',
        'phone',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * 隐藏的属性
     */
    protected $hidden = [
        'password',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'status' => 'integer',
        'last_login_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用

    /**
     * 设置密码（自动bcrypt加密）
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * 验证密码
     */
    public function checkPassword($password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * 更新最后登录信息
     */
    public function updateLastLogin(string $ip)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
