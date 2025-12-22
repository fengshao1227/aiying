<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkWechatNotifyService
{
    protected $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('wechat_pay.work_wechat_webhook');
    }

    /**
     * 发送支付成功通知
     *
     * @param Order $order
     * @return bool
     */
    public function sendPaymentSuccessNotify(Order $order): bool
    {
        try {
            $message = $this->buildPaymentSuccessMessage($order);

            $response = Http::post($this->webhookUrl, $message);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['errcode']) && $result['errcode'] === 0) {
                    Log::info('企业微信通知发送成功', ['order_no' => $order->order_no]);
                    return true;
                }

                Log::warning('企业微信通知发送失败', [
                    'order_no' => $order->order_no,
                    'response' => $result,
                ]);
                return false;
            }

            Log::error('企业微信通知请求失败', [
                'order_no' => $order->order_no,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('企业微信通知异常', [
                'order_no' => $order->order_no,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 构建支付成功消息
     */
    protected function buildPaymentSuccessMessage(Order $order): array
    {
        // 获取商品信息
        $items = $order->items->map(function ($item) {
            return sprintf(
                '%s x%d',
                $item->product_name,
                $item->quantity
            );
        })->implode('、');

        // 订单类型
        $orderTypeText = $order->order_type === 'goods' ? '商品订单' : '套餐订单';

        // 构建Markdown消息
        $content = sprintf(
            "## 💰 新订单支付成功\n\n" .
            "**订单类型**: %s\n" .
            "**订单号**: %s\n" .
            "**用户**: %s (%s)\n" .
            "**商品**: %s\n" .
            "**金额**: ¥%s\n" .
            "**支付时间**: %s\n",
            $orderTypeText,
            $order->order_no,
            $order->receiver_name ?? '未知',
            $this->maskPhone($order->receiver_phone ?? ''),
            $items,
            number_format($order->total_amount, 2),
            $order->payment_time ? $order->payment_time->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
        );

        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $content,
            ],
        ];
    }

    /**
     * 手机号脱敏
     */
    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) === 11) {
            return substr($phone, 0, 3) . '****' . substr($phone, 7);
        }

        return $phone;
    }
}
