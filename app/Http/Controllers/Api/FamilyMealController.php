<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyMealPackage;
use App\Models\FamilyMealOrder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyMealController extends Controller
{
    /**
     * 获取家庭套餐列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = FamilyMealPackage::where('status', 1);

        // 搜索
        if ($request->has('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 排序
        $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc');

        $packages = $query->paginate($request->input('per_page', 15));

        // 转换数据格式，使用product_id作为id返回给前端
        $transformedData = $packages->getCollection()->map(function ($package) {
            return [
                'id' => $package->product_id ?? $package->id, // 使用product_id，如果没有则用套餐id
                'package_id' => $package->id, // 保留原始套餐id供参考
                'name' => $package->name,
                'price' => $package->price,
                'cover_image' => $package->cover_image,
                'description' => $package->description,
                'services' => $package->services,
                'duration_days' => $package->duration_days,
            ];
        });

        $packages->setCollection($transformedData);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $packages,
        ]);
    }

    /**
     * 获取家庭套餐详情
     */
    public function show($id): JsonResponse
    {
        $package = FamilyMealPackage::find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
                'data' => null,
            ], 404);
        }

        if ($package->status !== 1) {
            return response()->json([
                'code' => 400,
                'message' => '套餐已下架',
                'data' => null,
            ], 400);
        }

        // 返回格式与列表保持一致
        $data = [
            'id' => $package->product_id ?? $package->id,
            'package_id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'cover_image' => $package->cover_image,
            'description' => $package->description,
            'services' => $package->services,
            'duration_days' => $package->duration_days,
        ];

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }

    /**
     * 创建家属订餐订单
     *
     * 请求参数:
     * - room_name: string 房间号
     * - customer_phone: string 客户手机号
     * - meal_dates: array 订餐日期数组 ["2025-12-23", "2025-12-24"]
     * - meal_times: object 餐次及份数 {"breakfast": 2, "lunch": 1, "dinner": 0}
     * - quantity: int 每餐份数(可选,兼容旧逻辑)
     * - remarks: string 备注(可选)
     * - meal_id: int 套餐ID(可选)
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        // 参数验证
        $validated = $request->validate([
            'room_name' => 'required|string|max:50',
            'customer_phone' => 'required|string|max:20',
            'meal_dates' => 'required|array|min:1',
            'meal_dates.*' => 'required|date_format:Y-m-d',
            'meal_times' => 'required|array',
            'quantity' => 'nullable|integer|min:1',
            'remarks' => 'nullable|string|max:500',
            'meal_id' => 'nullable|integer|exists:family_meal_packages,id',
        ]);

        try {
            DB::beginTransaction();

            // 获取套餐信息(如果有)
            $package = null;
            $unitPrice = 0;

            if (!empty($validated['meal_id'])) {
                $package = FamilyMealPackage::find($validated['meal_id']);
                if ($package) {
                    $unitPrice = $package->price;
                }
            }

            // 如果没有套餐或套餐价格为0,则需要前端传入单价
            if ($unitPrice <= 0) {
                $unitPrice = $request->input('unit_price', 0);
            }

            // 计算总份数
            $mealTimes = $validated['meal_times'];
            $totalPortions = 0;

            // 支持两种格式:
            // 格式1: {"breakfast": 2, "lunch": 1, "dinner": 0}
            // 格式2: ["breakfast", "lunch"] (使用quantity字段)
            if (isset($mealTimes['breakfast']) || isset($mealTimes['lunch']) || isset($mealTimes['dinner'])) {
                // 格式1: 带份数的对象
                $totalPortions = (int)($mealTimes['breakfast'] ?? 0)
                    + (int)($mealTimes['lunch'] ?? 0)
                    + (int)($mealTimes['dinner'] ?? 0);
            } else {
                // 格式2: 数组形式,使用quantity
                $quantity = $validated['quantity'] ?? 1;
                $totalPortions = count($mealTimes) * $quantity;
                // 转换为标准格式
                $standardMealTimes = [];
                foreach ($mealTimes as $mealType) {
                    $standardMealTimes[$mealType] = $quantity;
                }
                $mealTimes = $standardMealTimes;
            }

            // 计算总天数
            $totalDays = count($validated['meal_dates']);

            // 计算总金额 = 单价 × 总份数 × 天数
            $totalAmount = $unitPrice * $totalPortions * $totalDays;

            // 生成订单号
            $orderNo = 'FM' . date('YmdHis') . rand(1000, 9999);

            // 创建订单
            $order = FamilyMealOrder::create([
                'order_no' => $orderNo,
                'user_id' => $user->id,
                'package_id' => $package?->id,
                'room_name' => $validated['room_name'],
                'customer_phone' => $validated['customer_phone'],
                'meal_dates' => $validated['meal_dates'],
                'meal_times' => $mealTimes,
                'quantity' => $validated['quantity'] ?? 1,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'order_status' => 0,
                'payment_status' => 0,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '订单创建成功',
                'data' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'room_name' => $order->room_name,
                    'customer_phone' => $order->customer_phone,
                    'meal_dates' => $order->meal_dates,
                    'meal_times' => $order->meal_times,
                    'total_days' => $totalDays,
                    'total_portions' => $totalPortions,
                    'unit_price' => $order->unit_price,
                    'total_amount' => $order->total_amount,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 400,
                'message' => '参数验证失败',
                'data' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '订单创建失败: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 获取用户的家属订餐订单列表
     */
    public function orderList(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $query = FamilyMealOrder::where('user_id', $user->id);

        // 状态筛选
        if ($request->has('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    /**
     * 获取家属订餐订单详情
     */
    public function orderDetail(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $order = FamilyMealOrder::with('package')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    /**
     * 取消家属订餐订单
     */
    public function cancelOrder(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $order = FamilyMealOrder::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        if ($order->order_status !== 0) {
            return response()->json([
                'code' => 400,
                'message' => '订单状态不允许取消',
                'data' => null,
            ], 400);
        }

        $order->update([
            'order_status' => 3,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'code' => 200,
            'message' => '订单已取消',
            'data' => $order,
        ]);
    }
}
