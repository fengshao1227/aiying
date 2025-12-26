<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\WalletPaymentService;
use App\Services\PaymentPasswordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    protected WalletService $walletService;
    protected WalletPaymentService $paymentService;
    protected PaymentPasswordService $passwordService;

    public function __construct(
        WalletService $walletService,
        WalletPaymentService $paymentService,
        PaymentPasswordService $passwordService
    ) {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
        $this->passwordService = $passwordService;
    }

    public function info(Request $request): JsonResponse
    {
        $user = $request->user;
        $wallet = $this->walletService->getOrCreateWallet($user->id);

        // 计算累计充值和累计消费
        $stats = \App\Models\V2\WalletTransaction::where('user_id', $user->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'topup' AND amount > 0 THEN amount ELSE 0 END), 0) as total_recharged,
                COALESCE(SUM(CASE WHEN type = 'consume' AND amount < 0 THEN ABS(amount) ELSE 0 END), 0) as total_consumed
            ")
            ->first();

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'balance' => $wallet->balance,
                'status' => $wallet->isFrozen() ? 'frozen' : 'active',
                'has_password' => $wallet->hasPassword(),
                'total_recharged' => number_format($stats->total_recharged ?? 0, 2, '.', ''),
                'total_consumed' => number_format($stats->total_consumed ?? 0, 2, '.', ''),
            ],
        ]);
    }

    public function setPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|size:6|regex:/^\d{6}$/',
        ], [
            'password.required' => '请输入支付密码',
            'password.size' => '支付密码必须是6位数字',
            'password.regex' => '支付密码必须是6位数字',
        ]);

        $user = $request->user;
        $wallet = $this->walletService->getOrCreateWallet($user->id);

        if ($wallet->hasPassword()) {
            return response()->json([
                'code' => 400,
                'message' => '支付密码已设置，如需修改请使用修改密码接口',
            ], 400);
        }

        try {
            $this->passwordService->setPassword($wallet, $request->password);
            return response()->json([
                'code' => 0,
                'message' => '支付密码设置成功',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'old_password' => 'required|string|size:6',
            'new_password' => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        $user = $request->user;
        $wallet = $this->walletService->getOrCreateWallet($user->id);

        try {
            $this->passwordService->changePassword($wallet, $request->old_password, $request->new_password);
            return response()->json([
                'code' => 0,
                'message' => '支付密码修改成功',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function verifyPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|size:6',
        ]);

        $user = $request->user;
        $wallet = $this->walletService->getOrCreateWallet($user->id);

        try {
            $this->passwordService->verifyPassword($wallet, $request->password);
            return response()->json([
                'code' => 0,
                'message' => '密码验证成功',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function recharge(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ], [
            'amount.required' => '请输入充值金额',
            'amount.numeric' => '充值金额格式错误',
            'amount.min' => '充值金额不能小于0.01元',
        ]);

        $user = $request->user;

        try {
            $result = $this->paymentService->createRechargeOrder($user->id, (float) $request->amount);

            return response()->json([
                'code' => 0,
                'message' => '充值订单创建成功',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user;

        $type = $request->query('type');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $transactions = $this->walletService->getTransactions($user->id, $type, $page, $perPage);

        $list = $transactions->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'type_text' => $item->type_text,
                'amount' => abs($item->amount),
                'direction' => $item->direction,
                'balance_after' => $item->balance_after,
                'remark' => $item->reason,
                'created_at' => $item->created_at->toISOString(),
            ];
        });

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $list,
                'total' => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }
}
