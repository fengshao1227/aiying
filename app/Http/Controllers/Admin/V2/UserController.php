<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\PointsHistory;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('customer');

        if ($request->filled('keyword')) {
            $keyword = addcslashes($request->keyword, '%_');
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('bind_phone', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('customer_name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::with('customer')->find($id);

        if (!$user) {
            return response()->json(['code' => 404, 'message' => '用户不存在'], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $user,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['code' => 404, 'message' => '用户不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $user->status = (int) $request->status;
        $user->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $user,
        ]);
    }

    public function adjustPoints(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $points = (int) $request->points;
        $reason = $request->reason;
        $operatorId = $request->user()?->id;

        try {
            $user = null;

            DB::transaction(function () use ($id, $points, $reason, $operatorId, &$user) {
                $user = User::lockForUpdate()->find($id);

                if (!$user) {
                    throw new \Exception('用户不存在');
                }

                $balanceBefore = $user->points_balance ?? 0;
                $balanceAfter = $balanceBefore + $points;

                if ($balanceAfter < 0) {
                    throw new \Exception('积分余额不足');
                }

                $user->points_balance = $balanceAfter;
                $user->save();

                PointsHistory::create([
                    'user_id' => $user->id,
                    'customer_id' => $user->customer_id,
                    'type' => $points > 0 ? PointsHistory::TYPE_ADMIN_ADD : PointsHistory::TYPE_ADMIN_DEDUCT,
                    'points' => $points,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => PointsHistory::SOURCE_ADMIN,
                    'description' => $reason,
                    'operator_id' => $operatorId,
                ]);
            });

            return response()->json([
                'code' => 200,
                'message' => '积分调整成功',
                'data' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    public function orders(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['code' => 404, 'message' => '用户不存在'], 404);
        }

        $type = $request->input('type', 'mall');
        $perPage = min((int) $request->input('per_page', 10), 100);

        if ($type === 'meal') {
            $query = MealOrder::with('items')->where('user_id', $user->id);
        } else {
            $query = Order::with('items')->where('user_id', $user->id);
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('order_status', $request->status);
        }

        $orders = $query->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    public function stats(Request $request)
    {
        $query = User::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $total = (clone $query)->count();
        $enabled = (clone $query)->where('status', User::STATUS_ENABLED)->count();
        $disabled = (clone $query)->where('status', User::STATUS_DISABLED)->count();
        $pointsTotal = (clone $query)->sum('points_balance');

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'total' => $total,
                'enabled' => $enabled,
                'disabled' => $disabled,
                'points_total' => $pointsTotal,
            ],
        ]);
    }
}
