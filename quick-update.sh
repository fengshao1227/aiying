#!/bin/bash

# =================================================================
# 快速更新脚本（适用于紧急修复和小改动）
# =================================================================
# 使用说明：
# 1. 赋予执行权限: chmod +x quick-update.sh
# 2. 快速部署: ./quick-update.sh "修复bug"
# 3. 无提交信息: ./quick-update.sh
# =================================================================

SERVER_HOST="82.157.47.215"
SERVER_USER="root"
SERVER_PASSWORD="Aiying88"
SERVER_PATH="/www/wwwroot/aiying-backend"

GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${CYAN}⚡ 快速部署模式${NC}\n"

# 1. 提交
COMMIT_MSG="${1:-Quick update: $(date '+%Y-%m-%d %H:%M:%S')}"
echo -e "${CYAN}▶ 提交更改...${NC}"
git add . && git commit -m "$COMMIT_MSG"

# 2. 推送
echo -e "${CYAN}▶ 推送代码...${NC}"
BRANCH=$(git branch --show-current)
git push origin "$BRANCH"

# 3. 服务器更新
echo -e "${CYAN}▶ 服务器更新...${NC}"
sshpass -p "$SERVER_PASSWORD" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" \
    "cd $SERVER_PATH && git pull && php artisan config:cache && php artisan route:cache && chown -R www:www $SERVER_PATH"

if [ $? -eq 0 ]; then
    echo -e "\n${GREEN}✓ 快速部署完成！${NC}\n"
else
    echo -e "\n${RED}✗ 部署失败${NC}\n"
    exit 1
fi
