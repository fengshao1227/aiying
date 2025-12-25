<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\V2\MealOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealOrderReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,month',
            'room_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
        ]);

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $groupBy = $request->input('group_by', 'day');
        $roomId = $request->input('room_id');
        $customerId = $request->input('customer_id');

        $query = MealOrderItem::query()
            ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
            ->whereBetween('meal_order_items.meal_date', [$startDate, $endDate])
            ->whereNull('meal_orders.deleted_at')
            ->where('meal_orders.payment_status', 1);

        if ($roomId) {
            $query->where('meal_orders.room_id', $roomId);
        }

        if ($customerId) {
            $query->where('meal_orders.customer_id', $customerId);
        }

        $dateFormat = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $stats = (clone $query)->select(
            DB::raw("DATE_FORMAT(meal_order_items.meal_date, '$dateFormat') as date"),
            DB::raw('COUNT(DISTINCT meal_orders.id) as total_orders'),
            DB::raw('SUM(meal_order_items.subtotal) as total_amount'),
            DB::raw('SUM(meal_order_items.quantity) as total_items')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $breakdown = (clone $query)->select(
            'meal_order_items.meal_type',
            DB::raw('SUM(meal_order_items.quantity) as total_quantity'),
            DB::raw('SUM(meal_order_items.subtotal) as total_amount')
        )
        ->groupBy('meal_order_items.meal_type')
        ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'stats' => $stats,
                'breakdown' => $breakdown,
                'summary' => [
                    'total_orders' => $stats->sum('total_orders'),
                    'total_amount' => $stats->sum('total_amount'),
                    'total_items' => $stats->sum('total_items'),
                ],
            ],
        ]);
    }
}
