<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Order;
use App\Models\V2\MealOrder;
use App\Models\V2\User;
use App\Services\V2\WechatPayService;
use App\Services\V2\WeworkNotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected WechatPayService $wechatPayService;
    protected WeworkNotifyService $notifyService;

    public function __construct(WechatPayService $wechatPayService, WeworkNotifyService $notifyService)
    {
        $this->wechatPayService = $wechatPayService;
        $this->notifyService = $notifyService;
    }

    /**
     * 商城订单支付
     */
    public function payMallOrder(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('v2_user');
            $order = Order::forUser($user->id)->find($id);

            if (!$order) {
                return $this->error('订单不存在', 404);
            }

            if (!$order->canPay()) {
                return $this->error('订单状态不允许支付');
            }

            if (bccomp($order->actual_amount, '0', 2) === 0) {
                $order->markAsPaid('POINTS_ONLY_' . time());
                // 纯积分支付也发送通知
                try {
                    $this->notifyService->sendMallOrderNotify($order->fresh(['items', 'customer']));
                } catch (\Exception $e) {
                    Log::error('Failed to send mall order notification', ['order_id' => $id, 'error' => $e->getMessage()]);
                }
                return $this->success(['paid' => true], '支付成功（纯积分）');
            }

            $totalFee = (int) bcmul($order->actual_amount, '100', 0);
            $description = '瑷婴商城订单-' . $order->order_no;

            $payParams = $this->wechatPayService->createJsapiOrder(
                $order->order_no,
                $totalFee,
                $description,
                $user->openid
            );

            return $this->success($payParams, '获取支付参数成功');
        } catch (\Exception $e) {
            Log::error('PayMallOrder error', ['error' => $e->getMessage(), 'order_id' => $id]);
            return $this->error($e->getMessage());
        }
    }

    /**
     * 订餐订单支付
     */
    public function payMealOrder(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('v2_user');
            $order = MealOrder::forUser($user->id)->find($id);

            if (!$order) {
                return $this->error('订单不存在', 404);
            }

            if (!$order->canPay()) {
                return $this->error('订单状态不允许支付');
            }

            if (bccomp($order->actual_amount, '0', 2) === 0) {
                $order->markAsPaid('POINTS_ONLY_' . time());
                $this->notifyService->sendMealOrderNotify($order);
                return $this->success(['paid' => true], '支付成功（纯积分）');
            }

            $totalFee = (int) bcmul($order->actual_amount, '100', 0);
            $description = '瑷婴订餐-' . $order->order_no;

            $payParams = $this->wechatPayService->createJsapiOrder(
                $order->order_no,
                $totalFee,
                $description,
                $user->openid
            );

            return $this->success($payParams, '获取支付参数成功');
        } catch (\Exception $e) {
            Log::error('PayMealOrder error', ['error' => $e->getMessage(), 'order_id' => $id]);
            return $this->error($e->getMessage());
        }
    }

    /**
     * 微信支付回调
     */
    public function notify(Request $request): JsonResponse
    {
        try {
            $headers = $request->headers->all();
            $body = $request->getContent();

            $data = $this->wechatPayService->verifyAndDecryptNotify($headers, $body);

            if ($data['trade_state'] !== 'SUCCESS') {
                return $this->notifySuccess();
            }

            $orderNo = $data['out_trade_no'];
            $transactionId = $data['transaction_id'];

            // 验证订单金额一致性
            $totalFee = $data['amount']['total'] ?? 0;

            // 用于在事务外发送通知
            $mallOrderToNotify = null;
            $mealOrderToNotify = null;

            $processed = DB::transaction(function () use ($orderNo, $transactionId, $totalFee, &$mallOrderToNotify, &$mealOrderToNotify) {
                $mallOrder = Order::where('order_no', $orderNo)
                    ->lockForUpdate()
                    ->first();

                if ($mallOrder) {
                    // 幂等性检查
                    if ($mallOrder->payment_status === Order::PAYMENT_STATUS_PAID) {
                        return 'already_paid';
                    }

                    // 验证金额一致性
                    $expectedAmount = (int) bcmul($mallOrder->actual_amount, '100', 0);
                    if ($totalFee !== $expectedAmount) {
                        Log::error('Payment amount mismatch', [
                            'order_no' => $orderNo,
                            'expected' => $expectedAmount,
                            'actual' => $totalFee,
                        ]);
                        throw new \Exception('订单金额不一致');
                    }

                    $mallOrder->markAsPaid($transactionId);
                    $mallOrderToNotify = $mallOrder->fresh(['items', 'customer']);
                    return 'mall_order_paid';
                }

                $mealOrder = MealOrder::where('order_no', $orderNo)
                    ->lockForUpdate()
                    ->first();

                if ($mealOrder) {
                    // 幂等性检查
                    if ($mealOrder->payment_status === MealOrder::PAYMENT_STATUS_PAID) {
                        return 'already_paid';
                    }

                    // 验证金额一致性
                    $expectedAmount = (int) bcmul($mealOrder->actual_amount, '100', 0);
                    if ($totalFee !== $expectedAmount) {
                        Log::error('Payment amount mismatch', [
                            'order_no' => $orderNo,
                            'expected' => $expectedAmount,
                            'actual' => $totalFee,
                        ]);
                        throw new \Exception('订单金额不一致');
                    }

                    $mealOrder->markAsPaid($transactionId);
                    $mealOrderToNotify = $mealOrder->fresh(['items', 'customer']);
                    return 'meal_order_paid';
                }

                Log::warning('Payment notify: order not found', ['order_no' => $orderNo]);
                return 'order_not_found';
            });

            // 事务提交后发送通知（避免持锁期间网络IO）
            if ($mallOrderToNotify) {
                try {
                    $this->notifyService->sendMallOrderNotify($mallOrderToNotify);
                } catch (\Exception $e) {
                    Log::error('Failed to send mall order notification', [
                        'order_no' => $orderNo,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($mealOrderToNotify) {
                try {
                    $this->notifyService->sendMealOrderNotify($mealOrderToNotify);
                } catch (\Exception $e) {
                    Log::error('Failed to send meal order notification', [
                        'order_no' => $orderNo,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 根据处理结果返回
            if ($processed === 'order_not_found') {
                Log::error('Payment callback: order not found', ['order_no' => $orderNo]);
                return response()->json([
                    'code' => 'FAIL',
                    'message' => '订单不存在',
                ], 500);
            }

            return $this->notifySuccess();
        } catch (\Exception $e) {
            Log::error('Payment notify error', ['error' => $e->getMessage()]);
            return response()->json([
                'code' => 'FAIL',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function notifySuccess(): JsonResponse
    {
        return response()->json([
            'code' => 'SUCCESS',
            'message' => '成功',
        ]);
    }

    protected function success($data = null, string $message = '操作成功'): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function error(string $message, int $httpCode = 400): JsonResponse
    {
        return response()->json([
            'code' => $httpCode,
            'message' => $message,
            'data' => null,
        ], $httpCode);
    }
}
