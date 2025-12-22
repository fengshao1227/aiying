#!/bin/bash

# =================================================================
# 代码回滚脚本
# =================================================================
# 使用说明：
# 1. 赋予执行权限: chmod +x rollback.sh
# 2. 回滚到上一个版本: ./rollback.sh
# 3. 回滚到指定版本: ./rollback.sh <commit-hash>
# =================================================================

SERVER_HOST="82.157.47.215"
SERVER_USER="root"
SERVER_PASSWORD="Aiying88"
SERVER_PATH="/www/wwwroot/aiying-backend"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

COMMIT_HASH="$1"

echo -e "${YELLOW}⚠️  代码回滚操作${NC}\n"

if [ -z "$COMMIT_HASH" ]; then
    echo -e "${CYAN}▶ 将回滚到上一个版本...${NC}"
    ROLLBACK_CMD="git reset --hard HEAD~1"
else
    echo -e "${CYAN}▶ 将回滚到版本: $COMMIT_HASH${NC}"
    ROLLBACK_CMD="git reset --hard $COMMIT_HASH"
fi

# 确认操作
echo -e "${RED}警告: 此操作将丢失服务器上的未提交更改！${NC}"
read -p "确定要继续吗? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo -e "${YELLOW}已取消回滚操作${NC}"
    exit 0
fi

echo -e "\n${CYAN}▶ 连接服务器并执行回滚...${NC}"

sshpass -p "$SERVER_PASSWORD" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" << ENDSSH
    cd $SERVER_PATH || exit 1

    echo "当前版本:"
    git log -1 --oneline

    echo -e "\n回滚中..."
    $ROLLBACK_CMD

    if [ \$? -eq 0 ]; then
        echo -e "\n回滚后版本:"
        git log -1 --oneline

        echo -e "\n清理缓存..."
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear

        echo -e "\n优化缓存..."
        php artisan config:cache
        php artisan route:cache

        echo -e "\n修正权限..."
        chown -R www:www $SERVER_PATH

        echo -e "\n回滚成功！"
    else
        echo -e "\n回滚失败！"
        exit 1
    fi
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "\n${GREEN}✓ 回滚操作完成${NC}\n"
else
    echo -e "\n${RED}✗ 回滚操作失败${NC}\n"
    exit 1
fi
