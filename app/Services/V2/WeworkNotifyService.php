<?php

namespace App\Services\V2;

use App\Models\V2\MealOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WeworkNotifyService
{
    protected string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('wechat_pay.work_wechat_webhook');
    }

    /**
     * 发送商城订单通知
     */
    public function sendMallOrderNotify(\App\Models\V2\Order $order): bool
    {
        $order->load(['items', 'customer']);

        $customerName = $order->customer?->customer_name ?? '未知客户';
        $receiverInfo = $order->receiver_name ?? '未知收货人';

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = sprintf('  - %s x%d: ¥%.2f', $item->product_name, $item->quantity, $item->subtotal);
        }

        $deliveryType = $order->delivery_type === 'express' ? '快递配送' : '送到房间';

        $content = sprintf(
            "🛒 **新商城订单**\n\n" .
            "**订单号**: %s\n" .
            "**客户**: %s\n" .
            "**收货人**: %s\n" .
            "**配送方式**: %s\n" .
            "**商品详情**:\n%s\n\n" .
            "**订单金额**: ¥%.2f\n" .
            "**实付金额**: ¥%.2f\n" .
            "**支付时间**: %s",
            $order->order_no,
            $customerName,
            $receiverInfo,
            $deliveryType,
            implode("\n", $itemDetails),
            $order->total_amount,
            $order->actual_amount,
            $order->paid_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s')
        );

        return $this->sendMarkdown($content, 'new_mall_order', $order->id);
    }

    /**
     * 发送订餐订单通知
     */
    public function sendMealOrderNotify(MealOrder $order): bool
    {
        $order->load(['items', 'customer']);

        $customerName = $order->customer_name ?? $order->customer?->customer_name ?? '未知客户';
        $roomName = $order->room_name ?? '未知房间';

        $mealDetails = [];
        foreach ($order->items as $item) {
            $mealDetails[] = sprintf(
                '  - %s %s: %d份 ¥%.2f',
                $item->meal_date->format('m-d'),
                $this->getMealTypeName($item->meal_type),
                $item->quantity,
                $item->subtotal
            );
        }

        $content = sprintf(
            "🍽️ **新订餐订单**\n\n" .
            "**订单号**: %s\n" .
            "**客户**: %s\n" .
            "**房间**: %s\n" .
            "**订餐详情**:\n%s\n\n" .
            "**订单金额**: ¥%.2f\n" .
            "**实付金额**: ¥%.2f\n" .
            "**支付时间**: %s",
            $order->order_no,
            $customerName,
            $roomName,
            implode("\n", $mealDetails),
            $order->total_amount,
            $order->actual_amount,
            $order->paid_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s')
        );

        return $this->sendMarkdown($content, 'new_meal_order', $order->id);
    }

    /**
     * 发送每日订餐统计
     */
    public function sendDailyMealReport(string $date): bool
    {
        $stats = DB::table('meal_order_items')
            ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
            ->where('meal_order_items.meal_date', $date)
            ->whereIn('meal_orders.order_status', [MealOrder::STATUS_PAID, MealOrder::STATUS_COMPLETED])
            ->whereNull('meal_orders.deleted_at')
            ->select('meal_order_items.meal_type', DB::raw('SUM(meal_order_items.quantity) as total_quantity'))
            ->groupBy('meal_order_items.meal_type')
            ->get()
            ->keyBy('meal_type');

        $breakfast = $stats->get('breakfast')?->total_quantity ?? 0;
        $lunch = $stats->get('lunch')?->total_quantity ?? 0;
        $dinner = $stats->get('dinner')?->total_quantity ?? 0;
        $total = $breakfast + $lunch + $dinner;

        if ($total == 0) {
            return true;
        }

        $content = sprintf(
            "📊 **%s 订餐统计**\n\n" .
            "🌅 早餐: **%d** 份\n" .
            "☀️ 午餐: **%d** 份\n" .
            "🌙 晚餐: **%d** 份\n\n" .
            "**合计**: %d 份",
            $date,
            $breakfast,
            $lunch,
            $dinner,
            $total
        );

        return $this->sendMarkdown($content, 'daily_meal_report');
    }

    /**
     * 发送Markdown消息
     */
    protected function sendMarkdown(string $content, string $type, ?int $relatedId = null): bool
    {
        if (empty($this->webhookUrl)) {
            Log::warning('WeworkNotify: webhook URL is not configured');
            return false;
        }

        $data = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $content,
            ],
        ];

        $result = $this->send($data);
        $success = $result['errcode'] === 0;

        $this->logNotification($type, $content, $relatedId, $success, $result['errmsg'] ?? null);

        return $success;
    }

    /**
     * 发送请求到企业微信
     */
    protected function send(array $data): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('WeworkNotify curl error', ['error' => $error]);
            return ['errcode' => -1, 'errmsg' => $error];
        }

        return json_decode($response, true) ?? ['errcode' => -1, 'errmsg' => 'Invalid response'];
    }

    /**
     * 记录通知日志
     */
    protected function logNotification(string $type, string $content, ?int $relatedId, bool $success, ?string $errorMessage): void
    {
        try {
            DB::table('notification_logs')->insert([
                'type' => $type,
                'channel' => 'wechat_robot',
                'content' => $content,
                'related_id' => $relatedId,
                'status' => $success ? 1 : 2,
                'error_message' => $errorMessage,
                'sent_at' => $success ? now() : null,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('WeworkNotify log failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 获取餐次名称
     */
    protected function getMealTypeName(string $type): string
    {
        return match ($type) {
            'breakfast' => '早餐',
            'lunch' => '午餐',
            'dinner' => '晚餐',
            default => $type,
        };
    }
}
