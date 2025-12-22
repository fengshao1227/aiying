<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 微信支付配置
    |--------------------------------------------------------------------------
    |
    | 微信支付APIV3配置信息
    |
    */

    // 小程序AppID
    'appid' => env('WECHAT_PAY_APPID', 'wxccf8804f6f48fb46'),

    // 商户号
    'mch_id' => env('WECHAT_PAY_MCH_ID', '1635595538'),

    // APIV3密钥（用于回调数据解密）
    'api_v3_key' => env('WECHAT_PAY_API_V3_KEY', 'kRc1pvCcaq9jK1qzWGST6lGfso9uiJrY'),

    // 商户证书序列号
    'serial_no' => env('WECHAT_PAY_SERIAL_NO', ''),

    // 商户私钥文件路径
    'private_key_path' => storage_path('wechat_pay/apiclient_key.pem'),

    // 商户证书文件路径
    'cert_path' => storage_path('wechat_pay/apiclient_cert.pem'),

    // 支付回调通知URL
    'notify_url' => env('APP_URL', 'https://aiying.qdhs.cloud') . '/api/payments/wechat/notify',

    // 微信支付API地址
    'api_url' => 'https://api.mch.weixin.qq.com',

    // 企业微信Webhook地址
    'work_wechat_webhook' => env('WECHAT_WORK_WEBHOOK', 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=8b9d2a7f-7f27-4471-adcf-ad09f1dccf0b'),
];
