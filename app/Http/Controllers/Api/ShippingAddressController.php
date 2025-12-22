<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShippingAddressRequest;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    /**
     * 获取收货地址列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $addresses = ShippingAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $addresses,
        ]);
    }

    /**
     * 获取默认收货地址
     */
    public function getDefault(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $address = ShippingAddress::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $address,
        ]);
    }

    /**
     * 创建收货地址
     */
    public function store(ShippingAddressRequest $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        // 如果设置为默认地址，取消其他默认地址
        if ($request->is_default) {
            ShippingAddress::where('user_id', $user->id)
                ->update(['is_default' => 0]);
        }

        $address = ShippingAddress::create([
            'user_id' => $user->id,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'detail_address' => $request->detail_address,
            'is_default' => $request->is_default ?? 0,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $address,
        ]);
    }

    /**
     * 更新收货地址
     */
    public function update(ShippingAddressRequest $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $address = ShippingAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'code' => 404,
                'message' => '地址不存在',
                'data' => null,
            ], 404);
        }

        // 如果设置为默认地址，取消其他默认地址
        if ($request->is_default) {
            ShippingAddress::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => 0]);
        }

        $address->update($request->only([
            'receiver_name',
            'receiver_phone',
            'province',
            'city',
            'district',
            'detail_address',
            'is_default',
        ]));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $address,
        ]);
    }

    /**
     * 删除收货地址
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $address = ShippingAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'code' => 404,
                'message' => '地址不存在',
                'data' => null,
            ], 404);
        }

        $address->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null,
        ]);
    }

    /**
     * 设置默认地址
     */
    public function setDefault(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $address = ShippingAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'code' => 404,
                'message' => '地址不存在',
                'data' => null,
            ], 404);
        }

        // 取消其他默认地址
        ShippingAddress::where('user_id', $user->id)
            ->update(['is_default' => 0]);

        // 设置当前地址为默认
        $address->update(['is_default' => 1]);

        return response()->json([
            'code' => 200,
            'message' => '设置成功',
            'data' => $address,
        ]);
    }
}
