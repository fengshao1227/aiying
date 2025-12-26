<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CachedPersonalAccessToken extends SanctumPersonalAccessToken
{
    const CACHE_PREFIX = 'sanctum:token:';
    const CACHE_TTL = 2592000; // 30天

    /**
     * 重写 findToken 方法，添加 Redis 缓存层
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            return static::findTokenFromCache($token);
        }

        [$id, $token] = explode('|', $token, 2);

        $cacheKey = self::CACHE_PREFIX . $id;

        $instance = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return static::find($id);
        });

        if ($instance && hash_equals($instance->token, hash('sha256', $token))) {
            return $instance;
        }

        return null;
    }

    /**
     * 从缓存中查找 Token（无 ID 前缀的情况）
     */
    protected static function findTokenFromCache($token)
    {
        $hashedToken = hash('sha256', $token);
        $cacheKey = self::CACHE_PREFIX . 'hash:' . $hashedToken;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($hashedToken) {
            return static::where('token', $hashedToken)->first();
        });
    }

    /**
     * 删除时清除缓存
     */
    public function delete()
    {
        Cache::forget(self::CACHE_PREFIX . $this->id);
        Cache::forget(self::CACHE_PREFIX . 'hash:' . $this->token);

        return parent::delete();
    }

    /**
     * 清除指定 Token ID 的缓存
     */
    public static function clearTokenCache($tokenId)
    {
        Cache::forget(self::CACHE_PREFIX . $tokenId);
    }
}
