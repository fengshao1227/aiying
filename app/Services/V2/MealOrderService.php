<?php

namespace App\Services\V2;

use App\Models\V2\MealOrder;
use App\Models\V2\MealOrderItem;
use App\Models\V2\MealConfig;
use App\Models\V2\User;
use Illuminate\Support\Facades\DB;

class MealOrderService
{
    public function create(User $user, array $params)
    {
        return DB::transaction(function () use ($user, $params) {
            $user = User::lockForUpdate()->find($user->id);

            if (!$user->customer_id) {
                throw new \Exception('请先绑定客户信息');
            }

            $items = $params['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('请选择订餐项');
            }

            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $mealConfig = MealConfig::where('meal_type', $item['meal_type'])->first();
                if (!$mealConfig || $mealConfig->status !== MealConfig::STATUS_ENABLED) {
                    throw new \Exception("餐次 {$item['meal_type']} 不可用");
                }

                $mealDate = new \DateTime($item['meal_date']);
                if (!$mealConfig->isAvailableForDate($mealDate)) {
                    throw new \Exception("餐次 {$mealConfig->name} 在 {$item['meal_date']} 不可预订");
                }

                $quantity = $item['quantity'] ?? 1;
                $subtotal = bcmul((string)$mealConfig->price, (string)$quantity, 2);
                $totalAmount = bcadd((string)$totalAmount, $subtotal, 2);

                $orderItems[] = [
                    'meal_date' => $item['meal_date'],
                    'meal_type' => $item['meal_type'],
                    'meal_name' => $mealConfig->name,
                    'quantity' => $quantity,
                    'unit_price' => $mealConfig->price,
                    'subtotal' => $subtotal,
                ];
            }

            $pointsUsed = $params['points_used'] ?? 0;
            $pointsDiscount = 0;

            if ($pointsUsed > 0) {
                if ($pointsUsed > $user->points_balance) {
                    throw new \Exception('积分余额不足');
                }

                $pointsDiscount = bcdiv((string)$pointsUsed, '100', 2);
            }

            $actualAmount = bcsub((string)$totalAmount, (string)$pointsDiscount, 2);
            if (bccomp($actualAmount, '0', 2) < 0) {
                $actualAmount = '0.00';
            }

            $isZeroAmount = bccomp($actualAmount, '0', 2) === 0;
            $paymentType = $isZeroAmount ? MealOrder::PAYMENT_POINTS : ($pointsUsed > 0 ? MealOrder::PAYMENT_MIXED : MealOrder::PAYMENT_CASH);
            $orderStatus = $isZeroAmount ? MealOrder::STATUS_PAID : MealOrder::STATUS_PENDING;
            $paymentStatus = $isZeroAmount ? MealOrder::PAYMENT_STATUS_PAID : MealOrder::PAYMENT_STATUS_UNPAID;

            $order = MealOrder::create([
                'order_no' => $this->generateOrderNo('ME'),
                'user_id' => $user->id,
                'customer_id' => $user->customer_id,
                'room_id' => $params['room_id'] ?? null,
                'room_name' => $params['room_name'] ?? null,
                'customer_name' => $params['customer_name'] ?? null,
                'total_amount' => $totalAmount,
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
                if (!$user->deductPoints($pointsUsed, 'meal', $order->id, '订餐消费')) {
                    throw new \Exception('积分扣减失败');
                }
            }

            return $order->load('items');
        });
    }

    public function cancel(MealOrder $order, ?string $reason = null)
    {
        if (!$order->canCancel()) {
            throw new \Exception('订单当前状态不允许取消');
        }

        return DB::transaction(function () use ($order, $reason) {
            if ($order->points_used > 0) {
                $user = $order->user;
                $user->addPoints($order->points_used, 'refund', 'meal', $order->id, '订餐取消退还积分');
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
