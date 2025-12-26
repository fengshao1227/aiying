<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CachedPersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * 管理员登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => '请输入用户名',
            'password.required' => '请输入密码',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        // 查找管理员
        $admin = Admin::where('username', $request->username)->first();

        if (!$admin) {
            return response()->json([
                'code' => 401,
                'message' => '用户名或密码错误',
                'data' => null
            ], 401);
        }

        // 验证账号状态
        if ($admin->status !== Admin::STATUS_ENABLED) {
            return response()->json([
                'code' => 403,
                'message' => '账号已被禁用',
                'data' => null
            ], 403);
        }

        // 验证密码
        if (!$admin->checkPassword($request->password)) {
            return response()->json([
                'code' => 401,
                'message' => '用户名或密码错误',
                'data' => null
            ], 401);
        }

        // 创建token
        $token = $admin->createToken('admin-token')->plainTextToken;

        // 更新最后登录信息
        $admin->updateLastLogin($request->ip());

        return response()->json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'admin' => [
                    'admin_id' => $admin->admin_id,
                    'username' => $admin->username,
                    'real_name' => $admin->real_name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                ]
            ]
        ]);
    }

    /**
     * 管理员登出
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        CachedPersonalAccessToken::clearTokenCache($token->id);
        $token->delete();

        return response()->json([
            'code' => 200,
            'message' => '登出成功',
            'data' => null
        ]);
    }

    /**
     * 获取当前管理员信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'admin_id' => $admin->admin_id,
                'username' => $admin->username,
                'real_name' => $admin->real_name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'status' => $admin->status,
                'last_login_at' => $admin->last_login_at?->format('Y-m-d H:i:s'),
                'last_login_ip' => $admin->last_login_ip,
            ]
        ]);
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ], [
            'old_password.required' => '请输入原密码',
            'new_password.required' => '请输入新密码',
            'new_password.min' => '新密码至少6位',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $admin = $request->user();

        // 验证原密码
        if (!$admin->checkPassword($request->old_password)) {
            return response()->json([
                'code' => 400,
                'message' => '原密码错误',
                'data' => null
            ], 400);
        }

        // 更新密码
        $admin->password = $request->new_password;
        $admin->save();

        // 删除所有token，强制重新登录
        $admin->tokens()->delete();

        return response()->json([
            'code' => 200,
            'message' => '密码修改成功，请重新登录',
            'data' => null
        ]);
    }
}
