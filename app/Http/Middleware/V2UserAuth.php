<?php

namespace App\Http\Middleware;

use App\Models\V2\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class V2UserAuth
{
    /**
     * 处理请求
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 从 Header 获取 openid
        $openid = $request->header('X-Openid');

        if (!$openid) {
            return response()->json([
                'code' => 401,
                'message' => '未登录，请先登录',
                'data' => null,
            ], 401);
        }

        // 查找用户
        $user = User::where('openid', $openid)->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '用户不存在，请重新登录',
                'data' => null,
            ], 401);
        }

        // 检查用户状态
        if ($user->status !== User::STATUS_ENABLED) {
            return response()->json([
                'code' => 403,
                'message' => '账号已被禁用',
                'data' => null,
            ], 403);
        }

        // 将用户信息挂载到请求对象
        $request->attributes->set('v2_user', $user);

        return $next($request);
    }
}
