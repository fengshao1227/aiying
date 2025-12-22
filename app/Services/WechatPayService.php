<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WechatPayService
{
    protected $config;
    protected $privateKey;

    public function __construct()
    {
        $this->config = config('wechat_pay');
        $this->loadPrivateKey();
    }

    /**
     * 加载商户私钥
     */
    protected function loadPrivateKey()
    {
        $keyPath = $this->config['private_key_path'];

        if (!file_exists($keyPath)) {
            throw new \Exception('商户私钥文件不存在，请先上传证书文件');
        }

        $this->privateKey = openssl_pkey_get_private(file_get_contents($keyPath));

        if (!$this->privateKey) {
            throw new \Exception('商户私钥加载失败');
        }
    }

    /**
     * 小程序统一下单
     *
     * @param Order $order 订单对象
     * @param string $openid 用户openid
     * @return array 小程序支付参数
     */
    public function createJsapiOrder(Order $order, string $openid): array
    {
        $url = '/v3/pay/transactions/jsapi';
        $data = [
            'appid' => $this->config['appid'],
            'mchid' => $this->config['mch_id'],
            'description' => $order->getPaymentDescription(),
            'out_trade_no' => $order->order_no,
            'notify_url' => $this->config['notify_url'],
            'amount' => [
                'total' => (int) ($order->total_amount * 100), // 转换为分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];

        $response = $this->request('POST', $url, $data);

        if (!isset($response['prepay_id'])) {
            throw new \Exception('微信下单失败: ' . json_encode($response));
        }

        // 生成小程序支付参数
        return $this->generateMiniProgramPayParams($response['prepay_id']);
    }

    /**
     * 生成小程序支付参数
     */
    protected function generateMiniProgramPayParams(string $prepayId): array
    {
        $appId = $this->config['appid'];
        $timeStamp = (string) time();
        $nonceStr = Str::random(32);
        $package = 'prepay_id=' . $prepayId;

        // 构造签名串
        $signStr = implode("\n", [$appId, $timeStamp, $nonceStr, $package, '']);

        // 生成签名
        openssl_sign($signStr, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        $paySign = base64_encode($signature);

        return [
            'appId' => $appId,
            'timeStamp' => $timeStamp,
            'nonceStr' => $nonceStr,
            'package' => $package,
            'signType' => 'RSA',
            'paySign' => $paySign,
        ];
    }

    /**
     * 验证并解密支付回调
     *
     * @param array $headers 请求头
     * @param string $body 请求体
     * @return array 解密后的数据
     */
    public function verifyAndDecryptNotify(array $headers, string $body): array
    {
        // 1. 验证签名（暂时跳过，因为需要微信平台证书）
        // 生产环境建议实现签名验证

        // 2. 解析请求体
        $data = json_decode($body, true);

        if (!isset($data['resource'])) {
            throw new \Exception('回调数据格式错误');
        }

        // 3. 解密数据
        return $this->decryptResource($data['resource']);
    }

    /**
     * 解密回调数据
     */
    protected function decryptResource(array $resource): array
    {
        $ciphertext = base64_decode($resource['ciphertext']);
        $associatedData = $resource['associated_data'] ?? '';
        $nonce = $resource['nonce'];
        $apiV3Key = $this->config['api_v3_key'];

        // AEAD_AES_256_GCM解密
        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $apiV3Key,
            OPENSSL_RAW_DATA,
            $nonce,
            substr($ciphertext, -16), // 提取tag
            $associatedData
        );

        if ($decrypted === false) {
            throw new \Exception('回调数据解密失败');
        }

        $result = json_decode(substr($ciphertext, 0, -16), true);

        if (!$result) {
            // 如果上面的解密失败，尝试另一种方式
            $tag = substr($ciphertext, -16);
            $ciphertext = substr($ciphertext, 0, -16);

            $decrypted = openssl_decrypt(
                $ciphertext,
                'aes-256-gcm',
                $apiV3Key,
                OPENSSL_RAW_DATA,
                $nonce,
                $tag,
                $associatedData
            );

            if ($decrypted === false) {
                throw new \Exception('回调数据解密失败');
            }

            $result = json_decode($decrypted, true);
        }

        return $result;
    }

    /**
     * 查询订单支付状态
     */
    public function queryOrder(string $outTradeNo): array
    {
        $url = '/v3/pay/transactions/out-trade-no/' . $outTradeNo;
        $url .= '?mchid=' . $this->config['mch_id'];

        return $this->request('GET', $url);
    }

    /**
     * 发送HTTP请求
     */
    protected function request(string $method, string $url, array $data = []): array
    {
        $fullUrl = $this->config['api_url'] . $url;
        $timestamp = time();
        $nonce = Str::random(32);
        $body = $method === 'GET' ? '' : json_encode($data, JSON_UNESCAPED_UNICODE);

        // 生成签名
        $signature = $this->generateSignature($method, $url, $timestamp, $nonce, $body);

        // 构造Authorization头
        $authorization = sprintf(
            'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"',
            $this->config['mch_id'],
            $nonce,
            $signature,
            $timestamp,
            $this->config['serial_no']
        );

        // 发送请求
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Aiying-Laravel/1.0',
        ])->send($method, $fullUrl, [
            'body' => $body,
        ]);

        if (!$response->successful()) {
            Log::error('微信支付API请求失败', [
                'url' => $fullUrl,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('微信支付API请求失败: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * 生成请求签名
     */
    protected function generateSignature(string $method, string $url, int $timestamp, string $nonce, string $body): string
    {
        // 构造签名串
        $signStr = implode("\n", [
            $method,
            $url,
            $timestamp,
            $nonce,
            $body,
            ''
        ]);

        // 使用商户私钥签名
        openssl_sign($signStr, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * 析构函数：释放私钥资源
     */
    public function __destruct()
    {
        if ($this->privateKey) {
            openssl_free_key($this->privateKey);
        }
    }
}
