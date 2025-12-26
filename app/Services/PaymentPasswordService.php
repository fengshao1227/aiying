<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\V2\Wallet;

class PaymentPasswordService
{
    private int $maxFailCount = 5;
    private int $lockMinutes = 30;

    public function __construct()
    {
        $this->maxFailCount = (int) $this->getConfig('password_max_fail', 5);
        $this->lockMinutes = (int) $this->getConfig('password_lock_minutes', 30);
    }

    public function setPassword(Wallet $wallet, string $password): void
    {
        if (!$this->isValidPassword($password)) {
            throw new \InvalidArgumentException('支付密码必须是6位数字');
        }

        $wallet->payment_password = Hash::make($password);
        $wallet->password_set_at = now();
        $wallet->password_fail_count = 0;
        $wallet->password_locked_until = null;
        $wallet->save();
    }

    public function verifyPassword(Wallet $wallet, string $password): bool
    {
        if (!$wallet->hasPassword()) {
            throw new \Exception('请先设置支付密码');
        }

        return DB::transaction(function () use ($wallet, $password) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            if ($wallet->isPasswordLocked()) {
                $remaining = now()->diffInMinutes($wallet->password_locked_until);
                throw new \Exception("密码已锁定，请{$remaining}分钟后再试");
            }

            if (Hash::check($password, $wallet->payment_password)) {
                $wallet->password_fail_count = 0;
                $wallet->save();
                return true;
            }

            $wallet->password_fail_count += 1;

            if ($wallet->password_fail_count >= $this->maxFailCount) {
                $wallet->password_locked_until = now()->addMinutes($this->lockMinutes);
                $wallet->save();
                throw new \Exception('密码错误次数过多，请稍后再试');
            }

            $wallet->save();
            throw new \Exception('支付密码错误');
        });
    }

    public function changePassword(Wallet $wallet, string $oldPassword, string $newPassword): void
    {
        $this->verifyPassword($wallet, $oldPassword);
        $this->setPassword($wallet, $newPassword);
    }

    public function resetFailCount(Wallet $wallet): void
    {
        $wallet->password_fail_count = 0;
        $wallet->password_locked_until = null;
        $wallet->save();
    }

    private function isValidPassword(string $password): bool
    {
        return preg_match('/^\d{6}$/', $password) === 1;
    }

    private function getConfig(string $key, $default = null)
    {
        $config = DB::table('wallet_configs')->where('config_key', $key)->first();
        return $config ? $config->config_value : $default;
    }
}
