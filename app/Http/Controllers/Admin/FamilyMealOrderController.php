<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyMealOrder;
use Illuminate\Http\Request;

class FamilyMealOrderController extends Controller
{
    /**
     * 获取家属订餐订单列表
     */
    public function index(Request $request)
    {
        $query = FamilyMealOrder::with('user');

        // 状态筛选
        if ($request->has('order_status') && $request->order_status !== null) {
            $query->where('order_status', $request->order_status);
        }

        // 日期筛选
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
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
        $order = FamilyMealOrder::with(['user', 'package'])->find($id);

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
        $order = FamilyMealOrder::find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
            ], 404);
        }

        $order->order_status = $request->order_status;
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
        $order = FamilyMealOrder::find($id);

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
}
