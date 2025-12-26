<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\Product;
use App\Models\Room;
use App\Models\RoomStatus;
use App\Models\Customer;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overview()
    {
        $data = CacheService::remember(
            CacheService::dashboardKey(),
            CacheService::TTL_SHORT,
            fn() => $this->buildOverviewData()
        );

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }

    private function buildOverviewData(): array
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(6);
        $monthStart = Carbon::now()->startOfMonth();

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

        // 客户统计
        $totalCustomers = Customer::count();
        $currentCustomers = Customer::whereNotNull('check_in_date')
            ->whereDate('check_in_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('check_out_date')
                    ->orWhereDate('check_out_date', '>=', $today);
            })
            ->count();
        $monthNewCustomers = Customer::whereDate('created_at', '>=', $monthStart)->count();
        $packageDistribution = Customer::select('package_name', DB::raw('count(*) as count'))
            ->whereNotNull('package_name')
            ->where('package_name', '!=', '')
            ->groupBy('package_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 近7天趋势 - 优化：使用分组查询代替循环
        $orderTrendData = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', $weekAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date')
            ->toArray();

        $mealOrderTrendData = MealOrder::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', $weekAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date')
            ->toArray();

        $revenueTrendData = Order::select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(actual_amount) as amount'))
            ->whereDate('paid_at', '>=', $weekAgo)
            ->where('payment_status', 1)
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('amount', 'date')
            ->toArray();

        $mealRevenueTrendData = MealOrder::select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(actual_amount) as amount'))
            ->whereDate('paid_at', '>=', $weekAgo)
            ->where('payment_status', 1)
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('amount', 'date')
            ->toArray();

        $dates = [];
        $orderTrend = [];
        $revenueTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('m-d');
            $orderTrend[] = ($orderTrendData[$dateStr] ?? 0) + ($mealOrderTrendData[$dateStr] ?? 0);
            $revenueTrend[] = (float) (($revenueTrendData[$dateStr] ?? 0) + ($mealRevenueTrendData[$dateStr] ?? 0));
        }

        // 订单状态分布 - 优化：单次查询
        $orderStatusData = Order::select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();

        $orderStatus = [
            'pending' => $orderStatusData[0] ?? 0,
            'paid' => $orderStatusData[1] ?? 0,
            'shipped' => $orderStatusData[2] ?? 0,
            'completed' => $orderStatusData[3] ?? 0,
            'cancelled' => $orderStatusData[4] ?? 0,
        ];

        // 热销商品TOP5
        $topProducts = Product::orderByDesc('sales')->limit(5)->get(['id', 'name', 'sales', 'price']);

        // 待处理事项
        $pendingShipment = $orderStatusData[1] ?? 0;
        $pendingMeals = MealOrder::where('order_status', 1)->count();
        $lowStock = Product::where('status', 1)->where('stock', '<', 10)->count();
        $refundApplying = Order::where('refund_status', 1)->count();

        return [
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
            'customers' => [
                'total' => $totalCustomers,
                'current' => $currentCustomers,
                'month_new' => $monthNewCustomers,
                'packages' => $packageDistribution,
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
        ];
    }
}
