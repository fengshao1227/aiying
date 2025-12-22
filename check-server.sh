#!/bin/bash

# =================================================================
# 服务器状态检查脚本
# =================================================================
# 使用说明：
# 1. 赋予执行权限: chmod +x check-server.sh
# 2. 检查服务器: ./check-server.sh
# =================================================================

SERVER_HOST="82.157.47.215"
SERVER_USER="root"
SERVER_PASSWORD="Aiying88"
SERVER_PATH="/www/wwwroot/aiying-backend"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║            服务器状态检查                                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}\n"

sshpass -p "$SERVER_PASSWORD" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
    GREEN='\033[0;32m'
    RED='\033[0;31m'
    YELLOW='\033[1;33m'
    CYAN='\033[0;36m'
    NC='\033[0m'

    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} 系统信息${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}主机名:${NC} $(hostname)"
    echo -e "${YELLOW}系统版本:${NC} $(cat /etc/os-release | grep PRETTY_NAME | cut -d'"' -f2)"
    echo -e "${YELLOW}内核版本:${NC} $(uname -r)"
    echo -e "${YELLOW}CPU:${NC} $(nproc) 核心"
    echo -e "${YELLOW}内存:${NC} $(free -h | grep Mem | awk '{print $2}')"
    echo -e "${YELLOW}磁盘:${NC} $(df -h / | tail -1 | awk '{print $2 " (已用 " $3 ", 剩余 " $4 ")"}')"

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} PHP环境${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if command -v php &> /dev/null; then
        echo -e "${GREEN}✓${NC} PHP版本: $(php -v | head -1 | awk '{print $2}')"
        echo -e "${YELLOW}PHP配置文件:${NC} $(php --ini | grep "Loaded Configuration" | cut -d: -f2 | xargs)"
        echo -e "${YELLOW}PHP扩展:${NC}"
        php -m | grep -E "pdo_mysql|mbstring|openssl|json|curl|fileinfo" | sed 's/^/  - /'
    else
        echo -e "${RED}✗${NC} PHP未安装"
    fi

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} Composer${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if command -v composer &> /dev/null; then
        echo -e "${GREEN}✓${NC} Composer版本: $(composer --version | awk '{print $3}')"
    else
        echo -e "${RED}✗${NC} Composer未安装"
    fi

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} MySQL数据库${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if command -v mysql &> /dev/null; then
        echo -e "${GREEN}✓${NC} MySQL已安装"
        MYSQL_VERSION=$(mysql --version | awk '{print $5}' | sed 's/,//')
        echo -e "${YELLOW}MySQL版本:${NC} $MYSQL_VERSION"

        # 测试数据库连接
        if mysql -h 82.157.47.215 -u root -pAiying@123456 -e "SELECT 1" &> /dev/null; then
            echo -e "${GREEN}✓${NC} 数据库连接正常"
            echo -e "${YELLOW}数据库列表:${NC}"
            mysql -h 82.157.47.215 -u root -pAiying@123456 -e "SHOW DATABASES" | grep -v "Database\|information_schema\|performance_schema\|mysql\|sys" | sed 's/^/  - /'
        else
            echo -e "${RED}✗${NC} 数据库连接失败"
        fi
    else
        echo -e "${RED}✗${NC} MySQL未安装"
    fi

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} Nginx Web服务器${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if command -v nginx &> /dev/null; then
        echo -e "${GREEN}✓${NC} Nginx版本: $(nginx -v 2>&1 | cut -d/ -f2)"

        if systemctl is-active --quiet nginx; then
            echo -e "${GREEN}✓${NC} Nginx运行状态: 运行中"
        else
            echo -e "${RED}✗${NC} Nginx运行状态: 未运行"
        fi

        echo -e "${YELLOW}配置文件:${NC} /etc/nginx/nginx.conf"
        echo -e "${YELLOW}站点配置:${NC}"
        ls /etc/nginx/conf.d/*.conf 2>/dev/null | sed 's/^/  - /' || echo "  无"
    else
        echo -e "${RED}✗${NC} Nginx未安装"
    fi

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} 项目状态${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if [ -d "/www/wwwroot/aiying-backend" ]; then
        echo -e "${GREEN}✓${NC} 项目目录: /www/wwwroot/aiying-backend"

        cd /www/wwwroot/aiying-backend

        # Git状态
        if [ -d ".git" ]; then
            echo -e "${GREEN}✓${NC} Git仓库已初始化"
            CURRENT_BRANCH=$(git branch --show-current 2>/dev/null)
            echo -e "${YELLOW}当前分支:${NC} $CURRENT_BRANCH"
            LATEST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%cr)" 2>/dev/null)
            echo -e "${YELLOW}最新提交:${NC} $LATEST_COMMIT"

            # 检查是否有未提交更改
            if [[ -n $(git status -s 2>/dev/null) ]]; then
                echo -e "${YELLOW}⚠${NC} 有未提交的更改"
            else
                echo -e "${GREEN}✓${NC} 工作区干净"
            fi
        else
            echo -e "${RED}✗${NC} Git仓库未初始化"
        fi

        # Laravel环境检查
        if [ -f ".env" ]; then
            echo -e "${GREEN}✓${NC} .env文件存在"
            APP_ENV=$(grep "^APP_ENV=" .env | cut -d= -f2)
            APP_DEBUG=$(grep "^APP_DEBUG=" .env | cut -d= -f2)
            APP_URL=$(grep "^APP_URL=" .env | cut -d= -f2)
            echo -e "${YELLOW}环境模式:${NC} $APP_ENV"
            echo -e "${YELLOW}调试模式:${NC} $APP_DEBUG"
            echo -e "${YELLOW}应用URL:${NC} $APP_URL"
        else
            echo -e "${RED}✗${NC} .env文件不存在"
        fi

        # 检查关键目录权限
        echo -e "${YELLOW}目录权限:${NC}"
        STORAGE_PERM=$(stat -c %a storage 2>/dev/null || stat -f %A storage 2>/dev/null)
        CACHE_PERM=$(stat -c %a bootstrap/cache 2>/dev/null || stat -f %A bootstrap/cache 2>/dev/null)
        echo -e "  - storage: $STORAGE_PERM"
        echo -e "  - bootstrap/cache: $CACHE_PERM"

        # 检查vendor
        if [ -d "vendor" ]; then
            echo -e "${GREEN}✓${NC} Composer依赖已安装"
        else
            echo -e "${RED}✗${NC} Composer依赖未安装"
        fi

        # 磁盘使用
        PROJECT_SIZE=$(du -sh . 2>/dev/null | awk '{print $1}')
        echo -e "${YELLOW}项目大小:${NC} $PROJECT_SIZE"

    else
        echo -e "${RED}✗${NC} 项目目录不存在: /www/wwwroot/aiying-backend"
    fi

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} 网络连接${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}监听端口:${NC}"
    ss -tlnp 2>/dev/null | grep -E ":(80|443|3306|9000)" | awk '{print $4}' | sed 's/^/  - /' || netstat -tlnp 2>/dev/null | grep -E ":(80|443|3306|9000)" | awk '{print $4}' | sed 's/^/  - /'

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} 系统负载${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}负载平均值:${NC} $(uptime | awk -F'load average:' '{print $2}')"
    echo -e "${YELLOW}运行时间:${NC} $(uptime -p)"
    echo -e "${YELLOW}内存使用:${NC}"
    free -h | grep -E "Mem|Swap" | awk '{printf "  %s: %s / %s (已用: %s)\n", $1, $3, $2, $3}'

    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN} 最近日志${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}"
    if [ -f "/www/wwwroot/aiying-backend/storage/logs/laravel.log" ]; then
        echo -e "${YELLOW}Laravel日志 (最近10行):${NC}"
        tail -10 /www/wwwroot/aiying-backend/storage/logs/laravel.log 2>/dev/null | sed 's/^/  /' || echo "  无日志"
    fi

    if [ -f "/var/log/nginx/aiying.error.log" ]; then
        echo -e "\n${YELLOW}Nginx错误日志 (最近5行):${NC}"
        tail -5 /var/log/nginx/aiying.error.log 2>/dev/null | sed 's/^/  /' || echo "  无错误"
    fi

    echo -e "\n${GREEN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}检查完成！${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}\n"
ENDSSH

echo -e "${GREEN}✓ 服务器检查完成${NC}\n"
