-- ============================================================================
-- 瑷婴月子中心小程序 - 数据库设计 v2.0
-- 创建日期：2025-12-24
-- 说明：全新设计，从零开始
-- ============================================================================

-- 设置字符集
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 第一部分：用户体系
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1.1 users - 小程序用户表
-- 说明：微信小程序登录的用户，可通过手机号绑定到月子中心客户
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(64) NOT NULL COMMENT '微信openid',
  `unionid` varchar(64) DEFAULT NULL COMMENT '微信unionid（预留）',
  `customer_id` bigint unsigned DEFAULT NULL COMMENT '绑定的客户ID',
  `bind_phone` varchar(20) DEFAULT NULL COMMENT '绑定的月子中心登记手机号',
  `nickname` varchar(64) DEFAULT NULL COMMENT '微信昵称',
  `avatar` varchar(512) DEFAULT NULL COMMENT '头像URL',
  `gender` tinyint unsigned DEFAULT '0' COMMENT '性别：0=未知，1=男，2=女',
  `phone` varchar(20) DEFAULT NULL COMMENT '微信绑定手机号',
  `points_balance` int unsigned NOT NULL DEFAULT '0' COMMENT '积分余额',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=正常',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  UNIQUE KEY `uk_bind_phone` (`bind_phone`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='小程序用户表';

-- ============================================================================
-- 第二部分：商品系统
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 2.1 categories - 商品分类表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `icon` varchar(512) DEFAULT NULL COMMENT '分类图标',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_sort_status` (`sort_order` DESC, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品分类表';

-- ---------------------------------------------------------------------------
-- 2.2 products - 商品表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `category_id` bigint unsigned NOT NULL COMMENT '分类ID',
  `name` varchar(128) NOT NULL COMMENT '商品名称',
  `cover_image` varchar(512) NOT NULL COMMENT '商品主图',
  `images` json DEFAULT NULL COMMENT '商品图片列表',
  `delivery_type` enum('express','room') NOT NULL DEFAULT 'express' COMMENT '配送类型：express=快递，room=送到房间',
  `original_price` decimal(10,2) unsigned DEFAULT NULL COMMENT '原价（划线价）',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '现金价格',
  `points_price` int unsigned DEFAULT NULL COMMENT '积分价格（null=不支持积分兑换）',
  `stock` int unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `sales` int unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `unit` varchar(20) DEFAULT '件' COMMENT '单位',
  `summary` varchar(255) DEFAULT NULL COMMENT '商品简介',
  `description` text COMMENT '商品详情（富文本）',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序（越大越靠前）',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=下架，1=上架',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_category_status` (`category_id`, `status`),
  KEY `idx_delivery_type` (`delivery_type`),
  KEY `idx_sort_order` (`sort_order` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品表';

-- ============================================================================
-- 第三部分：商城订单系统
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 3.1 orders - 商城订单表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `customer_id` bigint unsigned DEFAULT NULL COMMENT '客户ID',

  -- 配送信息
  `delivery_type` enum('express','room') NOT NULL DEFAULT 'express' COMMENT '配送类型',
  `room_id` bigint unsigned DEFAULT NULL COMMENT '房间ID（房间配送）',
  `room_name` varchar(50) DEFAULT NULL COMMENT '房间名称（冗余）',
  `receiver_name` varchar(50) DEFAULT NULL COMMENT '收货人姓名（快递）',
  `receiver_phone` varchar(20) DEFAULT NULL COMMENT '收货人电话（快递）',
  `receiver_address` varchar(255) DEFAULT NULL COMMENT '收货地址（快递）',

  -- 金额信息
  `total_amount` decimal(10,2) unsigned NOT NULL COMMENT '商品总金额',
  `freight_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '运费',
  `points_used` int unsigned NOT NULL DEFAULT '0' COMMENT '使用积分',
  `points_discount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
  `actual_amount` decimal(10,2) unsigned NOT NULL COMMENT '实付金额',

  -- 支付信息
  `payment_type` enum('cash','points','mixed') NOT NULL DEFAULT 'cash' COMMENT '支付类型：cash=现金，points=纯积分，mixed=混合',
  `payment_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '支付状态：0=未支付，1=已支付',
  `transaction_id` varchar(64) DEFAULT NULL COMMENT '微信支付交易号',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',

  -- 订单状态
  `order_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '订单状态：0=待支付，1=待发货，2=已发货，3=已完成，4=已取消',
  `shipping_no` varchar(64) DEFAULT NULL COMMENT '快递单号',
  `shipping_company` varchar(50) DEFAULT NULL COMMENT '快递公司',
  `shipped_at` timestamp NULL DEFAULT NULL COMMENT '发货时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT '取消时间',
  `cancel_reason` varchar(255) DEFAULT NULL COMMENT '取消原因',

  -- 退款信息
  `refund_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '退款状态：0=无，1=申请中，2=已退款，3=已拒绝',
  `refund_reason` varchar(255) DEFAULT NULL COMMENT '退款原因',
  `refund_amount` decimal(10,2) unsigned DEFAULT NULL COMMENT '退款金额',
  `refund_points` int unsigned DEFAULT NULL COMMENT '退还积分',
  `refund_at` timestamp NULL DEFAULT NULL COMMENT '退款时间',

  `remarks` varchar(255) DEFAULT NULL COMMENT '订单备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_refund_status` (`refund_status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商城订单表';

-- ---------------------------------------------------------------------------
-- 3.2 order_items - 商城订单明细表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `product_name` varchar(128) NOT NULL COMMENT '商品名称（冗余）',
  `product_image` varchar(512) NOT NULL COMMENT '商品图片（冗余）',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '商品单价',
  `quantity` int unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  `subtotal` decimal(10,2) unsigned NOT NULL COMMENT '小计金额',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商城订单明细表';

-- ============================================================================
-- 第四部分：订餐系统
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 4.1 meal_configs - 订餐配置表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `meal_configs`;
CREATE TABLE `meal_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `meal_type` enum('breakfast','lunch','dinner') NOT NULL COMMENT '餐次类型',
  `name` varchar(20) NOT NULL COMMENT '餐次名称',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '单价',
  `order_start_time` time DEFAULT NULL COMMENT '可订餐开始时间',
  `order_end_time` time DEFAULT NULL COMMENT '可订餐截止时间',
  `advance_days` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '需提前几天订餐（0=当天可订）',
  `description` varchar(255) DEFAULT NULL COMMENT '描述说明',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_meal_type` (`meal_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订餐配置表';

-- 插入默认餐次配置
INSERT INTO `meal_configs` (`meal_type`, `name`, `price`, `order_start_time`, `order_end_time`, `advance_days`, `description`) VALUES
('breakfast', '早餐', 15.00, '00:00:00', '20:00:00', 1, '陪护早餐，需提前一天晚8点前预订'),
('lunch', '午餐', 25.00, '00:00:00', '09:00:00', 0, '陪护午餐，当天早9点前可订'),
('dinner', '晚餐', 25.00, '00:00:00', '14:00:00', 0, '陪护晚餐，当天下午2点前可订');

-- ---------------------------------------------------------------------------
-- 4.2 meal_orders - 订餐订单表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `meal_orders`;
CREATE TABLE `meal_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `customer_id` bigint unsigned NOT NULL COMMENT '客户ID',
  `room_id` bigint unsigned DEFAULT NULL COMMENT '房间ID',
  `room_name` varchar(50) NOT NULL COMMENT '房间名称（冗余）',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名（冗余）',

  -- 金额信息
  `total_amount` decimal(10,2) unsigned NOT NULL COMMENT '订单总金额',
  `points_used` int unsigned NOT NULL DEFAULT '0' COMMENT '使用积分',
  `points_discount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
  `actual_amount` decimal(10,2) unsigned NOT NULL COMMENT '实付金额',

  -- 支付信息
  `payment_type` enum('cash','points','mixed') NOT NULL DEFAULT 'cash' COMMENT '支付类型',
  `payment_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '支付状态：0=未支付，1=已支付',
  `transaction_id` varchar(64) DEFAULT NULL COMMENT '微信支付交易号',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',

  -- 订单状态
  `order_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '订单状态：0=待支付，1=已支付，2=已完成，3=已取消',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT '取消时间',
  `cancel_reason` varchar(255) DEFAULT NULL COMMENT '取消原因',

  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订餐订单表';

-- ---------------------------------------------------------------------------
-- 4.3 meal_order_items - 订餐订单明细表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `meal_order_items`;
CREATE TABLE `meal_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `meal_order_id` bigint unsigned NOT NULL COMMENT '订餐订单ID',
  `meal_date` date NOT NULL COMMENT '用餐日期',
  `meal_type` enum('breakfast','lunch','dinner') NOT NULL COMMENT '餐次类型',
  `meal_name` varchar(20) NOT NULL COMMENT '餐次名称（冗余）',
  `quantity` int unsigned NOT NULL DEFAULT '1' COMMENT '份数',
  `unit_price` decimal(10,2) unsigned NOT NULL COMMENT '单价',
  `subtotal` decimal(10,2) unsigned NOT NULL COMMENT '小计',
  `delivery_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '配送状态：0=待配送，1=已配送',
  `delivered_at` timestamp NULL DEFAULT NULL COMMENT '配送时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_meal_order_id` (`meal_order_id`),
  KEY `idx_meal_date` (`meal_date`),
  KEY `idx_meal_type` (`meal_type`),
  KEY `idx_date_type` (`meal_date`, `meal_type`),
  KEY `idx_delivery_status` (`delivery_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订餐订单明细表';

-- ============================================================================
-- 第五部分：积分系统
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 5.1 points_history - 积分变动记录表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `points_history`;
CREATE TABLE `points_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `customer_id` bigint unsigned DEFAULT NULL COMMENT '客户ID',
  `type` enum('earn','spend','refund','admin_add','admin_deduct') NOT NULL COMMENT '变动类型：earn=获取，spend=消费，refund=退还，admin_add=后台充值，admin_deduct=后台扣除',
  `points` int NOT NULL COMMENT '变动积分（正数=增加，负数=减少）',
  `balance_before` int unsigned NOT NULL COMMENT '变动前余额',
  `balance_after` int unsigned NOT NULL COMMENT '变动后余额',
  `source` varchar(50) DEFAULT NULL COMMENT '来源：order=商城订单，meal=订餐，admin=后台操作',
  `source_id` bigint unsigned DEFAULT NULL COMMENT '来源ID（订单ID等）',
  `description` varchar(255) DEFAULT NULL COMMENT '变动描述',
  `operator_id` bigint unsigned DEFAULT NULL COMMENT '操作人ID（后台操作时）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_type` (`type`),
  KEY `idx_source` (`source`, `source_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分变动记录表';

-- ============================================================================
-- 第六部分：系统配置
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 6.1 system_configs - 系统配置表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `system_configs`;
CREATE TABLE `system_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` varchar(64) NOT NULL COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string' COMMENT '值类型',
  `group` varchar(50) DEFAULT 'general' COMMENT '配置分组',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 插入默认配置
INSERT INTO `system_configs` (`config_key`, `config_value`, `config_type`, `group`, `description`) VALUES
-- 积分配置
('points_exchange_rate', '100', 'number', 'points', '积分兑换比例：多少积分=1元'),
('points_max_discount_rate', '50', 'number', 'points', '积分最大抵扣比例（百分比）'),

-- 订单配置
('order_cancel_minutes', '30', 'number', 'order', '订单自动取消时间（分钟）'),
('order_auto_complete_days', '7', 'number', 'order', '订单自动完成天数（发货后）'),

-- 通知配置
('wechat_robot_webhook', '', 'string', 'notification', '企业微信机器人Webhook地址'),
('daily_report_time', '20:00', 'string', 'notification', '每日统计推送时间'),
('notify_new_meal_order', 'true', 'boolean', 'notification', '新订餐订单是否通知'),
('notify_new_goods_order', 'true', 'boolean', 'notification', '新商城订单是否通知');

-- ---------------------------------------------------------------------------
-- 6.2 notification_logs - 通知日志表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `notification_logs`;
CREATE TABLE `notification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `type` varchar(50) NOT NULL COMMENT '通知类型：new_meal_order=新订餐，daily_meal_report=每日统计，new_goods_order=新商城订单',
  `channel` varchar(50) NOT NULL DEFAULT 'wechat_robot' COMMENT '通知渠道',
  `title` varchar(128) DEFAULT NULL COMMENT '通知标题',
  `content` text NOT NULL COMMENT '通知内容',
  `related_id` bigint unsigned DEFAULT NULL COMMENT '关联ID（订单ID等）',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=待发送，1=已发送，2=发送失败',
  `error_message` varchar(255) DEFAULT NULL COMMENT '错误信息',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知日志表';

-- ============================================================================
-- 第七部分：购物车（可选）
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 7.1 carts - 购物车表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `quantity` int unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  `selected` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否选中：0=否，1=是',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_product` (`user_id`, `product_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车表';

-- ============================================================================
-- 恢复外键检查
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;
