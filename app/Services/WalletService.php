<?php

namespace App\Services;

use App\Models\V2\Wallet;
use App\Models\V2\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getOrCreateWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'status' => Wallet::STATUS_NORMAL, 'version' => 1]
        );
    }

    public function getWalletByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function credit(Wallet $wallet, float $amount, string $type, string $source, ?int $sourceId = null, ?string $transactionId = null, ?string $reason = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('金额必须大于0');
        }

        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已冻结，无法操作');
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $source, $sourceId, $transactionId, $reason) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->version += 1;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'source' => $source,
                'source_id' => $sourceId,
                'transaction_id' => $transactionId,
                'status' => WalletTransaction::STATUS_SUCCESS,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        });
    }

    public function debit(Wallet $wallet, float $amount, string $type, string $source, ?int $sourceId = null, ?string $reason = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('金额必须大于0');
        }

        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已冻结，无法操作');
        }

        if ($wallet->balance < $amount) {
            throw new \Exception('余额不足');
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $source, $sourceId, $reason) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            if ($wallet->balance < $amount) {
                throw new \Exception('余额不足');
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance -= $amount;
            $wallet->version += 1;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => $type,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'source' => $source,
                'source_id' => $sourceId,
                'status' => WalletTransaction::STATUS_SUCCESS,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        });
    }

    public function adjust(Wallet $wallet, float $amount, int $operatorId, string $reason): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $operatorId, $reason) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;

            if ($wallet->balance < 0) {
                throw new \Exception('调整后余额不能为负数');
            }

            $wallet->version += 1;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => WalletTransaction::TYPE_ADJUST,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'source' => WalletTransaction::SOURCE_ADMIN,
                'operator_id' => $operatorId,
                'reason' => $reason,
                'status' => WalletTransaction::STATUS_SUCCESS,
                'created_at' => now(),
            ]);
        });
    }

    public function freeze(Wallet $wallet, int $operatorId, string $reason): void
    {
        if ($wallet->isFrozen()) {
            throw new \Exception('钱包已处于冻结状态');
        }

        DB::transaction(function () use ($wallet, $operatorId, $reason) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $wallet->status = Wallet::STATUS_FROZEN;
            $wallet->frozen_at = now();
            $wallet->frozen_by = $operatorId;
            $wallet->frozen_reason = $reason;
            $wallet->version += 1;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => WalletTransaction::TYPE_FREEZE,
                'amount' => 0,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'source' => WalletTransaction::SOURCE_ADMIN,
                'operator_id' => $operatorId,
                'reason' => $reason,
                'status' => WalletTransaction::STATUS_SUCCESS,
                'created_at' => now(),
            ]);
        });
    }

    public function unfreeze(Wallet $wallet, int $operatorId, string $reason): void
    {
        if (!$wallet->isFrozen()) {
            throw new \Exception('钱包未处于冻结状态');
        }

        DB::transaction(function () use ($wallet, $operatorId, $reason) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $wallet->status = Wallet::STATUS_NORMAL;
            $wallet->frozen_at = null;
            $wallet->frozen_by = null;
            $wallet->frozen_reason = null;
            $wallet->version += 1;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => WalletTransaction::TYPE_UNFREEZE,
                'amount' => 0,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'source' => WalletTransaction::SOURCE_ADMIN,
                'operator_id' => $operatorId,
                'reason' => $reason,
                'status' => WalletTransaction::STATUS_SUCCESS,
                'created_at' => now(),
            ]);
        });
    }

    public function getTransactions(int $userId, ?string $type = null, int $page = 1, int $perPage = 20)
    {
        $query = WalletTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
