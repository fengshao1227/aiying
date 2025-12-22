# 爱婴月子中心后台管理API开发总结

## 项目概述

本项目为爱婴月子中心开发了完整的后台管理API系统，采用Laravel 12框架，使用RESTful API设计模式。系统包含客户管理、房间管理、房态管理和评分卡管理四大核心模块。

### 技术栈
- **框架**: Laravel 12 (PHP 8.3+)
- **认证**: Laravel Sanctum (Token-based)
- **数据库**: MySQL 8.0
- **API风格**: RESTful
- **响应格式**: JSON

### 数据库设计

#### 1. 管理员表 (admins)
```sql
CREATE TABLE `admins` (
  `admin_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 默认管理员账号
-- 用户名: admin
-- 密码: admin123
```

#### 2. 客户表 (customers)
```sql
CREATE TABLE `customers` (
  `customer_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `customer_name` varchar(50) NOT NULL COMMENT '客户姓名',
  `phone` varchar(20) NOT NULL COMMENT '联系电话',
  `package_name` varchar(100) DEFAULT NULL COMMENT '套餐名称',
  `baby_name` varchar(50) DEFAULT NULL COMMENT '宝宝姓名',
  `mother_birthday` date DEFAULT NULL COMMENT '宝妈生日',
  `baby_birthday` date DEFAULT NULL COMMENT '宝宝生日',
  `nanny_name` varchar(50) DEFAULT NULL COMMENT '月嫂姓名',
  `due_date` date DEFAULT NULL COMMENT '预产期',
  `address` text DEFAULT NULL COMMENT '家庭住址',
  `check_in_date` datetime DEFAULT NULL COMMENT '入住时间',
  `check_out_date` datetime DEFAULT NULL COMMENT '出所时间',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3. 房间表 (rooms)
```sql
CREATE TABLE `rooms` (
  `room_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '房间ID',
  `room_name` varchar(50) NOT NULL COMMENT '房间名称',
  `floor` tinyint NOT NULL COMMENT '楼层：1=一楼，2=二楼',
  `room_type` varchar(50) DEFAULT NULL COMMENT '房型',
  `color_code` varchar(7) DEFAULT NULL COMMENT '颜色代码（用于前端展示）',
  `ac_group_id` int DEFAULT NULL COMMENT '空调分组ID',
  `display_order` int DEFAULT '0' COMMENT '显示顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4. 房态表 (room_status)
```sql
CREATE TABLE `room_status` (
  `room_status_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '房态ID',
  `room_id` bigint unsigned NOT NULL COMMENT '房间ID',
  `customer_id` bigint unsigned DEFAULT NULL COMMENT '客户ID',
  `check_in_date` datetime DEFAULT NULL COMMENT '入住日期',
  `check_out_date` datetime DEFAULT NULL COMMENT '退房日期',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0=空闲，1=已入住，2=维修',
  `record_month` varchar(7) NOT NULL COMMENT '记录月份（格式：YYYY-MM）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_status_id`),
  KEY `idx_room_month` (`room_id`, `record_month`),
  KEY `idx_customer` (`customer_id`),
  CONSTRAINT `fk_room_status_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_room_status_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5. 评分卡记录表 (score_card_records)
```sql
CREATE TABLE `score_card_records` (
  `score_card_record_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '评分卡记录ID',
  `customer_id` bigint unsigned NOT NULL COMMENT '客户ID',
  `card_number` int NOT NULL COMMENT '评分卡编号',
  `record_date` date NOT NULL COMMENT '记录日期',
  `score_data` json DEFAULT NULL COMMENT '评分数据（JSON格式）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`score_card_record_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_date` (`record_date`),
  CONSTRAINT `fk_score_card_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 认证系统

### 认证流程
系统使用Laravel Sanctum实现Token-based认证：

1. 管理员登录获取Token
2. 后续请求携带Token访问受保护接口
3. 中间件验证Token和管理员状态

### 认证相关API

#### 1. 管理员登录
```http
POST /api/admin/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "登录成功",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxx",
    "admin": {
      "admin_id": 1,
      "username": "admin",
      "real_name": "系统管理员",
      "status": 1,
      "last_login_at": "2025-12-22 10:30:00",
      "last_login_ip": "127.0.0.1"
    }
  }
}
```

#### 2. 获取管理员信息
```http
GET /api/admin/info
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "admin_id": 1,
    "username": "admin",
    "real_name": "系统管理员",
    "status": 1
  }
}
```

#### 3. 退出登录
```http
POST /api/admin/logout
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "退出成功",
  "data": null
}
```

#### 4. 修改密码
```http
POST /api/admin/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
  "old_password": "admin123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "密码修改成功",
  "data": null
}
```

---

## 客户管理API

### 1. 获取客户列表（分页+搜索）
```http
GET /api/admin/customers?keyword=张三&package_name=豪华套餐&is_checked_in=1&sort_by=created_at&sort_order=desc&per_page=15&page=1
Authorization: Bearer {token}
```

**查询参数**:
- `keyword`: 关键字搜索（客户姓名、电话）
- `package_name`: 套餐名称筛选
- `is_checked_in`: 入住状态（1=已入住，0=未入住）
- `sort_by`: 排序字段（默认: created_at）
- `sort_order`: 排序方向（asc/desc，默认: desc）
- `per_page`: 每页数量（默认: 15）
- `page`: 页码

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "customer_id": 1,
        "customer_name": "张三",
        "phone": "13800138000",
        "package_name": "豪华套餐",
        "baby_name": "宝宝",
        "check_in_date": "2025-01-15 14:00:00",
        "check_out_date": null,
        "created_at": "2025-01-10 10:00:00"
      }
    ],
    "total": 50,
    "per_page": 15,
    "last_page": 4
  }
}
```

### 2. 获取客户详情
```http
GET /api/admin/customers/1
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "customer_id": 1,
    "customer_name": "张三",
    "phone": "13800138000",
    "package_name": "豪华套餐",
    "baby_name": "宝宝",
    "mother_birthday": "1990-05-20",
    "baby_birthday": "2025-01-20",
    "nanny_name": "李月嫂",
    "due_date": "2025-01-18",
    "address": "北京市朝阳区xxx",
    "check_in_date": "2025-01-15 14:00:00",
    "check_out_date": null,
    "remarks": "VIP客户",
    "room_statuses": [
      {
        "room_status_id": 1,
        "room_id": 101,
        "status": 1,
        "record_month": "2025-01",
        "room": {
          "room_id": 101,
          "room_name": "101室",
          "floor": 1
        }
      }
    ],
    "score_card_records": [
      {
        "score_card_record_id": 1,
        "card_number": 1,
        "record_date": "2025-01-16",
        "score_data": {
          "health": 90,
          "service": 95
        }
      }
    ]
  }
}
```

### 3. 创建客户
```http
POST /api/admin/customers
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_name": "张三",
  "phone": "13800138000",
  "package_name": "豪华套餐",
  "baby_name": "宝宝",
  "mother_birthday": "1990-05-20",
  "baby_birthday": "2025-01-20",
  "nanny_name": "李月嫂",
  "due_date": "2025-01-18",
  "address": "北京市朝阳区xxx",
  "check_in_date": "2025-01-15 14:00:00",
  "remarks": "VIP客户"
}
```

**响应示例**:
```json
{
  "code": 201,
  "message": "创建成功",
  "data": {
    "customer_id": 1,
    "customer_name": "张三",
    "phone": "13800138000",
    ...
  }
}
```

### 4. 更新客户
```http
PUT /api/admin/customers/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_name": "张三",
  "phone": "13800138001",
  "remarks": "更新备注"
}
```

### 5. 删除客户
```http
DELETE /api/admin/customers/1
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "删除成功",
  "data": null
}
```

---

## 房间管理API

### 1. 获取房间列表
```http
GET /api/admin/rooms?floor=1&room_type=豪华套房&keyword=101&sort_by=display_order&sort_order=asc&paginate=true&per_page=15
Authorization: Bearer {token}
```

**查询参数**:
- `floor`: 楼层筛选（1=一楼，2=二楼）
- `room_type`: 房型筛选
- `keyword`: 关键字搜索（房间名称）
- `sort_by`: 排序字段（默认: display_order）
- `sort_order`: 排序方向（asc/desc，默认: asc）
- `paginate`: 是否分页（true/false，默认: true）
- `per_page`: 每页数量（默认: 15）

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "room_id": 1,
        "room_name": "101室",
        "floor": 1,
        "room_type": "豪华套房",
        "color_code": "#FF5733",
        "ac_group_id": 1,
        "display_order": 1
      }
    ],
    "total": 30,
    "per_page": 15,
    "last_page": 2
  }
}
```

### 2. 获取房间详情
```http
GET /api/admin/rooms/1
Authorization: Bearer {token}
```

### 3. 创建房间
```http
POST /api/admin/rooms
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_name": "101室",
  "floor": 1,
  "room_type": "豪华套房",
  "color_code": "#FF5733",
  "ac_group_id": 1,
  "display_order": 1
}
```

**验证规则**:
- `room_name`: 必填，最大50字符，唯一
- `floor`: 必填，只能是1或2
- `room_type`: 可选，最大50字符
- `color_code`: 可选，最大7字符
- `ac_group_id`: 可选，整数
- `display_order`: 可选，整数

### 4. 更新房间
```http
PUT /api/admin/rooms/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_name": "101室VIP",
  "room_type": "总统套房",
  "display_order": 2
}
```

### 5. 删除房间
```http
DELETE /api/admin/rooms/1
Authorization: Bearer {token}
```

**注意**: 如果房间存在房态记录，将无法删除，返回400错误。

---

## 房态管理API

### 1. 获取指定月份的房态列表
```http
GET /api/admin/room-status?record_month=2025-01
Authorization: Bearer {token}
```

**查询参数**:
- `record_month`: 必填，查询月份（格式：YYYY-MM）

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": [
    {
      "room_id": 1,
      "room_name": "101室",
      "floor": 1,
      "room_type": "豪华套房",
      "room_statuses": [
        {
          "room_status_id": 1,
          "customer_id": 1,
          "check_in_date": "2025-01-15 14:00:00",
          "check_out_date": "2025-02-10 12:00:00",
          "status": 1,
          "record_month": "2025-01"
        }
      ]
    }
  ]
}
```

### 2. 创建房态记录
```http
POST /api/admin/room-status
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_id": 1,
  "customer_id": 1,
  "check_in_date": "2025-01-15 14:00:00",
  "check_out_date": "2025-02-10 12:00:00",
  "status": 1,
  "record_month": "2025-01"
}
```

**验证规则**:
- `room_id`: 必填，必须存在
- `customer_id`: 可选，必须存在
- `check_in_date`: 可选，日期格式
- `check_out_date`: 可选，日期格式，必须晚于入住日期
- `status`: 必填，0=空闲，1=已入住，2=维修
- `record_month`: 必填，格式YYYY-MM

**注意**: 每个房间在同一月份只能有一条房态记录。

### 3. 更新房态记录
```http
PUT /api/admin/room-status/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": 0,
  "customer_id": null
}
```

### 4. 删除房态记录
```http
DELETE /api/admin/room-status/1
Authorization: Bearer {token}
```

### 5. 办理入住（跨月自动生成）
```http
POST /api/admin/room-status/check-in
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_id": 1,
  "customer_id": 1,
  "check_in_date": "2025-01-15 14:00:00",
  "check_out_date": "2025-03-10 12:00:00"
}
```

**功能说明**:
- 自动计算入住时间跨越的所有月份
- 为每个月份自动创建房态记录
- 使用数据库事务确保数据一致性

**响应示例**:
```json
{
  "code": 200,
  "message": "入住办理成功",
  "data": {
    "room_id": 1,
    "customer_id": 1,
    "months": ["2025-01", "2025-02", "2025-03"]
  }
}
```

### 6. 办理退房
```http
POST /api/admin/room-status/check-out
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_id": 1,
  "customer_id": 1
}
```

**功能说明**:
- 查找该客户在该房间的所有房态记录
- 将状态改为空闲（status=0）
- 清空客户信息和入住/退房日期
- 使用数据库事务确保数据一致性

**响应示例**:
```json
{
  "code": 200,
  "message": "退房办理成功",
  "data": {
    "updated_count": 3
  }
}
```

---

## 评分卡管理API

### 1. 获取评分卡记录列表
```http
GET /api/admin/score-cards?customer_id=1&start_date=2025-01-01&end_date=2025-01-31&sort_by=record_date&sort_order=desc&per_page=15
Authorization: Bearer {token}
```

**查询参数**:
- `customer_id`: 客户ID筛选
- `start_date`: 开始日期
- `end_date`: 结束日期
- `sort_by`: 排序字段（默认: record_date）
- `sort_order`: 排序方向（asc/desc，默认: desc）
- `per_page`: 每页数量（默认: 15）

**响应示例**:
```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "score_card_record_id": 1,
        "customer_id": 1,
        "card_number": 1,
        "record_date": "2025-01-16",
        "score_data": {
          "health": 90,
          "service": 95,
          "food": 88
        },
        "customer": {
          "customer_id": 1,
          "customer_name": "张三",
          "phone": "13800138000"
        }
      }
    ],
    "total": 20,
    "per_page": 15,
    "last_page": 2
  }
}
```

### 2. 获取评分卡详情
```http
GET /api/admin/score-cards/1
Authorization: Bearer {token}
```

### 3. 创建评分卡记录
```http
POST /api/admin/score-cards
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "card_number": 1,
  "record_date": "2025-01-16",
  "score_data": {
    "health": 90,
    "service": 95,
    "food": 88,
    "environment": 92
  }
}
```

**验证规则**:
- `customer_id`: 必填，必须存在
- `card_number`: 必填，整数，最小为1
- `record_date`: 必填，日期格式
- `score_data`: 可选，JSON对象

### 4. 更新评分卡记录
```http
PUT /api/admin/score-cards/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "card_number": 2,
  "score_data": {
    "health": 95,
    "service": 98,
    "food": 90,
    "environment": 93
  }
}
```

### 5. 删除评分卡记录
```http
DELETE /api/admin/score-cards/1
Authorization: Bearer {token}
```

---

## 错误响应格式

所有API接口统一采用以下错误响应格式：

### 400 - 请求参数错误
```json
{
  "code": 400,
  "message": "请输入客户姓名",
  "data": null
}
```

### 401 - 未认证
```json
{
  "code": 401,
  "message": "未登录或登录已过期",
  "data": null
}
```

### 403 - 无权限
```json
{
  "code": 403,
  "message": "账号已被禁用",
  "data": null
}
```

### 404 - 资源不存在
```json
{
  "code": 404,
  "message": "客户不存在",
  "data": null
}
```

### 500 - 服务器错误
```json
{
  "code": 500,
  "message": "入住办理失败：数据库连接超时",
  "data": null
}
```

---

## 开发最佳实践

### 1. 数据验证
所有输入数据都通过Laravel Validator进行验证，确保数据安全和完整性。

### 2. 错误处理
- 统一的JSON响应格式
- 友好的中文错误提示
- 适当的HTTP状态码

### 3. 数据库事务
复杂操作（如入住、退房）使用数据库事务，确保数据一致性。

### 4. 关联加载
使用Eloquent关系预加载（eager loading）优化查询性能，避免N+1问题。

### 5. 安全性
- 密码使用bcrypt加密
- Token认证保护敏感接口
- 管理员状态检查
- SQL注入防护（Eloquent ORM）
- XSS防护（Laravel自动转义）

---

## 部署说明

### 环境要求
- PHP 8.3+
- MySQL 8.0+
- Composer 2.x
- Laravel 12

### 部署步骤
1. 克隆代码仓库
2. 安装依赖: `composer install`
3. 配置环境变量（.env文件）
4. 执行SQL建表脚本: `new_tables.sql`
5. 生成应用密钥: `php artisan key:generate`
6. 配置Web服务器（Nginx/Apache）
7. 启动服务: `php artisan serve`

### 默认管理员账号
- 用户名: `admin`
- 密码: `admin123`

---

## 后续开发计划

### 待开发模块
1. 小程序用户端API（商品、订单、积分等）
2. 数据统计和报表功能
3. 文件上传和管理
4. 消息通知系统
5. 操作日志记录

### 优化方向
1. 添加API接口缓存
2. 实现接口限流
3. 添加API文档自动生成（Swagger/OpenAPI）
4. 实现批量操作接口
5. 添加数据导出功能

---

## 联系方式

如有问题或建议，请联系开发团队。

**文档最后更新时间**: 2025-12-22
