<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\WechatPayService;
use App\Services\WorkWechatNotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $wechatPayService;
    protected $notifyService;

    public function __construct(
        WechatPayService $wechatPayService,
        WorkWechatNotifyService $notifyService
    ) {
        $this->wechatPayService = $wechatPayService;
        $this->notifyService = $notifyService;
    }

    /**
     * 发起支付
     * POST /api/orders/{id}/pay
     */
    public function pay(Request $request, $id): JsonResponse
    {
        Log::info('开始处理支付请求', [
            'order_id' => $id,
            'openid_header' => $request->header('X-Openid'),
        ]);

        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            Log::warning('用户未登录', ['openid' => $request->header('X-Openid')]);
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        Log::info('用户验证通过', ['user_id' => $user->id]);

        // 查询订单
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            Log::warning('订单不存在', ['order_id' => $id, 'user_id' => $user->id]);
            return response()->json([
                'code' => 404,
                'message' => '订单不存在',
                'data' => null,
            ], 404);
        }

        Log::info('订单查询成功', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
        ]);

        // 检查订单状态
        if (!$order->canPay()) {
            Log::warning('订单状态不允许支付', [
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
            ]);
            return response()->json([
                'code' => 400,
                'message' => '订单状态不允许支付',
                'data' => null,
            ], 400);
        }

        try {
            Log::info('开始调用微信支付API', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'openid' => $user->openid,
            ]);

            // 调用微信支付统一下单
            $paymentParams = $this->wechatPayService->createJsapiOrder($order, $user->openid);

            Log::info('支付参数生成成功', ['prepay_id' => $paymentParams['package'] ?? 'unknown']);

            return response()->json([
                'code' => 200,
                'message' => '支付参数获取成功',
                'data' => $paymentParams,
            ]);
        } catch (\Exception $e) {
            Log::error('发起支付失败', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'code' => 500,
                'message' => '发起支付失败：' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 微信支付回调
     * POST /api/payments/wechat/notify
     */
    public function wechatNotify(Request $request): Response
    {
        try {
            // 获取请求头和请求体
            $headers = $request->headers->all();
            $body = $request->getContent();

            Log::info('收到微信支付回调', [
                'headers' => $headers,
                'body' => $body,
            ]);

            // 验证并解密数据
            $data = $this->wechatPayService->verifyAndDecryptNotify($headers, $body);

            Log::info('支付回调数据解密成功', $data);

            // 处理支付结果
            $this->handlePaymentNotify($data);

            // 返回成功响应给微信
            return response('', 200);
        } catch (\Exception $e) {
            Log::error('处理支付回调失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // 返回失败响应，微信会重试
            return response()->json([
                'code' => 'FAIL',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 处理支付通知
     */
    protected function handlePaymentNotify(array $data): void
    {
        $outTradeNo = $data['out_trade_no'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $tradeState = $data['trade_state'] ?? '';
        $successTime = $data['success_time'] ?? null;

        // 查询订单
        $order = Order::where('order_no', $outTradeNo)->first();

        if (!$order) {
            Log::warning('支付回调订单不存在', ['order_no' => $outTradeNo]);
            throw new \Exception('订单不存在');
        }

        // 检查订单状态（防止重复处理）
        if ($order->payment_status === 1) {
            Log::info('订单已支付，跳过处理', ['order_no' => $outTradeNo]);
            return;
        }

        // 验证支付金额
        $paidAmount = ($data['amount']['total'] ?? 0) / 100; // 分转元
        if (abs($paidAmount - $order->total_amount) > 0.01) {
            Log::error('支付金额不匹配', [
                'order_no' => $outTradeNo,
                'expected' => $order->total_amount,
                'actual' => $paidAmount,
            ]);
            throw new \Exception('支付金额不匹配');
        }

        // 只处理支付成功的回调
        if ($tradeState !== 'SUCCESS') {
            Log::info('支付未成功', ['order_no' => $outTradeNo, 'state' => $tradeState]);
            return;
        }

        // 开始事务处理
        DB::beginTransaction();
        try {
            // 更新订单状态
            $order->markAsPaid($transactionId, [
                'trade_state' => $tradeState,
                'success_time' => $successTime,
                'transaction_id' => $transactionId,
                'payer_openid' => $data['payer']['openid'] ?? '',
            ]);

            // 创建支付记录
            Payment::create([
                'order_id' => $order->id,
                'payment_no' => $transactionId,
                'payment_method' => 'wechat',
                'amount' => $paidAmount,
                'status' => 1,
                'paid_at' => $successTime ? date('Y-m-d H:i:s', strtotime($successTime)) : now(),
                'callback_data' => json_encode($data),
            ]);

            DB::commit();

            Log::info('订单支付成功', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'transaction_id' => $transactionId,
            ]);

            // 发送企业微信通知（异步，不影响主流程）
            try {
                $this->notifyService->sendPaymentSuccessNotify($order);
            } catch (\Exception $e) {
                Log::error('发送企业微信通知失败', [
                    'order_no' => $order->order_no,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
