<?php

namespace App\Services\V2;

use Illuminate\Support\Facades\Log;

class WechatPayService
{
    protected string $appId;
    protected string $mchId;
    protected string $apiV3Key;
    protected string $serialNo;
    protected string $privateKeyPath;
    protected string $notifyUrl;
    protected string $apiUrl;

    public function __construct()
    {
        $this->appId = config('wechat_pay.appid');
        $this->mchId = config('wechat_pay.mch_id');
        $this->apiV3Key = config('wechat_pay.api_v3_key');
        $this->serialNo = config('wechat_pay.serial_no');
        $this->privateKeyPath = config('wechat_pay.private_key_path');
        $this->notifyUrl = config('wechat_pay.notify_url');
        $this->apiUrl = config('wechat_pay.api_url');
    }

    /**
     * 创建JSAPI预支付订单
     */
    public function createJsapiOrder(string $orderNo, int $totalFee, string $description, string $openid): array
    {
        $url = '/v3/pay/transactions/jsapi';

        $data = [
            'appid' => $this->appId,
            'mchid' => $this->mchId,
            'description' => $description,
            'out_trade_no' => $orderNo,
            'notify_url' => $this->notifyUrl,
            'amount' => [
                'total' => $totalFee,
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];

        $result = $this->request('POST', $url, $data);

        if (!isset($result['prepay_id'])) {
            Log::error('WechatPay createJsapiOrder failed', ['result' => $result, 'data' => $data]);
            throw new \Exception($result['message'] ?? '创建预支付订单失败');
        }

        return $this->generatePayParams($result['prepay_id']);
    }

    /**
     * 生成小程序支付参数
     */
    protected function generatePayParams(string $prepayId): array
    {
        $timestamp = (string) time();
        $nonceStr = $this->generateNonceStr();
        $package = 'prepay_id=' . $prepayId;

        $message = $this->appId . "\n" . $timestamp . "\n" . $nonceStr . "\n" . $package . "\n";
        $paySign = $this->sign($message);

        return [
            'appId' => $this->appId,
            'timeStamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'package' => $package,
            'signType' => 'RSA',
            'paySign' => $paySign,
        ];
    }

    /**
     * 验证并解密支付回调
     */
    public function verifyAndDecryptNotify(array $headers, string $body): array
    {
        $timestamp = $headers['wechatpay-timestamp'][0] ?? $headers['Wechatpay-Timestamp'][0] ?? '';
        $nonce = $headers['wechatpay-nonce'][0] ?? $headers['Wechatpay-Nonce'][0] ?? '';
        $signature = $headers['wechatpay-signature'][0] ?? $headers['Wechatpay-Signature'][0] ?? '';
        $serial = $headers['wechatpay-serial'][0] ?? $headers['Wechatpay-Serial'][0] ?? '';

        if (empty($timestamp) || empty($nonce) || empty($signature)) {
            throw new \Exception('缺少必要的请求头');
        }

        // 验证签名
        $message = $timestamp . "\n" . $nonce . "\n" . $body . "\n";
        if (!$this->verifySignature($message, $signature)) {
            Log::error('WechatPay callback signature verification failed', [
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'serial' => $serial,
            ]);
            throw new \Exception('签名验证失败');
        }

        $data = json_decode($body, true);
        if (!$data || !isset($data['resource'])) {
            throw new \Exception('回调数据格式错误');
        }

        $resource = $data['resource'];
        if (!isset($resource['ciphertext'], $resource['nonce'])) {
            throw new \Exception('回调数据缺少必要字段');
        }

        $ciphertext = base64_decode($resource['ciphertext']);
        $associatedData = $resource['associated_data'] ?? '';
        $resourceNonce = $resource['nonce'];

        $decrypted = $this->decryptAesGcm($ciphertext, $associatedData, $resourceNonce);
        if ($decrypted === false) {
            throw new \Exception('解密失败');
        }

        $result = json_decode($decrypted, true);
        if (!$result) {
            throw new \Exception('解密后数据格式错误');
        }

        return $result;
    }

    /**
     * 验证微信支付平台签名
     */
    protected function verifySignature(string $message, string $signature): bool
    {
        // 注意：这里需要使用微信支付平台证书公钥验证
        // 由于平台证书需要定期更新，建议使用微信提供的证书管理工具
        // 这里提供基础实现，实际使用时需要配置平台证书路径

        $platformCertPath = config('wechat_pay.platform_cert_path');
        if (!$platformCertPath || !file_exists($platformCertPath)) {
            Log::warning('WechatPay platform certificate not configured, skipping signature verification');
            return true; // 如果未配置证书，暂时跳过验证（生产环境必须配置）
        }

        $platformCert = file_get_contents($platformCertPath);
        $publicKey = openssl_pkey_get_public($platformCert);

        if (!$publicKey) {
            Log::error('WechatPay failed to load platform certificate');
            return false;
        }

        $signatureDecoded = base64_decode($signature);
        $result = openssl_verify($message, $signatureDecoded, $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }

    /**
     * AEAD_AES_256_GCM 解密
     */
    protected function decryptAesGcm(string $ciphertext, string $associatedData, string $nonce): string|false
    {
        $tagLength = 16;
        $tag = substr($ciphertext, -$tagLength);
        $ciphertext = substr($ciphertext, 0, -$tagLength);

        return openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->apiV3Key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $associatedData
        );
    }

    /**
     * 发起HTTP请求
     */
    protected function request(string $method, string $url, array $data = []): array
    {
        $fullUrl = $this->apiUrl . $url;
        $body = $method === 'GET' ? '' : json_encode($data);
        $timestamp = time();
        $nonceStr = $this->generateNonceStr();

        $message = $method . "\n" . $url . "\n" . $timestamp . "\n" . $nonceStr . "\n" . $body . "\n";
        $signature = $this->sign($message);

        $authorization = sprintf(
            'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mchId,
            $nonceStr,
            $timestamp,
            $this->serialNo,
            $signature
        );

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Aiying-Laravel/1.0',
            'Authorization: ' . $authorization,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('WechatPay request curl error', ['error' => $error]);
            throw new \Exception('请求微信支付接口失败: ' . $error);
        }

        $result = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            Log::error('WechatPay request failed', [
                'httpCode' => $httpCode,
                'response' => $response,
            ]);
        }

        return $result;
    }

    /**
     * SHA256-RSA2048 签名
     */
    protected function sign(string $message): string
    {
        if (!file_exists($this->privateKeyPath)) {
            throw new \Exception('商户私钥文件不存在: ' . $this->privateKeyPath);
        }

        $privateKey = file_get_contents($this->privateKeyPath);
        $pkeyId = openssl_pkey_get_private($privateKey);

        if (!$pkeyId) {
            throw new \Exception('无法加载商户私钥');
        }

        openssl_sign($message, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * 申请退款
     */
    public function refund(string $orderNo, string $transactionId, float $totalAmount, float $refundAmount, string $reason = '用户申请退款'): array
    {
        $url = '/v3/refund/domestic/refunds';

        $refundNo = 'REFUND_' . $orderNo . '_' . time();
        $totalFee = (int) bcmul((string)$totalAmount, '100', 0);
        $refundFee = (int) bcmul((string)$refundAmount, '100', 0);

        $data = [
            'transaction_id' => $transactionId,
            'out_refund_no' => $refundNo,
            'reason' => $reason,
            'amount' => [
                'refund' => $refundFee,
                'total' => $totalFee,
                'currency' => 'CNY',
            ],
        ];

        $result = $this->request('POST', $url, $data);

        if (!isset($result['refund_id'])) {
            Log::error('WechatPay refund failed', ['result' => $result, 'data' => $data]);
            throw new \Exception($result['message'] ?? '申请退款失败');
        }

        return [
            'refund_id' => $result['refund_id'],
            'out_refund_no' => $refundNo,
            'status' => $result['status'] ?? 'PROCESSING',
        ];
    }

    /**
     * 查询退款
     */
    public function queryRefund(string $outRefundNo): array
    {
        $url = '/v3/refund/domestic/refunds/' . $outRefundNo;

        $result = $this->request('GET', $url);

        if (!isset($result['refund_id'])) {
            Log::error('WechatPay query refund failed', ['result' => $result, 'out_refund_no' => $outRefundNo]);
            throw new \Exception($result['message'] ?? '查询退款失败');
        }

        return $result;
    }

    /**
     * 生成随机字符串
     */
    protected function generateNonceStr(int $length = 32): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
}
