<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\PointsHistory;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->attributes->get('v2_user');

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '用户未认证',
            ], 401);
        }

        $stats = PointsHistory::where('user_id', $user->id)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN points > 0 THEN points ELSE 0 END), 0) as total_earned,
                COALESCE(SUM(CASE WHEN points < 0 THEN points ELSE 0 END), 0) as total_used
            ')
            ->first();

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => [
                'points_balance' => $user->points_balance,
                'total_earned' => (int) ($stats->total_earned ?? 0),
                'total_used' => abs((int) ($stats->total_used ?? 0)),
            ],
        ]);
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:earn,spend,refund,admin_add,admin_deduct',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $user = $request->attributes->get('v2_user');

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '用户未认证',
            ], 401);
        }

        $query = PointsHistory::forUser($user->id)->recent();

        if (isset($validated['type'])) {
            $query->byType($validated['type']);
        }

        $history = $query->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $history,
        ]);
    }
}
