<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * 获取用户列表
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 关键词搜索
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $users,
        ]);
    }

    /**
     * 获取用户详情
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $user,
        ]);
    }

    /**
     * 更新用户信息
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user->update($request->only(['name', 'phone', 'status']));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $user,
        ]);
    }

    /**
     * 调整用户积分
     */
    public function updatePoints(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'points' => 'required|integer',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $newBalance = $user->points_balance + $request->points;

        if ($newBalance < 0) {
            return response()->json([
                'code' => 400,
                'message' => '积分余额不足',
            ], 400);
        }

        $user->points_balance = $newBalance;
        $user->save();

        // TODO: 记录积分变动日志

        return response()->json([
            'code' => 200,
            'message' => '积分调整成功',
            'data' => $user,
        ]);
    }

    /**
     * 获取用户订单
     */
    public function orders(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
            ], 404);
        }

        $perPage = $request->input('per_page', 10);
        $orders = $user->orders()->with('items.product')->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    /**
     * 删除用户（软删除）
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
