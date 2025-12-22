<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否有token
        if (!$request->bearerToken()) {
            return response()->json([
                'code' => 401,
                'message' => '未登录或登录已过期',
                'data' => null
            ], 401);
        }

        // 使用Sanctum验证token
        $admin = $request->user();

        if (!$admin || !($admin instanceof Admin)) {
            return response()->json([
                'code' => 401,
                'message' => '认证失败',
                'data' => null
            ], 401);
        }

        // 检查管理员状态
        if ($admin->status !== Admin::STATUS_ENABLED) {
            return response()->json([
                'code' => 403,
                'message' => '账号已被禁用',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
