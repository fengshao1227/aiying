<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    /**
     * 获取积分历史记录
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

        $query = PointsHistory::where('user_id', $user->id);

        // 类型筛选
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'balance' => $user->points_balance,
                'history' => $history,
            ],
        ]);
    }

    /**
     * 获取积分余额
     */
    public function balance(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'balance' => $user->points_balance,
            ],
        ]);
    }
}
