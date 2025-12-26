<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // 缓存时间常量（分钟）
    const TTL_SHORT = 5;        // 5分钟 - Dashboard统计
    const TTL_MEDIUM = 30;      // 30分钟 - 分类、配置
    const TTL_LONG = 60;        // 1小时 - 系统配置
    const TTL_DAY = 1440;       // 1天

    // 缓存键前缀
    const PREFIX_DASHBOARD = 'dashboard:';
    const PREFIX_CONFIG = 'config:';
    const PREFIX_CATEGORY = 'category:';
    const PREFIX_PRODUCT = 'product:';
    const PREFIX_ROOM = 'room:';
    const PREFIX_MEAL = 'meal:';

    /**
     * 获取缓存，不存在则执行回调并缓存
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        return Cache::remember($key, $ttl * 60, $callback);
    }

    /**
     * 清除指定前缀的所有缓存
     */
    public static function forgetByPrefix(string $prefix): void
    {
        // Redis支持模式删除
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys(config('cache.prefix') . $prefix . '*');
            foreach ($keys as $key) {
                $cleanKey = str_replace(config('cache.prefix'), '', $key);
                Cache::forget($cleanKey);
            }
        }
    }

    /**
     * 清除单个缓存
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Dashboard缓存键
     */
    public static function dashboardKey(): string
    {
        return self::PREFIX_DASHBOARD . 'overview';
    }

    /**
     * 系统配置缓存键
     */
    public static function configKey(string $group = 'all'): string
    {
        return self::PREFIX_CONFIG . $group;
    }

    /**
     * 分类缓存键
     */
    public static function categoryKey(string $suffix = 'list'): string
    {
        return self::PREFIX_CATEGORY . $suffix;
    }

    /**
     * 商品缓存键
     */
    public static function productKey(string $suffix = 'hot'): string
    {
        return self::PREFIX_PRODUCT . $suffix;
    }

    /**
     * 房间缓存键
     */
    public static function roomKey(string $suffix = 'list'): string
    {
        return self::PREFIX_ROOM . $suffix;
    }

    /**
     * 餐饮配置缓存键
     */
    public static function mealConfigKey(): string
    {
        return self::PREFIX_MEAL . 'config';
    }

    /**
     * 清除Dashboard缓存
     */
    public static function clearDashboard(): void
    {
        self::forget(self::dashboardKey());
    }

    /**
     * 清除配置缓存
     */
    public static function clearConfig(): void
    {
        self::forgetByPrefix(self::PREFIX_CONFIG);
    }

    /**
     * 清除分类缓存
     */
    public static function clearCategory(): void
    {
        self::forgetByPrefix(self::PREFIX_CATEGORY);
    }

    /**
     * 清除商品缓存
     */
    public static function clearProduct(): void
    {
        self::forgetByPrefix(self::PREFIX_PRODUCT);
        self::clearDashboard(); // 商品变更影响Dashboard热销榜
    }

    /**
     * 清除房间缓存
     */
    public static function clearRoom(): void
    {
        self::forgetByPrefix(self::PREFIX_ROOM);
        self::clearDashboard(); // 房间变更影响入住率
    }

    /**
     * 清除餐饮配置缓存
     */
    public static function clearMealConfig(): void
    {
        self::forget(self::mealConfigKey());
    }
}
