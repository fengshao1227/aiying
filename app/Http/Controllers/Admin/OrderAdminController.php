<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\V2\Order;
use App\Services\V2\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderAdminController extends Controller
{
    /**
     * 获取订单列表
     */
    public function index(Request $request)
    {
        $query = Order::with('user');

        // 状态筛选
        if ($request->has('order_status') && $request->order_status !== null) {
            $query->where('order_status', $request->order_status);
        }

        // 日期筛选
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    /**
     * 获取订单详情
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    /**
     * 更新订单状态
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $order,
        ]);
    }

    /**
     * 更新配送信息
     */
    public function updateDelivery(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'delivery_company' => 'required|string',
            'tracking_no' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $order->delivery_company = $request->delivery_company;
        $order->tracking_no = $request->tracking_no;
        $order->status = 'shipped';
        $order->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $order,
        ]);
    }

    /**
     * 删除订单（软删除）
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
            ], 404);
        }

        $order->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    public function approveRefund($id, RefundService $refundService)
    {
        try {
            return DB::transaction(function () use ($id, $refundService) {
                $order = Order::lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
                }

                if ($order->refund_status !== Order::REFUND_APPLYING) {
                    return response()->json(['code' => 400, 'message' => '订单未在退款申请状态'], 400);
                }

                $refundService->processRefund($order);
                return response()->json(['code' => 200, 'message' => '退款处理成功', 'data' => $order->fresh()]);
            });
        } catch (\Exception $e) {
            Log::error('Admin approve refund failed', ['order_id' => $id, 'exception' => $e]);
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    public function rejectRefund(Request $request, $id, RefundService $refundService)
    {
        try {
            return DB::transaction(function () use ($request, $id, $refundService) {
                $order = Order::lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json(['code' => 404, 'message' => '订单不存在'], 404);
                }

                $reason = $request->input('reason', '管理员拒绝退款');
                $refundService->rejectRefund($order, $reason);
                return response()->json(['code' => 200, 'message' => '已拒绝退款申请', 'data' => $order->fresh()]);
            });
        } catch (\Exception $e) {
            Log::error('Admin reject refund failed', ['order_id' => $id, 'exception' => $e]);
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }
}
