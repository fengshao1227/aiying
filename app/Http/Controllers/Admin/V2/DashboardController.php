<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\Product;
use App\Models\Room;
use App\Models\RoomStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overview()
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(6);

        // 今日统计
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayMealOrders = MealOrder::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('paid_at', $today)->where('payment_status', 1)->sum('actual_amount')
            + MealOrder::whereDate('paid_at', $today)->where('payment_status', 1)->sum('actual_amount');
        $todayNewUsers = User::whereDate('created_at', $today)->count();

        // 总量统计
        $totalUsers = User::count();
        $totalProducts = Product::where('status', 1)->count();
        $totalOrders = Order::count() + MealOrder::count();

        // 入住率
        $totalRooms = Room::count();
        $occupiedRooms = RoomStatus::where('status', 1)
            ->whereDate('check_in_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('check_out_date')
                    ->orWhereDate('check_out_date', '>=', $today);
            })
            ->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // 近7天趋势
        $dates = [];
        $orderTrend = [];
        $revenueTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('m-d');

            $dayOrders = Order::whereDate('created_at', $date)->count()
                + MealOrder::whereDate('created_at', $date)->count();
            $orderTrend[] = $dayOrders;

            $dayRevenue = Order::whereDate('paid_at', $date)->where('payment_status', 1)->sum('actual_amount')
                + MealOrder::whereDate('paid_at', $date)->where('payment_status', 1)->sum('actual_amount');
            $revenueTrend[] = (float) $dayRevenue;
        }

        // 订单状态分布
        $orderStatus = [
            'pending' => Order::where('order_status', 0)->count(),
            'paid' => Order::where('order_status', 1)->count(),
            'shipped' => Order::where('order_status', 2)->count(),
            'completed' => Order::where('order_status', 3)->count(),
            'cancelled' => Order::where('order_status', 4)->count(),
        ];

        // 热销商品TOP5
        $topProducts = Product::orderByDesc('sales')->limit(5)->get(['id', 'name', 'sales', 'price']);

        // 待处理事项
        $pendingShipment = Order::where('order_status', 1)->count();
        $pendingMeals = MealOrder::where('order_status', 1)->count();
        $lowStock = Product::where('status', 1)->where('stock', '<', 10)->count();
        $refundApplying = Order::where('refund_status', 1)->count();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'today' => [
                    'orders' => $todayOrders + $todayMealOrders,
                    'revenue' => (float) $todayRevenue,
                    'new_users' => $todayNewUsers,
                ],
                'total' => [
                    'users' => $totalUsers,
                    'products' => $totalProducts,
                    'orders' => $totalOrders,
                ],
                'occupancy' => [
                    'total_rooms' => $totalRooms,
                    'occupied' => $occupiedRooms,
                    'rate' => $occupancyRate,
                ],
                'trends' => [
                    'dates' => $dates,
                    'orders' => $orderTrend,
                    'revenue' => $revenueTrend,
                ],
                'order_status' => $orderStatus,
                'top_products' => $topProducts,
                'alerts' => [
                    'pending_shipment' => $pendingShipment,
                    'pending_meals' => $pendingMeals,
                    'low_stock' => $lowStock,
                    'refund_applying' => $refundApplying,
                ],
            ],
        ]);
    }
}
