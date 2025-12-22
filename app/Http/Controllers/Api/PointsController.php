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

        // 映射字段以匹配前端期望
        $formattedData = $history->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'points_change' => $item->points,
                'change_type' => $item->type,
                'description' => $item->description,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $formattedData,
            'total' => $history->total(),
            'per_page' => $history->perPage(),
            'current_page' => $history->currentPage(),
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

        // 计算累计获得积分
        $totalEarned = PointsHistory::where('user_id', $user->id)
            ->where('type', 'earn')
            ->sum('points');

        // 计算累计使用积分（spend + refund）
        $totalUsed = PointsHistory::where('user_id', $user->id)
            ->whereIn('type', ['spend', 'refund'])
            ->sum('points');

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'points_balance' => $user->points_balance,
                'total_earned' => abs($totalEarned),
                'total_used' => abs($totalUsed),
            ],
        ]);
    }
}
