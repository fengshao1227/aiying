<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WechatService
{
    private string $appId;
    private string $appSecret;
    private string $apiUrl = 'https://api.weixin.qq.com/sns/jscode2session';

    public function __construct()
    {
        $this->appId = config('wechat.mini_program.app_id');
        $this->appSecret = config('wechat.mini_program.app_secret');
    }

    /**
     * 通过code获取微信用户openid和session_key
     */
    public function code2Session(string $code): array
    {
        try {
            $response = Http::get($this->apiUrl, [
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ]);

            $data = $response->json();

            if (isset($data['errcode']) && $data['errcode'] !== 0) {
                Log::error('微信登录失败', $data);
                throw new \Exception($data['errmsg'] ?? '微信登录失败');
            }

            return [
                'openid' => $data['openid'],
                'session_key' => $data['session_key'],
                'unionid' => $data['unionid'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('微信登录异常', [
                'message' => $e->getMessage(),
                'code' => $code,
            ]);
            throw $e;
        }
    }

    /**
     * 解密微信手机号
     */
    public function decryptPhone(string $sessionKey, string $encryptedData, string $iv): ?string
    {
        try {
            $sessionKey = base64_decode($sessionKey);
            $encryptedData = base64_decode($encryptedData);
            $iv = base64_decode($iv);

            $decrypted = openssl_decrypt(
                $encryptedData,
                'AES-128-CBC',
                $sessionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            $data = json_decode($decrypted, true);

            return $data['phoneNumber'] ?? null;
        } catch (\Exception $e) {
            Log::error('解密手机号失败', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
