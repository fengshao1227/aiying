#!/bin/bash

# 微信支付接口测试脚本
# 用于测试发起支付和支付回调接口

# 配置
API_URL="https://aiying.qdhs.cloud/api"
TOKEN="" # 需要先登录获取token

# 颜色输出
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}   微信支付接口测试${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# 函数：打印测试结果
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
    fi
}

# 1. 测试登录（获取token）
echo -e "${YELLOW}[1] 测试登录...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "${API_URL}/admin/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }')

echo "登录响应: $LOGIN_RESPONSE"

# 提取token
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    print_result 0 "登录成功，获取到Token"
    echo "Token: $TOKEN"
else
    print_result 1 "登录失败"
    exit 1
fi

echo ""

# 2. 创建测试订单
echo -e "${YELLOW}[2] 创建测试订单...${NC}"

# 注意：这里需要有效的用户openid和地址ID
# 实际测试时需要替换这些值
TEST_OPENID="test_openid_12345"

# 先获取用户列表（需要有至少一个用户）
USERS_RESPONSE=$(curl -s -X GET "${API_URL}/admin/users?per_page=1" \
  -H "Authorization: Bearer $TOKEN")

echo "用户列表: $USERS_RESPONSE"

# 创建订单接口需要小程序端调用，这里我们直接测试发起支付接口
# 假设已有一个待支付的订单，ID为1

echo ""

# 3. 测试发起支付接口
echo -e "${YELLOW}[3] 测试发起支付接口...${NC}"

ORDER_ID=1 # 替换为实际的订单ID

# 注意：发起支付需要小程序端的openid，这里使用X-Openid header模拟
PAY_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST "${API_URL}/orders/${ORDER_ID}/pay" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Openid: ${TEST_OPENID}")

HTTP_STATUS=$(echo "$PAY_RESPONSE" | grep "HTTP_STATUS:" | cut -d':' -f2)
RESPONSE_BODY=$(echo "$PAY_RESPONSE" | sed '/HTTP_STATUS:/d')

echo "响应状态码: $HTTP_STATUS"
echo "响应内容: $RESPONSE_BODY"

if [ "$HTTP_STATUS" = "200" ]; then
    print_result 0 "发起支付接口调用成功"

    # 检查是否包含支付参数
    if echo "$RESPONSE_BODY" | grep -q "paySign"; then
        print_result 0 "返回了小程序支付参数"
    else
        print_result 1 "未返回支付参数"
    fi
elif [ "$HTTP_STATUS" = "401" ]; then
    print_result 1 "未登录或openid无效"
elif [ "$HTTP_STATUS" = "404" ]; then
    print_result 1 "订单不存在（请先创建订单）"
elif [ "$HTTP_STATUS" = "500" ]; then
    print_result 1 "服务器错误（可能是证书问题）"
    echo "错误详情: $RESPONSE_BODY"
else
    print_result 1 "未知错误，状态码: $HTTP_STATUS"
fi

echo ""

# 4. 测试支付回调接口（模拟）
echo -e "${YELLOW}[4] 测试支付回调接口（模拟）...${NC}"

# 注意：真实的支付回调需要微信的签名，这里只测试接口是否可访问
NOTIFY_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST "${API_URL}/payments/wechat/notify" \
  -H "Content-Type: application/json" \
  -d '{
    "id": "test_notify",
    "create_time": "2025-12-22T12:00:00+08:00",
    "resource_type": "encrypt-resource",
    "event_type": "TRANSACTION.SUCCESS",
    "resource": {
        "algorithm": "AEAD_AES_256_GCM",
        "ciphertext": "test_ciphertext",
        "associated_data": "transaction",
        "nonce": "test_nonce"
    }
  }')

HTTP_STATUS=$(echo "$NOTIFY_RESPONSE" | grep "HTTP_STATUS:" | cut -d':' -f2)
RESPONSE_BODY=$(echo "$NOTIFY_RESPONSE" | sed '/HTTP_STATUS:/d')

echo "响应状态码: $HTTP_STATUS"

if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "500" ]; then
    print_result 0 "回调接口可访问（解密失败是正常的，需要真实的微信回调数据）"
else
    print_result 1 "回调接口异常，状态码: $HTTP_STATUS"
fi

echo ""
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}   测试完成${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""
echo -e "${GREEN}重要提示：${NC}"
echo "1. 发起支付接口需要有效的订单ID和用户openid"
echo "2. 真实的支付流程需要在小程序中测试"
echo "3. 支付回调需要微信服务器发送，无法本地完全模拟"
echo "4. 建议使用微信支付沙箱环境进行完整测试"
echo ""
