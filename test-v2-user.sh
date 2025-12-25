#!/bin/bash

# V2 用户模块接口测试脚本
# 使用方法: ./test-v2-user.sh

BASE_URL="http://localhost:8000"
API_BASE="${BASE_URL}/v2/user"

echo "=========================================="
echo "V2 用户模块接口测试"
echo "=========================================="
echo ""

# 颜色定义
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 测试计数
TOTAL=0
PASSED=0
FAILED=0

# 测试函数
test_api() {
    local name=$1
    local method=$2
    local url=$3
    local data=$4
    local headers=$5
    local expected_code=$6

    TOTAL=$((TOTAL + 1))
    echo -e "${YELLOW}测试 ${TOTAL}: ${name}${NC}"
    echo "请求: ${method} ${url}"

    if [ -n "$data" ]; then
        echo "数据: ${data}"
    fi

    if [ -n "$headers" ]; then
        response=$(curl -s -w "\n%{http_code}" -X ${method} "${url}" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            ${headers} \
            ${data:+-d "$data"})
    else
        response=$(curl -s -w "\n%{http_code}" -X ${method} "${url}" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            ${data:+-d "$data"})
    fi

    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    echo "响应码: ${http_code}"
    echo "响应体: ${body}" | jq '.' 2>/dev/null || echo "${body}"

    if [ "$http_code" = "$expected_code" ]; then
        echo -e "${GREEN}✓ 通过${NC}"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗ 失败 (期望: ${expected_code}, 实际: ${http_code})${NC}"
        FAILED=$((FAILED + 1))
    fi

    echo ""
    echo "------------------------------------------"
    echo ""
}

echo "开始测试..."
echo ""

# ==========================================
# 测试 1: 微信登录 - 缺少 code 参数
# ==========================================
test_api \
    "微信登录 - 缺少参数" \
    "POST" \
    "${API_BASE}/login" \
    '{}' \
    "" \
    "400"

# ==========================================
# 测试 2: 微信登录 - 无效的 code
# ==========================================
test_api \
    "微信登录 - 无效code" \
    "POST" \
    "${API_BASE}/login" \
    '{"code":"invalid_code_123"}' \
    "" \
    "500"

# ==========================================
# 测试 3: 获取用户信息 - 未登录
# ==========================================
test_api \
    "获取用户信息 - 未登录" \
    "GET" \
    "${API_BASE}/profile" \
    "" \
    "" \
    "401"

# ==========================================
# 测试 4: 获取用户信息 - 无效 openid
# ==========================================
test_api \
    "获取用户信息 - 无效openid" \
    "GET" \
    "${API_BASE}/profile" \
    "" \
    "-H 'X-Openid: invalid_openid_123'" \
    "401"

# ==========================================
# 测试 5: 绑定客户 - 未登录
# ==========================================
test_api \
    "绑定客户 - 未登录" \
    "POST" \
    "${API_BASE}/bindCustomer" \
    '{"phone":"13800138000"}' \
    "" \
    "401"

# ==========================================
# 测试 6: 绑定客户 - 无效手机号格式
# ==========================================
test_api \
    "绑定客户 - 无效手机号" \
    "POST" \
    "${API_BASE}/bindCustomer" \
    '{"phone":"123"}' \
    "-H 'X-Openid: test_openid_123'" \
    "400"

# ==========================================
# 测试 7: 解绑客户 - 未登录
# ==========================================
test_api \
    "解绑客户 - 未登录" \
    "POST" \
    "${API_BASE}/unbindCustomer" \
    '{}' \
    "" \
    "401"

# ==========================================
# 测试总结
# ==========================================
echo "=========================================="
echo "测试总结"
echo "=========================================="
echo "总计: ${TOTAL}"
echo -e "${GREEN}通过: ${PASSED}${NC}"
echo -e "${RED}失败: ${FAILED}${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}所有测试通过！${NC}"
    exit 0
else
    echo -e "${RED}有 ${FAILED} 个测试失败${NC}"
    exit 1
fi
