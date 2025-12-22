#!/bin/bash

# =================================================================
# 爱婴月子中心后端自动部署脚本
# =================================================================
# 使用说明：
# 1. 赋予执行权限: chmod +x deploy.sh
# 2. 运行部署: ./deploy.sh
# 3. 可选参数: ./deploy.sh "提交信息"
# =================================================================

# 服务器配置
SERVER_HOST="82.157.47.215"
SERVER_USER="root"
SERVER_PASSWORD="Aiying88"
SERVER_PATH="/www/wwwroot/aiying-backend"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 打印函数
print_header() {
    echo -e "\n${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║  $1${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}\n"
}

print_step() {
    echo -e "${CYAN}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# 检查必要工具
check_requirements() {
    print_step "检查必要工具..."

    if ! command -v git &> /dev/null; then
        print_error "Git未安装，请先安装Git"
        exit 1
    fi

    if ! command -v sshpass &> /dev/null; then
        print_warning "sshpass未安装，将尝试安装..."
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            if command -v brew &> /dev/null; then
                brew install hudochenkov/sshpass/sshpass
            else
                print_error "请先安装Homebrew或手动安装sshpass"
                exit 1
            fi
        elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
            # Linux
            sudo apt-get update && sudo apt-get install -y sshpass
        else
            print_error "不支持的操作系统，请手动安装sshpass"
            exit 1
        fi
    fi

    print_success "工具检查完成"
}

# 检查git状态
check_git_status() {
    print_step "检查Git状态..."

    # 检查是否在git仓库中
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_error "当前目录不是Git仓库"
        exit 1
    fi

    # 检查是否有未提交的更改
    if [[ -n $(git status -s) ]]; then
        print_warning "检测到未提交的更改"
        git status -s
        echo ""
    fi

    print_success "Git状态检查完成"
}

# 提交代码到本地仓库
commit_changes() {
    print_step "提交本地更改..."

    # 获取提交信息
    COMMIT_MSG="${1:-Update: $(date '+%Y-%m-%d %H:%M:%S')}"

    # 检查是否有更改
    if [[ -z $(git status -s) ]]; then
        print_warning "没有需要提交的更改"
        return 0
    fi

    # 添加所有更改
    git add .

    # 提交
    git commit -m "$COMMIT_MSG"

    if [ $? -eq 0 ]; then
        print_success "代码提交成功: $COMMIT_MSG"
    else
        print_error "代码提交失败"
        exit 1
    fi
}

# 推送到远程仓库
push_to_remote() {
    print_step "推送到远程仓库..."

    # 获取当前分支
    CURRENT_BRANCH=$(git branch --show-current)

    # 检查是否有远程仓库
    if ! git remote get-url origin &> /dev/null; then
        print_error "未配置远程仓库origin"
        echo -e "${YELLOW}提示: 使用 git remote add origin <仓库地址> 添加远程仓库${NC}"
        exit 1
    fi

    # 推送到远程
    git push origin "$CURRENT_BRANCH"

    if [ $? -eq 0 ]; then
        print_success "推送成功: origin/$CURRENT_BRANCH"
    else
        print_error "推送失败"
        exit 1
    fi
}

# 部署到服务器
deploy_to_server() {
    print_step "连接到服务器: $SERVER_USER@$SERVER_HOST"

    # 执行远程命令
    sshpass -p "$SERVER_PASSWORD" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
        # 设置颜色
        RED='\033[0;31m'
        GREEN='\033[0;32m'
        YELLOW='\033[1;33m'
        CYAN='\033[0;36m'
        NC='\033[0m'

        echo -e "${CYAN}[服务器] 开始部署...${NC}"

        # 进入项目目录
        cd /www/wwwroot/aiying-backend || exit 1

        # 保存当前分支
        CURRENT_BRANCH=$(git branch --show-current)
        echo -e "${CYAN}[服务器] 当前分支: $CURRENT_BRANCH${NC}"

        # 拉取最新代码
        echo -e "${CYAN}[服务器] 拉取最新代码...${NC}"
        git fetch origin
        git pull origin "$CURRENT_BRANCH"

        if [ $? -ne 0 ]; then
            echo -e "${RED}[服务器] Git pull失败${NC}"
            exit 1
        fi

        echo -e "${GREEN}[服务器] ✓ 代码更新成功${NC}"

        # 安装/更新依赖（如果composer.json有变化）
        if git diff HEAD@{1} HEAD --name-only | grep -q "composer.json\|composer.lock"; then
            echo -e "${CYAN}[服务器] 检测到依赖变化，更新Composer依赖...${NC}"
            composer install --no-dev --optimize-autoloader
            echo -e "${GREEN}[服务器] ✓ 依赖更新成功${NC}"
        fi

        # 清理缓存
        echo -e "${CYAN}[服务器] 清理缓存...${NC}"
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        echo -e "${GREEN}[服务器] ✓ 缓存清理成功${NC}"

        # 优化缓存
        echo -e "${CYAN}[服务器] 优化缓存...${NC}"
        php artisan config:cache
        php artisan route:cache
        echo -e "${GREEN}[服务器] ✓ 缓存优化成功${NC}"

        # 修正文件权限
        echo -e "${CYAN}[服务器] 修正文件权限...${NC}"
        chown -R www:www /www/wwwroot/aiying-backend
        chmod -R 755 /www/wwwroot/aiying-backend
        chmod -R 775 /www/wwwroot/aiying-backend/storage
        chmod -R 775 /www/wwwroot/aiying-backend/bootstrap/cache
        echo -e "${GREEN}[服务器] ✓ 权限设置成功${NC}"

        # 获取最新提交信息
        LATEST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%cr by %an)")
        echo -e "${GREEN}[服务器] ✓ 部署完成${NC}"
        echo -e "${CYAN}[服务器] 最新提交: $LATEST_COMMIT${NC}"
ENDSSH

    if [ $? -eq 0 ]; then
        print_success "服务器部署成功"
    else
        print_error "服务器部署失败"
        exit 1
    fi
}

# 主函数
main() {
    # 打印标题
    echo -e "${GREEN}"
    echo "╔═══════════════════════════════════════════════════════════╗"
    echo "║         爱婴月子中心后端自动部署工具                     ║"
    echo "║    Aiying Postpartum Care Center Backend Deployment      ║"
    echo "╚═══════════════════════════════════════════════════════════╝"
    echo -e "${NC}"

    # 获取提交信息（如果提供）
    COMMIT_MESSAGE="$1"

    # 执行部署流程
    print_header "1. 准备工作"
    check_requirements
    check_git_status

    print_header "2. 本地代码提交"
    commit_changes "$COMMIT_MESSAGE"

    print_header "3. 推送到远程仓库"
    push_to_remote

    print_header "4. 服务器部署"
    deploy_to_server

    # 完成
    echo -e "\n${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║   🎉 部署完成！后端已成功更新！                         ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}\n"

    # 显示部署信息
    echo -e "${CYAN}部署信息:${NC}"
    echo -e "  服务器: ${YELLOW}$SERVER_HOST${NC}"
    echo -e "  项目路径: ${YELLOW}$SERVER_PATH${NC}"
    echo -e "  提交信息: ${YELLOW}${COMMIT_MESSAGE:-自动提交}${NC}"
    echo ""
}

# 运行主函数
main "$@"
