<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Services\V2\OrderService;
use App\Services\V2\MealOrderService;
use App\Services\V2\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;
    protected $mealOrderService;
    protected $refundService;

    public function __construct(OrderService $orderService, MealOrderService $mealOrderService, RefundService $refundService)
    {
        $this->orderService = $orderService;
        $this->mealOrderService = $mealOrderService;
        $this->refundService = $refundService;
    }

    public function createMallOrder(Request $request)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $validated = $request->validate([
                'delivery_type' => 'required|in:express,room',
                'room_id' => 'required_if:delivery_type,room|integer',
                'room_name' => 'required_if:delivery_type,room|string',
                'receiver_name' => 'required_if:delivery_type,express|string',
                'receiver_phone' => 'required_if:delivery_type,express|string',
                'receiver_address' => 'required_if:delivery_type,express|string',
                'freight_amount' => 'nullable|numeric|min:0',
                'points_used' => 'nullable|integer|min:0',
                'remarks' => 'nullable|string',
                'items' => 'nullable|array|min:1',
                'items.*.product_id' => 'required_with:items|integer',
                'items.*.quantity' => 'required_with:items|integer|min:1',
            ]);

            $order = $this->orderService->createOrder($user, $validated);

            return response()->json([
                'code' => 0,
                'message' => '订单创建成功',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function createMealOrder(Request $request)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.meal_date' => 'required|date',
                'items.*.meal_type' => 'required|in:breakfast,lunch,dinner',
                'items.*.quantity' => 'required|integer|min:1',
                'room_id' => 'nullable|integer',
                'room_name' => 'nullable|string',
                'customer_name' => 'nullable|string',
                'points_used' => 'nullable|integer|min:0',
                'remarks' => 'nullable|string',
            ]);

            $order = $this->mealOrderService->create($user, $validated);

            return response()->json([
                'code' => 0,
                'message' => '订餐成功',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function getMallOrders(Request $request)
    {
        $user = $request->attributes->get('v2_user');
        $status = $request->input('status');

        $query = Order::forUser($user->id)
            ->with(['items' => function ($q) {
                $q->select('id', 'order_id', 'product_name', 'product_image', 'quantity', 'subtotal');
            }])
            ->recent();

        if ($status !== null) {
            $query->byStatus($status);
        }

        $orders = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    public function getMealOrders(Request $request)
    {
        $user = $request->attributes->get('v2_user');
        $status = $request->input('status');

        $query = MealOrder::forUser($user->id)
            ->with(['items' => function ($q) {
                $q->select('id', 'meal_order_id', 'meal_date', 'meal_type', 'meal_name', 'quantity')
                    ->orderBy('meal_date', 'asc');
            }])
            ->recent();

        if ($status !== null) {
            $query->byStatus($status);
        }

        $orders = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $orders,
        ]);
    }

    public function getMallOrderDetail(Request $request, $id)
    {
        $user = $request->attributes->get('v2_user');

        $order = Order::forUser($user->id)->with('items')->find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    public function getMealOrderDetail(Request $request, $id)
    {
        $user = $request->attributes->get('v2_user');

        $order = MealOrder::forUser($user->id)->with('items')->find($id);

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    public function getMallOrderByOrderNo(Request $request, $orderNo)
    {
        $user = $request->attributes->get('v2_user');

        $order = Order::forUser($user->id)->with('items')->where('order_no', $orderNo)->first();

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    public function getMealOrderByOrderNo(Request $request, $orderNo)
    {
        $user = $request->attributes->get('v2_user');

        $order = MealOrder::forUser($user->id)->with('items')->where('order_no', $orderNo)->first();

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $order,
        ]);
    }

    public function getOrderByOrderNo(Request $request, $orderNo)
    {
        $user = $request->attributes->get('v2_user');

        // 先查商城订单
        $order = Order::forUser($user->id)->with('items')->where('order_no', $orderNo)->first();
        if ($order) {
            return response()->json([
                'code' => 0,
                'message' => '获取成功',
                'data' => ['order' => $order, 'type' => 'mall'],
            ]);
        }

        // 再查订餐订单
        $order = MealOrder::forUser($user->id)->with('items')->where('order_no', $orderNo)->first();
        if ($order) {
            return response()->json([
                'code' => 0,
                'message' => '获取成功',
                'data' => ['order' => $order, 'type' => 'meal'],
            ]);
        }

        return response()->json([
            'code' => 404,
            'message' => '订单不存在',
            'data' => null,
        ], 404);
    }

    public function cancelMallOrder(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $order = Order::forUser($user->id)->find($id);

            if (!$order) {
                return response()->json([
                    'code' => 404,
                    'message' => '订单不存在',
                    'data' => null,
                ], 404);
            }

            $reason = $request->input('reason');
            $this->orderService->cancel($order, $reason);

            return response()->json([
                'code' => 0,
                'message' => '订单已取消',
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function cancelMealOrder(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $order = MealOrder::forUser($user->id)->find($id);

            if (!$order) {
                return response()->json([
                    'code' => 404,
                    'message' => '订单不存在',
                    'data' => null,
                ], 404);
            }

            $reason = $request->input('reason');
            $this->mealOrderService->cancel($order, $reason);

            return response()->json([
                'code' => 0,
                'message' => '订单已取消',
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function refundMallOrder(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $order = Order::forUser($user->id)->find($id);

            if (!$order) {
                return response()->json([
                    'code' => 404,
                    'message' => '订单不存在',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            $order = $this->refundService->applyMallOrderRefund($order, $validated['reason']);

            return response()->json([
                'code' => 0,
                'message' => '退款申请已提交',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function refundMealOrder(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('v2_user');

            $order = MealOrder::forUser($user->id)->find($id);

            if (!$order) {
                return response()->json([
                    'code' => 404,
                    'message' => '订单不存在',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            $order = $this->refundService->applyMealOrderRefund($order, $validated['reason']);

            return response()->json([
                'code' => 0,
                'message' => '退款申请已提交',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function confirmReceipt(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('v2_user');

            return DB::transaction(function () use ($user, $id) {
                $order = Order::forUser($user->id)->lockForUpdate()->find($id);

                if (!$order) {
                    return response()->json([
                        'code' => 404,
                        'message' => '订单不存在',
                        'data' => null,
                    ], 404);
                }

                if (!$order->canConfirm()) {
                    return response()->json([
                        'code' => 400,
                        'message' => '当前订单状态不允许确认收货',
                        'data' => null,
                    ], 400);
                }

                $order->markAsCompleted();

                return response()->json([
                    'code' => 0,
                    'message' => '确认收货成功',
                    'data' => $order,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Confirm receipt failed', ['order_id' => $id, 'exception' => $e]);
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }
}
