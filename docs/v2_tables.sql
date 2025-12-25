-- ============================================
-- V2 模块数据库表创建 SQL
-- ============================================

-- 1. 收货地址表
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `name` varchar(50) NOT NULL COMMENT '收货人姓名',
  `phone` varchar(20) NOT NULL COMMENT '收货人电话',
  `province` varchar(50) NOT NULL COMMENT '省份',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `district` varchar(50) NOT NULL COMMENT '区县',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认地址',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_index` (`user_id`),
  KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址表';

-- 2. 系统配置表
CREATE TABLE IF NOT EXISTS `system_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(64) NOT NULL COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string' COMMENT '值类型',
  `group` varchar(50) NOT NULL DEFAULT 'general' COMMENT '配置分组',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_configs_config_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 插入默认配置
INSERT INTO `system_configs` (`config_key`, `config_value`, `config_type`, `group`, `description`, `created_at`, `updated_at`) VALUES
('points_exchange_rate', '100', 'number', 'points', '积分兑换比例（100积分=1元）', NOW(), NOW()),
('points_max_discount_rate', '50', 'number', 'points', '最大抵扣比例（%）', NOW(), NOW()),
('order_cancel_minutes', '30', 'number', 'order', '订单自动取消时间（分钟）', NOW(), NOW()),
('order_auto_complete_days', '7', 'number', 'order', '订单自动完成天数', NOW(), NOW()),
('daily_report_time', '20:00', 'string', 'notification', '每日统计推送时间', NOW(), NOW()),
('notify_new_meal_order', 'true', 'boolean', 'notification', '新订餐订单通知开关', NOW(), NOW()),
('notify_new_goods_order', 'true', 'boolean', 'notification', '新商城订单通知开关', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 3. 通知日志表
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT '通知类型',
  `channel` varchar(50) NOT NULL DEFAULT 'wechat_robot' COMMENT '通知渠道',
  `title` varchar(128) DEFAULT NULL COMMENT '通知标题',
  `content` text NOT NULL COMMENT '通知内容',
  `related_id` bigint unsigned DEFAULT NULL COMMENT '关联ID',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=待发送，1=已发送，2=失败',
  `error_message` varchar(255) DEFAULT NULL COMMENT '错误信息',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `notification_logs_type_index` (`type`),
  KEY `notification_logs_status_index` (`status`),
  KEY `notification_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知日志表';

-- 4. 给 meal_orders 表添加退款字段（如果不存在）
ALTER TABLE `meal_orders`
ADD COLUMN IF NOT EXISTS `refund_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '退款状态：0=无，1=申请中，2=已退款，3=已拒绝' AFTER `cancel_reason`,
ADD COLUMN IF NOT EXISTS `refund_reason` varchar(255) DEFAULT NULL COMMENT '退款原因' AFTER `refund_status`,
ADD COLUMN IF NOT EXISTS `refund_at` timestamp NULL DEFAULT NULL COMMENT '退款时间' AFTER `refund_reason`;
