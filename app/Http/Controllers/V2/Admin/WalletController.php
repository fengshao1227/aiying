<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\V2\Wallet;
use App\Models\V2\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Wallet::with('user:id,nickname,phone,avatar');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $wallets = $query->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $wallets->items(),
                'total' => $wallets->total(),
                'current_page' => $wallets->currentPage(),
                'last_page' => $wallets->lastPage(),
            ],
        ]);
    }

    public function show(int $userId): JsonResponse
    {
        $wallet = Wallet::with('user:id,nickname,phone,avatar')
            ->where('user_id', $userId)
            ->first();

        if (!$wallet) {
            return response()->json([
                'code' => 404,
                'message' => '钱包不存在',
            ], 404);
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $wallet,
        ]);
    }

    public function adjust(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ], [
            'amount.required' => '请输入调整金额',
            'reason.required' => '请输入调整原因',
        ]);

        $wallet = $this->walletService->getOrCreateWallet($userId);
        $admin = $request->user();

        try {
            $transaction = $this->walletService->adjust(
                $wallet,
                (float) $request->amount,
                $admin->id,
                $request->reason
            );

            return response()->json([
                'code' => 0,
                'message' => '余额调整成功',
                'data' => [
                    'new_balance' => $wallet->fresh()->balance,
                    'transaction_id' => $transaction->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function freeze(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json([
                'code' => 404,
                'message' => '钱包不存在',
            ], 404);
        }

        $admin = $request->user();

        try {
            $this->walletService->freeze($wallet, $admin->id, $request->reason);

            return response()->json([
                'code' => 0,
                'message' => '钱包已冻结',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function unfreeze(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json([
                'code' => 404,
                'message' => '钱包不存在',
            ], 404);
        }

        $admin = $request->user();

        try {
            $this->walletService->unfreeze($wallet, $admin->id, $request->reason);

            return response()->json([
                'code' => 0,
                'message' => '钱包已解冻',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function transactions(Request $request, int $userId): JsonResponse
    {
        $query = WalletTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $transactions->items(),
                'total' => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    public function configs(Request $request): JsonResponse
    {
        $configs = \DB::table('wallet_configs')->get();

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $configs,
        ]);
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $request->validate([
            'config_key' => 'required|string',
            'config_value' => 'required|string',
        ]);

        $admin = $request->user();

        \DB::table('wallet_configs')
            ->where('config_key', $request->config_key)
            ->update([
                'config_value' => $request->config_value,
                'updated_by' => $admin->id,
                'updated_at' => now(),
            ]);

        return response()->json([
            'code' => 0,
            'message' => '配置更新成功',
        ]);
    }
}
