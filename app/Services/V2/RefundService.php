<?php

namespace App\Services\V2;

use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    protected WechatPayService $wechatPayService;

    public function __construct(WechatPayService $wechatPayService)
    {
        $this->wechatPayService = $wechatPayService;
    }

    /**
     * 申请商城订单退款
     */
    public function applyMallOrderRefund(Order $order, string $reason): Order
    {
        if (!$this->canApplyRefund($order)) {
            throw new \Exception('当前订单状态不允许申请退款');
        }

        $order->refund_status = Order::REFUND_APPLYING;
        $order->refund_reason = $reason;
        $order->save();

        return $order;
    }

    /**
     * 申请订餐订单退款
     */
    public function applyMealOrderRefund(MealOrder $order, string $reason): MealOrder
    {
        if (!$this->canApplyMealRefund($order)) {
            throw new \Exception('当前订单状态不允许申请退款');
        }

        $order->refund_status = 1; // REFUND_APPLYING
        $order->refund_reason = $reason;
        $order->save();

        return $order;
    }

    /**
     * 处理退款（后台审核通过后调用）
     */
    public function processRefund(Order $order): bool
    {
        if ($order->refund_status !== Order::REFUND_APPLYING) {
            throw new \Exception('订单未在退款申请状态');
        }

        return DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->find($order->id);

            // 锁内二次校验退款状态
            if ($order->refund_status !== Order::REFUND_APPLYING) {
                throw new \Exception('订单退款状态已变更');
            }

            // 如果有现金支付，调用微信退款API
            if ($order->actual_amount > 0 && !empty($order->transaction_id) && !str_starts_with($order->transaction_id, 'POINTS_ONLY_')) {
                try {
                    $refundResult = $this->wechatPayService->refund(
                        $order->order_no,
                        $order->transaction_id,
                        (float)$order->actual_amount,
                        (float)$order->actual_amount,
                        $order->refund_reason ?? '用户申请退款'
                    );

                    Log::info('WechatPay refund success', [
                        'order_no' => $order->order_no,
                        'refund_id' => $refundResult['refund_id'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('WechatPay refund failed', [
                        'order_no' => $order->order_no,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \Exception('微信退款失败: ' . $e->getMessage());
                }
            }

            // 退还积分
            if ($order->points_used > 0) {
                $user = User::lockForUpdate()->find($order->user_id);
                if ($user) {
                    if (!$user->addPoints(
                        $order->points_used,
                        'refund',
                        'order',
                        $order->id,
                        '订单退款退还积分'
                    )) {
                        throw new \Exception('积分退还失败');
                    }
                }
            }

            // 恢复库存（预加载商品避免N+1）
            $order->load('items.product');
            foreach ($order->items as $item) {
                if ($item->product) {
                    if (!$item->product->restoreStock($item->quantity)) {
                        throw new \Exception('库存恢复失败');
                    }
                }
            }

            // 更新订单状态
            $order->refund_status = Order::REFUND_SUCCESS;
            $order->refund_amount = $order->actual_amount;
            $order->refund_points = $order->points_used;
            $order->refund_at = now();
            $order->order_status = Order::STATUS_CANCELLED;
            $order->cancelled_at = now();
            $order->cancel_reason = '退款成功';
            $order->save();

            return true;
        });
    }

    /**
     * 处理订餐退款
     */
    public function processMealRefund(MealOrder $order): bool
    {
        if ($order->refund_status !== 1) {
            throw new \Exception('订单未在退款申请状态');
        }

        return DB::transaction(function () use ($order) {
            $order = MealOrder::lockForUpdate()->find($order->id);

            // 退还积分
            if ($order->points_used > 0) {
                $user = User::lockForUpdate()->find($order->user_id);
                if ($user) {
                    $user->addPoints(
                        $order->points_used,
                        'refund',
                        'meal',
                        $order->id,
                        '订餐退款退还积分'
                    );
                }
            }

            // 更新订单状态
            $order->refund_status = 2; // REFUND_SUCCESS
            $order->order_status = MealOrder::STATUS_CANCELLED;
            $order->cancelled_at = now();
            $order->cancel_reason = '退款成功';
            $order->save();

            return true;
        });
    }

    /**
     * 拒绝退款
     */
    public function rejectRefund(Order $order, string $reason): Order
    {
        if ($order->refund_status !== Order::REFUND_APPLYING) {
            throw new \Exception('订单未在退款申请状态');
        }

        $order->refund_status = Order::REFUND_REJECTED;
        $order->refund_reason = $reason;
        $order->save();

        return $order;
    }

    /**
     * 拒绝订餐退款
     */
    public function rejectMealRefund(MealOrder $order, string $reason): MealOrder
    {
        if ($order->refund_status !== MealOrder::REFUND_APPLYING) {
            throw new \Exception('订单未在退款申请状态');
        }

        $order->refund_status = MealOrder::REFUND_REJECTED;
        $order->refund_reason = $reason;
        $order->save();

        return $order;
    }

    protected function canApplyRefund(Order $order): bool
    {
        return $order->payment_status === Order::PAYMENT_STATUS_PAID
            && $order->refund_status === Order::REFUND_NONE
            && in_array($order->order_status, [Order::STATUS_PAID, Order::STATUS_SHIPPED]);
    }

    protected function canApplyMealRefund(MealOrder $order): bool
    {
        return $order->payment_status === MealOrder::PAYMENT_STATUS_PAID
            && ($order->refund_status ?? 0) === 0
            && $order->order_status === MealOrder::STATUS_PAID;
    }
}
