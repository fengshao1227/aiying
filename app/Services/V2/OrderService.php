<?php

namespace App\Services\V2;

use App\Models\V2\Order;
use App\Models\V2\OrderItem;
use App\Models\V2\User;
use App\Models\V2\Product;
use App\Models\V2\Cart;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(User $user, array $params)
    {
        if (!empty($params['items'])) {
            return $this->createFromItems($user, $params);
        }
        return $this->createFromCart($user, $params);
    }

    public function createFromItems(User $user, array $params)
    {
        return DB::transaction(function () use ($user, $params) {
            $user = User::lockForUpdate()->find($user->id);

            $totalAmount = 0;
            $orderItems = [];

            foreach ($params['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new \Exception("商品不存在");
                }

                if ($product->status !== Product::STATUS_ON) {
                    throw new \Exception("商品 {$product->name} 已下架");
                }

                if (!$product->hasStock($item['quantity'])) {
                    throw new \Exception("商品 {$product->name} 库存不足");
                }

                $subtotal = bcmul((string)$product->price, (string)$item['quantity'], 2);
                $totalAmount = bcadd((string)$totalAmount, $subtotal, 2);

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->cover_image,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];

                if (!$product->deductStock($item['quantity'])) {
                    throw new \Exception("商品 {$product->name} 库存扣减失败");
                }
            }

            return $this->finalizeOrder($user, $params, $totalAmount, $orderItems);
        });
    }

    public function createFromCart(User $user, array $params)
    {
        return DB::transaction(function () use ($user, $params) {
            $user = User::lockForUpdate()->find($user->id);

            $cartItems = Cart::forUser($user->id)
                ->selected()
                ->with('product')
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('购物车为空');
            }

            $totalAmount = 0;
            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                $product = Product::lockForUpdate()->find($cartItem->product_id);

                if (!$product) {
                    throw new \Exception("商品不存在");
                }

                if ($product->status !== Product::STATUS_ON) {
                    throw new \Exception("商品 {$product->name} 已下架");
                }

                if (!$product->hasStock($cartItem->quantity)) {
                    throw new \Exception("商品 {$product->name} 库存不足");
                }

                $subtotal = bcmul((string)$product->price, (string)$cartItem->quantity, 2);
                $totalAmount = bcadd((string)$totalAmount, $subtotal, 2);

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->cover_image,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $subtotal,
                ];

                if (!$product->deductStock($cartItem->quantity)) {
                    throw new \Exception("商品 {$product->name} 库存扣减失败");
                }
            }

            $order = $this->finalizeOrder($user, $params, $totalAmount, $orderItems);
            Cart::forUser($user->id)->selected()->delete();

            return $order;
        });
    }

    protected function finalizeOrder(User $user, array $params, string $totalAmount, array $orderItems): Order
    {
        $freightAmount = $params['freight_amount'] ?? 0;
        $pointsUsed = $params['points_used'] ?? 0;
        $pointsDiscount = 0;

        if ($pointsUsed > 0) {
            if ($pointsUsed > $user->points_balance) {
                throw new \Exception('积分余额不足');
            }
            $pointsDiscount = bcdiv((string)$pointsUsed, '100', 2);
        }

        $actualAmount = bcadd((string)$totalAmount, (string)$freightAmount, 2);
        $actualAmount = bcsub($actualAmount, (string)$pointsDiscount, 2);
        if (bccomp($actualAmount, '0', 2) < 0) {
            $actualAmount = '0.00';
        }

        $isZeroAmount = bccomp($actualAmount, '0', 2) === 0;
        $paymentType = $isZeroAmount ? Order::PAYMENT_POINTS : ($pointsUsed > 0 ? Order::PAYMENT_MIXED : Order::PAYMENT_CASH);
        $orderStatus = $isZeroAmount ? Order::STATUS_PAID : Order::STATUS_PENDING;
        $paymentStatus = $isZeroAmount ? Order::PAYMENT_STATUS_PAID : Order::PAYMENT_STATUS_UNPAID;

        $order = Order::create([
            'order_no' => $this->generateOrderNo('MO'),
            'user_id' => $user->id,
            'customer_id' => $user->customer_id,
            'delivery_type' => $params['delivery_type'],
            'room_id' => $params['room_id'] ?? null,
            'room_name' => $params['room_name'] ?? null,
            'receiver_name' => $params['receiver_name'] ?? null,
            'receiver_phone' => $params['receiver_phone'] ?? null,
            'receiver_address' => $params['receiver_address'] ?? null,
            'total_amount' => $totalAmount,
            'freight_amount' => $freightAmount,
            'points_used' => $pointsUsed,
            'points_discount' => $pointsDiscount,
            'actual_amount' => $actualAmount,
            'payment_type' => $paymentType,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'paid_at' => $isZeroAmount ? now() : null,
            'remarks' => $params['remarks'] ?? null,
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        if ($pointsUsed > 0) {
            if (!$user->deductPoints($pointsUsed, 'order', $order->id, '订单消费')) {
                throw new \Exception('积分扣减失败');
            }
        }

        return $order->load('items');
    }

    public function cancel(Order $order, ?string $reason = null)
    {
        if (!$order->canCancel()) {
            throw new \Exception('订单当前状态不允许取消');
        }

        return DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->restoreStock($item->quantity);
                }
            }

            if ($order->points_used > 0) {
                $user = $order->user;
                $user->addPoints($order->points_used, 'refund', 'order', $order->id, '订单取消退还积分');
            }

            $order->markAsCancelled($reason);

            return $order;
        });
    }

    protected function generateOrderNo(string $prefix): string
    {
        return $prefix . date('YmdHis') . substr((string)(microtime(true) * 10000), -4) . rand(100, 999);
    }
}
