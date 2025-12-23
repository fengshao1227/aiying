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

            // 查找用户（包含软删除的记录，防止唯一约束冲突）
            $user = User::withTrashed()->where('openid', $wechatData['openid'])->first();

            if (!$user) {
                try {
                    $user = User::create([
                        'openid' => $wechatData['openid'],
                        'name' => '微信用户',
                        'status' => 1,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // 处理并发插入导致的唯一约束冲突
                    if ($e->errorInfo[1] === 1062) {
                        $user = User::withTrashed()->where('openid', $wechatData['openid'])->first();
                    } else {
                        throw $e;
                    }
                }
            }

            // 如果用户曾被软删除，恢复它
            if ($user->trashed()) {
                $user->restore();
            }

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

            // 查询是否是月子中心客户
            $customerName = null;
            if ($user->phone) {
                $customer = \App\Models\Customer::where('phone', $user->phone)->first();
                if ($customer) {
                    $customerName = $customer->customer_name;
                }
            }

            $userData = $user->toArray();
            $userData['customer_name'] = $customerName;
            $userData['nickname'] = $user->name; // 兼容前端nickname字段

            return response()->json([
                'code' => 200,
                'message' => '登录成功',
                'data' => [
                    'user' => $userData,
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

            // 检查手机号是否已被其他用户占用（包含软删除的）
            $existingUser = User::withTrashed()
                ->where('phone', $phone)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                if ($existingUser->trashed()) {
                    // 旧用户已软删除，清除其手机号后允许绑定
                    $existingUser->update(['phone' => null]);
                } else {
                    // 旧用户正常状态，拒绝绑定
                    return response()->json([
                        'code' => 400,
                        'message' => '该手机号已被其他账号绑定',
                        'data' => null,
                    ], 400);
                }
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
