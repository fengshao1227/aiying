#!/bin/bash

# =================================================================
# 爱婴月子中心后台管理API测试脚本
# =================================================================
# 使用说明：
# 1. 确保Laravel服务已启动: php artisan serve
# 2. 赋予执行权限: chmod +x test-api.sh
# 3. 运行测试: ./test-api.sh
# =================================================================

# 配置
API_BASE="http://localhost:8000/api"
TOKEN=""
ADMIN_ID=""

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 打印函数
print_section() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_test() {
    echo -e "${YELLOW}[测试] $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_response() {
    echo -e "${NC}响应: $1${NC}\n"
}

# 测试计数器
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# 执行测试并验证结果
run_test() {
    local test_name="$1"
    local method="$2"
    local endpoint="$3"
    local data="$4"
    local auth_header="$5"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    print_test "$test_name"

    if [ "$method" = "GET" ]; then
        response=$(curl -s -X GET "$API_BASE$endpoint" \
            -H "Accept: application/json" \
            -H "$auth_header")
    elif [ "$method" = "POST" ]; then
        response=$(curl -s -X POST "$API_BASE$endpoint" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -H "$auth_header" \
            -d "$data")
    elif [ "$method" = "PUT" ]; then
        response=$(curl -s -X PUT "$API_BASE$endpoint" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -H "$auth_header" \
            -d "$data")
    elif [ "$method" = "DELETE" ]; then
        response=$(curl -s -X DELETE "$API_BASE$endpoint" \
            -H "Accept: application/json" \
            -H "$auth_header")
    fi

    # 检查响应
    code=$(echo "$response" | grep -o '"code":[0-9]*' | head -1 | cut -d':' -f2)
    message=$(echo "$response" | grep -o '"message":"[^"]*"' | head -1 | cut -d':' -f2 | tr -d '"')

    if [ ! -z "$code" ] && [ "$code" -lt 400 ]; then
        print_success "成功 - $message"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        print_error "失败 - $message"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi

    print_response "$response"

    echo "$response"
}

# =================================================================
# 开始测试
# =================================================================

echo -e "${GREEN}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║    爱婴月子中心后台管理API测试工具                       ║"
echo "║    API Test Suite for Aiying Postpartum Care Center      ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# =================================================================
# 1. 认证系统测试
# =================================================================
print_section "1. 认证系统测试"

# 1.1 管理员登录
print_test "1.1 管理员登录"
login_response=$(run_test "管理员登录" "POST" "/admin/login" \
    '{"username":"admin","password":"admin123"}' \
    "")

# 提取Token
TOKEN=$(echo "$login_response" | grep -o '"token":"[^"]*"' | head -1 | cut -d':' -f2 | tr -d '"')
ADMIN_ID=$(echo "$login_response" | grep -o '"admin_id":[0-9]*' | head -1 | cut -d':' -f2)

if [ -z "$TOKEN" ]; then
    print_error "登录失败，无法获取Token，后续测试将跳过"
    exit 1
fi

print_success "Token已获取: ${TOKEN:0:20}..."
echo ""

# 1.2 获取管理员信息
run_test "1.2 获取管理员信息" "GET" "/admin/info" "" "Authorization: Bearer $TOKEN" > /dev/null

# 1.3 测试未认证访问
print_test "1.3 测试未认证访问（应该失败）"
response=$(curl -s -X GET "$API_BASE/admin/info" \
    -H "Accept: application/json")
code=$(echo "$response" | grep -o '"code":[0-9]*' | head -1 | cut -d':' -f2)
if [ "$code" = "401" ]; then
    print_success "未认证访问正确返回401"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    print_error "未认证访问应返回401"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo ""

# =================================================================
# 2. 客户管理测试
# =================================================================
print_section "2. 客户管理测试"

# 2.1 创建客户
customer_response=$(run_test "2.1 创建客户" "POST" "/admin/customers" \
    '{
        "customer_name": "测试客户",
        "phone": "13800138000",
        "package_name": "豪华套餐",
        "baby_name": "小宝宝",
        "mother_birthday": "1990-05-20",
        "baby_birthday": "2025-01-20",
        "nanny_name": "李月嫂",
        "due_date": "2025-01-18",
        "address": "北京市朝阳区测试地址",
        "check_in_date": "2025-01-15 14:00:00",
        "remarks": "测试客户数据"
    }' \
    "Authorization: Bearer $TOKEN")

# 提取客户ID
CUSTOMER_ID=$(echo "$customer_response" | grep -o '"customer_id":[0-9]*' | head -1 | cut -d':' -f2)

if [ ! -z "$CUSTOMER_ID" ]; then
    print_success "客户ID已获取: $CUSTOMER_ID"
    echo ""
fi

# 2.2 获取客户列表
run_test "2.2 获取客户列表（带分页）" "GET" "/admin/customers?per_page=10&page=1" "" "Authorization: Bearer $TOKEN" > /dev/null

# 2.3 搜索客户
run_test "2.3 搜索客户（关键字）" "GET" "/admin/customers?keyword=测试" "" "Authorization: Bearer $TOKEN" > /dev/null

# 2.4 获取客户详情
if [ ! -z "$CUSTOMER_ID" ]; then
    run_test "2.4 获取客户详情" "GET" "/admin/customers/$CUSTOMER_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 2.5 更新客户
if [ ! -z "$CUSTOMER_ID" ]; then
    run_test "2.5 更新客户信息" "PUT" "/admin/customers/$CUSTOMER_ID" \
        '{"remarks": "更新后的备注信息"}' \
        "Authorization: Bearer $TOKEN" > /dev/null
fi

# =================================================================
# 3. 房间管理测试
# =================================================================
print_section "3. 房间管理测试"

# 3.1 创建房间
room_response=$(run_test "3.1 创建房间" "POST" "/admin/rooms" \
    '{
        "room_name": "测试101室",
        "floor": 1,
        "room_type": "豪华套房",
        "color_code": "#FF5733",
        "ac_group_id": 1,
        "display_order": 1
    }' \
    "Authorization: Bearer $TOKEN")

# 提取房间ID
ROOM_ID=$(echo "$room_response" | grep -o '"room_id":[0-9]*' | head -1 | cut -d':' -f2)

if [ ! -z "$ROOM_ID" ]; then
    print_success "房间ID已获取: $ROOM_ID"
    echo ""
fi

# 3.2 获取房间列表
run_test "3.2 获取房间列表" "GET" "/admin/rooms?paginate=true&per_page=10" "" "Authorization: Bearer $TOKEN" > /dev/null

# 3.3 筛选房间（楼层）
run_test "3.3 筛选房间（一楼）" "GET" "/admin/rooms?floor=1" "" "Authorization: Bearer $TOKEN" > /dev/null

# 3.4 获取房间详情
if [ ! -z "$ROOM_ID" ]; then
    run_test "3.4 获取房间详情" "GET" "/admin/rooms/$ROOM_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 3.5 更新房间
if [ ! -z "$ROOM_ID" ]; then
    run_test "3.5 更新房间信息" "PUT" "/admin/rooms/$ROOM_ID" \
        '{"room_type": "总统套房", "display_order": 2}' \
        "Authorization: Bearer $TOKEN" > /dev/null
fi

# =================================================================
# 4. 房态管理测试
# =================================================================
print_section "4. 房态管理测试"

# 4.1 办理入住（跨月）
if [ ! -z "$ROOM_ID" ] && [ ! -z "$CUSTOMER_ID" ]; then
    checkin_response=$(run_test "4.1 办理入住（跨月测试）" "POST" "/admin/room-status/check-in" \
        "{
            \"room_id\": $ROOM_ID,
            \"customer_id\": $CUSTOMER_ID,
            \"check_in_date\": \"2025-01-15 14:00:00\",
            \"check_out_date\": \"2025-03-10 12:00:00\"
        }" \
        "Authorization: Bearer $TOKEN")
fi

# 4.2 获取房态列表（当月）
run_test "4.2 获取2025年1月房态" "GET" "/admin/room-status?record_month=2025-01" "" "Authorization: Bearer $TOKEN" > /dev/null

# 4.3 获取房态列表（跨月）
run_test "4.3 获取2025年2月房态" "GET" "/admin/room-status?record_month=2025-02" "" "Authorization: Bearer $TOKEN" > /dev/null

# 4.4 办理退房
if [ ! -z "$ROOM_ID" ] && [ ! -z "$CUSTOMER_ID" ]; then
    run_test "4.4 办理退房" "POST" "/admin/room-status/check-out" \
        "{
            \"room_id\": $ROOM_ID,
            \"customer_id\": $CUSTOMER_ID
        }" \
        "Authorization: Bearer $TOKEN" > /dev/null
fi

# =================================================================
# 5. 评分卡管理测试
# =================================================================
print_section "5. 评分卡管理测试"

# 5.1 创建评分卡记录
if [ ! -z "$CUSTOMER_ID" ]; then
    scorecard_response=$(run_test "5.1 创建评分卡记录" "POST" "/admin/score-cards" \
        "{
            \"customer_id\": $CUSTOMER_ID,
            \"card_number\": 1,
            \"record_date\": \"2025-01-16\",
            \"score_data\": {
                \"health\": 90,
                \"service\": 95,
                \"food\": 88,
                \"environment\": 92
            }
        }" \
        "Authorization: Bearer $TOKEN")

    # 提取评分卡ID
    SCORECARD_ID=$(echo "$scorecard_response" | grep -o '"score_card_record_id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ ! -z "$SCORECARD_ID" ]; then
        print_success "评分卡ID已获取: $SCORECARD_ID"
        echo ""
    fi
fi

# 5.2 获取评分卡列表
run_test "5.2 获取评分卡列表" "GET" "/admin/score-cards?per_page=10" "" "Authorization: Bearer $TOKEN" > /dev/null

# 5.3 筛选评分卡（按客户）
if [ ! -z "$CUSTOMER_ID" ]; then
    run_test "5.3 筛选评分卡（按客户）" "GET" "/admin/score-cards?customer_id=$CUSTOMER_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 5.4 筛选评分卡（日期范围）
run_test "5.4 筛选评分卡（日期范围）" "GET" "/admin/score-cards?start_date=2025-01-01&end_date=2025-01-31" "" "Authorization: Bearer $TOKEN" > /dev/null

# 5.5 获取评分卡详情
if [ ! -z "$SCORECARD_ID" ]; then
    run_test "5.5 获取评分卡详情" "GET" "/admin/score-cards/$SCORECARD_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 5.6 更新评分卡
if [ ! -z "$SCORECARD_ID" ]; then
    run_test "5.6 更新评分卡" "PUT" "/admin/score-cards/$SCORECARD_ID" \
        '{
            "card_number": 2,
            "score_data": {
                "health": 95,
                "service": 98,
                "food": 90,
                "environment": 93
            }
        }' \
        "Authorization: Bearer $TOKEN" > /dev/null
fi

# =================================================================
# 6. 数据清理测试
# =================================================================
print_section "6. 数据清理测试"

# 6.1 删除评分卡
if [ ! -z "$SCORECARD_ID" ]; then
    run_test "6.1 删除评分卡记录" "DELETE" "/admin/score-cards/$SCORECARD_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 6.2 删除客户
if [ ! -z "$CUSTOMER_ID" ]; then
    run_test "6.2 删除客户" "DELETE" "/admin/customers/$CUSTOMER_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# 6.3 删除房间
if [ ! -z "$ROOM_ID" ]; then
    run_test "6.3 删除房间" "DELETE" "/admin/rooms/$ROOM_ID" "" "Authorization: Bearer $TOKEN" > /dev/null
fi

# =================================================================
# 7. 认证系统测试（续）
# =================================================================
print_section "7. 认证系统测试（续）"

# 7.1 修改密码
run_test "7.1 修改管理员密码" "POST" "/admin/change-password" \
    '{
        "old_password": "admin123",
        "new_password": "newpassword123",
        "new_password_confirmation": "newpassword123"
    }' \
    "Authorization: Bearer $TOKEN" > /dev/null

# 7.2 用新密码登录
new_login_response=$(run_test "7.2 使用新密码登录" "POST" "/admin/login" \
    '{"username":"admin","password":"newpassword123"}' \
    "")

NEW_TOKEN=$(echo "$new_login_response" | grep -o '"token":"[^"]*"' | head -1 | cut -d':' -f2 | tr -d '"')

# 7.3 改回原密码
if [ ! -z "$NEW_TOKEN" ]; then
    run_test "7.3 恢复原密码" "POST" "/admin/change-password" \
        '{
            "old_password": "newpassword123",
            "new_password": "admin123",
            "new_password_confirmation": "admin123"
        }' \
        "Authorization: Bearer $NEW_TOKEN" > /dev/null
fi

# 7.4 退出登录
run_test "7.4 退出登录" "POST" "/admin/logout" "" "Authorization: Bearer $TOKEN" > /dev/null

# =================================================================
# 测试总结
# =================================================================
print_section "测试总结"

echo -e "${BLUE}总测试数: $TOTAL_TESTS${NC}"
echo -e "${GREEN}通过: $PASSED_TESTS${NC}"
echo -e "${RED}失败: $FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "\n${GREEN}╔═══════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║   🎉 所有测试通过！API运行正常！     ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════╝${NC}\n"
    exit 0
else
    echo -e "\n${RED}╔═══════════════════════════════════════╗${NC}"
    echo -e "${RED}║   ⚠️  部分测试失败，请检查日志！    ║${NC}"
    echo -e "${RED}╚═══════════════════════════════════════╝${NC}\n"
    exit 1
fi
