<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ShippingAddress;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * 获取订单列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $query = Order::with(['items.product', 'items.specification'])
            ->where('user_id', $user->id);

        // 状态筛选
        if ($request->has('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // 类型筛选
        if ($request->has('order_type')) {
            $query->where('order_type', $request->order_type);
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
     * 获取订单详情
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $order = Order::with(['items.product', 'items.specification', 'payments'])
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
     * 创建订单
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        try {
            DB::beginTransaction();

            // 订单类型
            $orderType = $request->order_type;

            // 获取收货地址（仅商品订单需要）
            $address = null;
            if ($orderType === 'goods') {
                $address = ShippingAddress::where('user_id', $user->id)
                    ->where('id', $request->address_id)
                    ->first();

                if (!$address) {
                    DB::rollBack();
                    return response()->json([
                        'code' => 400,
                        'message' => '收货地址不存在',
                        'data' => null,
                    ], 400);
                }
            }

            // 验证购物车商品
            $cartItems = ShoppingCart::with(['product', 'specification'])
                ->where('user_id', $user->id)
                ->whereIn('id', $request->cart_ids)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'code' => 400,
                    'message' => '购物车商品不存在',
                    'data' => null,
                ], 400);
            }

            // 计算总金额
            $goodsAmount = 0;
            foreach ($cartItems as $item) {
                // 验证库存
                if ($item->specification_id) {
                    if ($item->specification->stock < $item->quantity) {
                        DB::rollBack();
                        return response()->json([
                            'code' => 400,
                            'message' => $item->product->name . ' 库存不足',
                            'data' => null,
                        ], 400);
                    }
                } else {
                    if ($item->product->stock < $item->quantity) {
                        DB::rollBack();
                        return response()->json([
                            'code' => 400,
                            'message' => $item->product->name . ' 库存不足',
                            'data' => null,
                        ], 400);
                    }
                }

                $goodsAmount += $item->price * $item->quantity;
            }

            // 计算积分抵扣
            $pointsUsed = $request->points_used ?? 0;
            $pointsDiscount = 0;

            if ($pointsUsed > 0) {
                if ($pointsUsed > $user->points_balance) {
                    DB::rollBack();
                    return response()->json([
                        'code' => 400,
                        'message' => '积分余额不足',
                        'data' => null,
                    ], 400);
                }

                // 100积分 = 1元
                $pointsDiscount = round($pointsUsed / 100, 2);

                // 积分抵扣不能超过订单金额
                if ($pointsDiscount > $goodsAmount) {
                    $pointsDiscount = $goodsAmount;
                    $pointsUsed = $pointsDiscount * 100;
                }
            }

            // 计算运费（可以根据实际情况调整）
            $shippingFee = $request->shipping_fee ?? 0;

            // 计算总金额
            $totalAmount = $goodsAmount + $shippingFee - $pointsDiscount;

            // 创建订单
            $orderData = [
                'order_no' => $this->generateOrderNo(),
                'user_id' => $user->id,
                'order_type' => $orderType,
                'goods_amount' => $goodsAmount,
                'shipping_fee' => $shippingFee,
                'points_used' => $pointsUsed,
                'points_discount' => $pointsDiscount,
                'total_amount' => $totalAmount,
                'order_status' => 0,
                'payment_status' => 0,
                'remark' => $request->remark,
            ];

            // 根据订单类型填充不同字段
            if ($orderType === 'goods') {
                // 商品订单：填充收货地址
                $orderData['receiver_name'] = $address->receiver_name;
                $orderData['receiver_phone'] = $address->receiver_phone;
                $orderData['receiver_province'] = $address->province;
                $orderData['receiver_city'] = $address->city;
                $orderData['receiver_district'] = $address->district;
                $orderData['receiver_detail'] = $address->detail_address;
            } else {
                // 家庭套餐：填充房间号
                $orderData['room_number'] = $request->room_number;
                $orderData['receiver_name'] = '';
                $orderData['receiver_phone'] = '';
                $orderData['receiver_province'] = '';
                $orderData['receiver_city'] = '';
                $orderData['receiver_district'] = '';
                $orderData['receiver_detail'] = '';
            }

            $order = Order::create($orderData);

            // 创建订单明细
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'specification_id' => $item->specification_id,
                    'product_name' => $item->product->name,
                    'product_image' => $item->product->cover_image,
                    'spec_name' => $item->specification ? json_encode($item->specification->spec_values) : null,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total_amount' => $item->price * $item->quantity,
                ]);

                // 扣减库存
                if ($item->specification_id) {
                    $item->specification->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }

                // 增加销量
                $item->product->increment('sales', $item->quantity);
            }

            // 扣减积分
            if ($pointsUsed > 0) {
                $user->decrement('points_balance', $pointsUsed);
            }

            // 删除购物车记录
            ShoppingCart::whereIn('id', $request->cart_ids)->delete();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '订单创建成功',
                'data' => $order->load(['items.product', 'items.specification']),
            ]);
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
     * 取消订单
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $order = Order::with(['items'])
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

        if ($order->order_status !== 0) {
            return response()->json([
                'code' => 400,
                'message' => '订单状态不允许取消',
                'data' => null,
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 恢复库存
            foreach ($order->items as $item) {
                if ($item->specification_id) {
                    ProductSpecification::find($item->specification_id)
                        ->increment('stock', $item->quantity);
                } else {
                    Product::find($item->product_id)
                        ->increment('stock', $item->quantity);
                }

                // 减少销量
                Product::find($item->product_id)
                    ->decrement('sales', $item->quantity);
            }

            // 退还积分
            if ($order->points_used > 0) {
                $user->increment('points_balance', $order->points_used);
            }

            // 更新订单状态
            $order->update([
                'order_status' => 4,
                'cancelled_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '订单已取消',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '取消订单失败: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 确认收货
     */
    public function confirm(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        if ($order->order_status !== 2) {
            return response()->json([
                'code' => 400,
                'message' => '订单状态不允许确认收货',
                'data' => null,
            ], 400);
        }

        $order->update([
            'order_status' => 3,
            'completed_at' => now(),
        ]);

        return response()->json([
            'code' => 200,
            'message' => '确认收货成功',
            'data' => $order,
        ]);
    }

    /**
     * 生成订单号
     */
    private function generateOrderNo(): string
    {
        return date('YmdHis') . rand(100000, 999999);
    }
}
