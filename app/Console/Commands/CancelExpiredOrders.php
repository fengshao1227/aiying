<?php

namespace App\Console\Commands;

use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\SystemConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Cancel orders that have not been paid within the configured timeout period';

    public function handle(): int
    {
        $minutes = SystemConfig::getOrderCancelMinutes() ?? 30;
        $cutoffTime = now()->subMinutes($minutes);

        $this->info("Cancelling orders created before: {$cutoffTime}");
        Log::info('CancelExpiredOrders started', ['cutoff' => $cutoffTime, 'minutes' => $minutes]);

        $mallCancelled = $this->cancelMallOrders($cutoffTime);
        $mealCancelled = $this->cancelMealOrders($cutoffTime);

        $this->info("Cancelled: {$mallCancelled} mall orders, {$mealCancelled} meal orders");
        Log::info('CancelExpiredOrders completed', [
            'mall_cancelled' => $mallCancelled,
            'meal_cancelled' => $mealCancelled,
        ]);

        return self::SUCCESS;
    }

    protected function cancelMallOrders(\DateTimeInterface $cutoffTime): int
    {
        $expiredOrders = Order::where('payment_status', Order::PAYMENT_STATUS_UNPAID)
            ->where('order_status', Order::STATUS_PENDING)
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    $order = Order::lockForUpdate()->find($order->id);

                    if ($order->order_status !== Order::STATUS_PENDING ||
                        $order->payment_status !== Order::PAYMENT_STATUS_UNPAID) {
                        return;
                    }

                    $order->load('items.product');
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->restoreStock($item->quantity);
                        }
                    }

                    $order->markAsCancelled('支付超时自动取消');
                });
                $count++;
                Log::info("Auto cancelled mall order: {$order->order_no}");
            } catch (\Throwable $e) {
                Log::error("Failed to cancel mall order: {$order->order_no}", ['exception' => $e]);
            }
        }

        return $count;
    }

    protected function cancelMealOrders(\DateTimeInterface $cutoffTime): int
    {
        $expiredOrders = MealOrder::where('payment_status', MealOrder::PAYMENT_STATUS_UNPAID)
            ->where('order_status', MealOrder::STATUS_PENDING)
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    $order = MealOrder::lockForUpdate()->find($order->id);

                    if ($order->order_status !== MealOrder::STATUS_PENDING ||
                        $order->payment_status !== MealOrder::PAYMENT_STATUS_UNPAID) {
                        return;
                    }

                    $order->markAsCancelled('支付超时自动取消');
                });
                $count++;
                Log::info("Auto cancelled meal order: {$order->order_no}");
            } catch (\Throwable $e) {
                Log::error("Failed to cancel meal order: {$order->order_no}", ['exception' => $e]);
            }
        }

        return $count;
    }
}
