<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\MealOrder;
use App\Services\V2\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MealOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = MealOrder::with(['user', 'customer']);

        if ($request->filled('keyword')) {
            $keyword = addcslashes($request->keyword, '%_');
            $query->where(function ($q) use ($keyword) {
                $q->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('room_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('nickname', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('order_status', $request->status);
        }

        if ($request->has('refund_status') && $request->refund_status !== null && $request->refund_status !== '') {
            $query->where('refund_status', $request->refund_status);
        }

        if ($request->has('payment_status') && $request->payment_status !== null && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $orders = $query->recent()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    public function show($id)
    {
        $order = MealOrder::with(['user', 'customer', 'items'])->find($id);

        if (!$order) {
            return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    public function complete($id)
    {
        $order = MealOrder::find($id);

        if (!$order) {
            return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
        }

        if (!$order->canComplete()) {
            return response()->json(['code' => 400, 'message' => '订单状态不允许完成'], 400);
        }

        $order->markAsCompleted();

        return response()->json([
            'code' => 200,
            'message' => '完成成功',
            'data' => $order,
        ]);
    }

    public function approveRefund($id, RefundService $refundService)
    {
        try {
            return DB::transaction(function () use ($id, $refundService) {
                $order = MealOrder::lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
                }

                if ($order->refund_status !== MealOrder::REFUND_APPLYING) {
                    return response()->json(['code' => 400, 'message' => '订单未在退款申请状态'], 400);
                }

                $refundService->processMealRefund($order);

                return response()->json([
                    'code' => 200,
                    'message' => '退款处理成功',
                    'data' => $order->fresh(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Admin V2 meal order approve refund failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    public function rejectRefund(Request $request, $id, RefundService $refundService)
    {
        try {
            return DB::transaction(function () use ($request, $id, $refundService) {
                $order = MealOrder::lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
                }

                if ($order->refund_status !== MealOrder::REFUND_APPLYING) {
                    return response()->json(['code' => 400, 'message' => '订单未在退款申请状态'], 400);
                }

                $reason = $request->input('reason', '管理员拒绝退款');
                $refundService->rejectMealRefund($order, $reason);

                return response()->json([
                    'code' => 200,
                    'message' => '已拒绝退款申请',
                    'data' => $order->fresh(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Admin V2 meal order reject refund failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $order = MealOrder::find($id);

        if (!$order) {
            return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
        }

        if ($order->payment_status === MealOrder::PAYMENT_STATUS_PAID && $order->refund_status !== MealOrder::REFUND_SUCCESS) {
            return response()->json(['code' => 400, 'message' => '已支付且未退款的订单不允许删除'], 400);
        }

        $order->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    public function stats(Request $request)
    {
        $query = MealOrder::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->where('order_status', MealOrder::STATUS_PENDING)->count();
        $paid = (clone $query)->where('order_status', MealOrder::STATUS_PAID)->count();
        $completed = (clone $query)->where('order_status', MealOrder::STATUS_COMPLETED)->count();
        $cancelled = (clone $query)->where('order_status', MealOrder::STATUS_CANCELLED)->count();
        $refundApplying = (clone $query)->where('refund_status', MealOrder::REFUND_APPLYING)->count();
        $totalAmount = (clone $query)->where('payment_status', MealOrder::PAYMENT_STATUS_PAID)->sum('actual_amount');

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'paid' => $paid,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'refund_applying' => $refundApplying,
                'total_amount' => $totalAmount,
            ],
        ]);
    }
}
