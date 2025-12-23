<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WechatLoginRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Models\User;
use App\Services\WechatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private WechatService $wechatService
    ) {}

    /**
     * 微信小程序登录
     */
    public function wechatLogin(WechatLoginRequest $request): JsonResponse
    {
        try {
            // 获取微信openid
            $wechatData = $this->wechatService->code2Session($request->code);

            // 查找或创建用户
            $user = User::firstOrCreate(
                ['openid' => $wechatData['openid']],
                [
                    'openid' => $wechatData['openid'],
                    'status' => 1,
                ]
            );

            // 解密手机号(如果提供)
            if ($request->encrypted_data && $request->iv) {
                $phone = $this->wechatService->decryptPhone(
                    $wechatData['session_key'],
                    $request->encrypted_data,
                    $request->iv
                );

                if ($phone) {
                    $user->update(['phone' => $phone]);
                }
            }

            // 更新最后登录时间
            $user->update(['last_login_at' => now()]);

            // 生成简单token(或使用Sanctum)
            $token = Str::random(60);

            return response()->json([
                'code' => 200,
                'message' => '登录成功',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'session_key' => $wechatData['session_key'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '登录失败: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
                'data' => null,
            ], 404);
        }

        // 如果用户有手机号,查询是否是月子中心客户
        $customerName = null;
        if ($user->phone) {
            $customer = \App\Models\Customer::where('phone', $user->phone)->first();
            if ($customer) {
                $customerName = $customer->customer_name;
            }
        }

        $userData = $user->toArray();
        $userData['customer_name'] = $customerName; // 添加客户姓名字段

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $userData,
        ]);
    }

    /**
     * 更新用户信息
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
                'data' => null,
            ], 404);
        }

        $user->update($request->only(['name', 'avatar', 'gender', 'phone']));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $user,
        ]);
    }

    /**
     * 绑定手机号
     */
    public function bindPhone(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        if (!$request->code) {
            return response()->json([
                'code' => 400,
                'message' => '缺少code参数',
                'data' => null,
            ], 400);
        }

        try {
            // 调用微信接口获取手机号
            $phone = $this->wechatService->getPhoneNumber($request->code);

            if (!$phone) {
                return response()->json([
                    'code' => 500,
                    'message' => '获取手机号失败',
                    'data' => null,
                ], 500);
            }

            // 更新用户手机号
            $user->update(['phone' => $phone]);

            // 查询是否是月子中心客户
            $customerName = null;
            $customer = \App\Models\Customer::where('phone', $phone)->first();
            if ($customer) {
                $customerName = $customer->customer_name;
            }

            $userData = $user->toArray();
            $userData['customer_name'] = $customerName;

            return response()->json([
                'code' => 200,
                'message' => '绑定成功',
                'data' => [
                    'phone' => $phone,
                    'user' => $userData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '绑定失败: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
