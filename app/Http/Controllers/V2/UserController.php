<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\User;
use App\Models\Customer;
use App\Services\WechatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(
        private WechatService $wechatService
    ) {}

    /**
     * 微信登录
     * POST /v2/user/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        try {
            // 获取微信 openid
            $wechatData = $this->wechatService->code2Session($request->code);

            // 查找或创建用户
            $user = User::withTrashed()->where('openid', $wechatData['openid'])->first();

            if (!$user) {
                $user = User::create([
                    'openid' => $wechatData['openid'],
                    'unionid' => $wechatData['unionid'] ?? null,
                    'nickname' => '微信用户',
                    'status' => User::STATUS_ENABLED,
                    'points_balance' => 0,
                ]);
            } else if ($user->trashed()) {
                // 恢复软删除的用户
                $user->restore();
            }

            // 更新最后登录时间
            $user->update(['last_login_at' => now()]);

            // 生成 token (简单实现，实际项目可使用 JWT)
            $token = Str::random(60);

            // 构建响应数据
            $userData = [
                'id' => $user->id,
                'openid' => $user->openid,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'phone' => $user->phone,
                'pointsBalance' => $user->points_balance,
                'isBound' => $user->isBound(),
                'status' => $user->status,
            ];

            // 如果已绑定客户，添加客户信息
            if ($user->isBound() && $user->customer) {
                $userData['customer'] = [
                    'id' => $user->customer->customer_id,
                    'name' => $user->customer->customer_name,
                    'phone' => $user->customer->phone,
                ];
            }

            return response()->json([
                'code' => 0,
                'message' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
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
     * GET /v2/user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        // 预加载客户信息
        $user->load('customer');

        $userData = [
            'id' => $user->id,
            'openid' => $user->openid,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'phone' => $user->phone,
            'bindPhone' => $user->bind_phone,
            'pointsBalance' => $user->points_balance,
            'isBound' => $user->isBound(),
            'status' => $user->status,
            'lastLoginAt' => $user->last_login_at?->toISOString(),
        ];

        // 如果已绑定客户，添加客户信息
        if ($user->isBound() && $user->customer) {
            $userData['customer'] = [
                'id' => $user->customer->customer_id,
                'name' => $user->customer->customer_name,
                'phone' => $user->customer->phone,
                'packageName' => $user->customer->package_name,
                'babyName' => $user->customer->baby_name,
                'checkInDate' => $user->customer->check_in_date?->toISOString(),
                'checkOutDate' => $user->customer->check_out_date?->toISOString(),
            ];
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $userData,
        ]);
    }

    /**
     * 绑定月子中心客户
     * POST /v2/user/bindCustomer
     */
    public function bindCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        $user = $request->attributes->get('v2_user');
        $phone = $request->phone;

        // 检查用户是否已绑定
        if ($user->isBound()) {
            return response()->json([
                'code' => 400,
                'message' => '您已绑定客户，无需重复绑定',
                'data' => null,
            ], 400);
        }

        // 查找客户
        $customer = Customer::where('phone', $phone)->first();

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '未找到该手机号对应的客户记录，请联系前台确认',
                'data' => null,
            ], 404);
        }

        // 检查该客户是否已被其他用户绑定
        $existingUser = User::where('customer_id', $customer->customer_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'code' => 400,
                'message' => '该客户已被其他用户绑定',
                'data' => null,
            ], 400);
        }

        // 绑定客户
        $user->update([
            'customer_id' => $customer->customer_id,
            'bind_phone' => $phone,
        ]);

        // 重新加载用户数据
        $user->load('customer');

        $userData = [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'phone' => $user->phone,
            'bindPhone' => $user->bind_phone,
            'pointsBalance' => $user->points_balance,
            'isBound' => $user->isBound(),
            'status' => $user->status,
            'customer' => [
                'id' => $customer->customer_id,
                'name' => $customer->customer_name,
                'phone' => $customer->phone,
                'packageName' => $customer->package_name,
                'babyName' => $customer->baby_name,
                'checkInDate' => $customer->check_in_date?->toISOString(),
                'checkOutDate' => $customer->check_out_date?->toISOString(),
            ],
        ];

        return response()->json([
            'code' => 0,
            'message' => '绑定成功',
            'data' => $userData,
        ]);
    }

    /**
     * 解绑客户（可选功能）
     * POST /v2/user/unbindCustomer
     */
    public function unbindCustomer(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        if (!$user->isBound()) {
            return response()->json([
                'code' => 400,
                'message' => '您尚未绑定客户',
                'data' => null,
            ], 400);
        }

        // 解绑客户
        $user->update([
            'customer_id' => null,
            'bind_phone' => null,
        ]);

        $userData = [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'phone' => $user->phone,
            'bindPhone' => null,
            'pointsBalance' => $user->points_balance,
            'isBound' => false,
            'status' => $user->status,
        ];

        return response()->json([
            'code' => 0,
            'message' => '解绑成功',
            'data' => $userData,
        ]);
    }
}