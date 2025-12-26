<?php

namespace App\Services;

use App\Models\V2\Wallet;
use App\Models\V2\RechargeOrder;
use App\Models\V2\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletPaymentService
{
    protected WalletService $walletService;
    protected PaymentPasswordService $passwordService;
    protected WechatPayService $wechatPayService;

    public function __construct(
        WalletService $walletService,
        PaymentPasswordService $passwordService,
        WechatPayService $wechatPayService
    ) {
        $this->walletService = $walletService;
        $this->passwordService = $passwordService;
        $this->wechatPayService = $wechatPayService;
    }

    public function createRechargeOrder(int $userId, float $amount): array
    {
        $this->validateRechargeAmount($amount);

        $wallet = $this->walletService->getOrCreateWallet($userId);

        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已冻结，无法充值');
        }

        $rechargeOrder = RechargeOrder::create([
            'order_no' => RechargeOrder::generateOrderNo(),
            'user_id' => $userId,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'status' => RechargeOrder::STATUS_PENDING,
            'expired_at' => now()->addMinutes(30),
        ]);

        $user = $wallet->user;
        $payParams = $this->wechatPayService->createJsapiOrder([
            'out_trade_no' => $rechargeOrder->order_no,
            'description' => '钱包充值',
            'amount' => (int) ($amount * 100),
            'openid' => $user->openid,
            'notify_url' => config('wechat_pay.notify_url_recharge', config('wechat_pay.notify_url')),
        ]);

        $rechargeOrder->prepay_id = $payParams['prepay_id'] ?? null;
        $rechargeOrder->save();

        return [
            'order_no' => $rechargeOrder->order_no,
            'amount' => $rechargeOrder->amount,
            'pay_params' => $payParams,
        ];
    }

    public function handleRechargeNotify(string $orderNo, string $transactionId): bool
    {
        $rechargeOrder = RechargeOrder::where('order_no', $orderNo)->first();

        if (!$rechargeOrder) {
            throw new \Exception('充值订单不存在');
        }

        if ($rechargeOrder->isSuccess()) {
            return true;
        }

        return DB::transaction(function () use ($rechargeOrder, $transactionId) {
            $rechargeOrder = RechargeOrder::lockForUpdate()->find($rechargeOrder->id);

            if ($rechargeOrder->isSuccess()) {
                return true;
            }

            $rechargeOrder->status = RechargeOrder::STATUS_SUCCESS;
            $rechargeOrder->transaction_id = $transactionId;
            $rechargeOrder->paid_at = now();
            $rechargeOrder->save();

            $wallet = $this->walletService->getOrCreateWallet($rechargeOrder->user_id);

            $this->walletService->credit(
                $wallet,
                $rechargeOrder->amount,
                WalletTransaction::TYPE_TOPUP,
                WalletTransaction::SOURCE_WECHAT,
                $rechargeOrder->id,
                $transactionId,
                '微信充值'
            );

            return true;
        });
    }

    public function payWithWallet(Wallet $wallet, float $amount, string $password, string $source, int $orderId): WalletTransaction
    {
        $this->passwordService->verifyPassword($wallet, $password);

        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已冻结，无法支付');
        }

        if ($wallet->balance < $amount) {
            throw new \Exception('余额不足');
        }

        return $this->walletService->debit(
            $wallet,
            $amount,
            WalletTransaction::TYPE_CONSUME,
            $source,
            $orderId,
            '订单支付'
        );
    }

    public function payWithMixed(Wallet $wallet, float $totalAmount, float $walletAmount, string $password, string $source, int $orderId, string $openid): array
    {
        if ($walletAmount > 0) {
            $this->passwordService->verifyPassword($wallet, $password);
        }

        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已冻结，无法支付');
        }

        $walletAmount = min($walletAmount, $wallet->balance, $totalAmount);
        $wechatAmount = $totalAmount - $walletAmount;

        $result = [
            'wallet_amount' => $walletAmount,
            'wechat_amount' => $wechatAmount,
            'wallet_transaction' => null,
            'wechat_params' => null,
        ];

        if ($walletAmount > 0) {
            $result['wallet_transaction'] = $this->walletService->debit(
                $wallet,
                $walletAmount,
                WalletTransaction::TYPE_CONSUME,
                $source,
                $orderId,
                '订单支付（钱包部分）'
            );
        }

        if ($wechatAmount > 0) {
            $result['wechat_params'] = $this->wechatPayService->createJsapiOrder([
                'out_trade_no' => $source . '_' . $orderId . '_' . time(),
                'description' => '订单支付',
                'amount' => (int) ($wechatAmount * 100),
                'openid' => $openid,
            ]);
        }

        return $result;
    }

    private function validateRechargeAmount(float $amount): void
    {
        $min = (float) $this->getConfig('topup_min', 1);
        $max = (float) $this->getConfig('topup_max', 200);

        if ($amount < $min) {
            throw new \InvalidArgumentException("充值金额不能小于{$min}元");
        }

        if ($amount > $max) {
            throw new \InvalidArgumentException("充值金额不能大于{$max}元");
        }
    }

    private function getConfig(string $key, $default = null)
    {
        $config = DB::table('wallet_configs')->where('config_key', $key)->first();
        return $config ? $config->config_value : $default;
    }
}
