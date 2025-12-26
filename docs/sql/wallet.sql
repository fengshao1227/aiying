-- ============================================
-- 钱包模块数据库表结构
-- 生成时间：2025-12-26
-- ============================================

-- 1. 用户钱包表
CREATE TABLE IF NOT EXISTS `user_wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '钱包ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额（元）',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=正常，1=冻结',
  `payment_password` varchar(255) DEFAULT NULL COMMENT '支付密码（bcrypt哈希）',
  `password_set_at` datetime DEFAULT NULL COMMENT '密码设置时间',
  `password_fail_count` int unsigned NOT NULL DEFAULT '0' COMMENT '密码错误次数',
  `password_locked_until` datetime DEFAULT NULL COMMENT '密码锁定截止时间',
  `frozen_at` datetime DEFAULT NULL COMMENT '冻结时间',
  `frozen_by` bigint unsigned DEFAULT NULL COMMENT '冻结操作管理员ID',
  `frozen_reason` varchar(255) DEFAULT NULL COMMENT '冻结原因',
  `version` int unsigned NOT NULL DEFAULT '1' COMMENT '乐观锁版本号',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_wallets_user_id_unique` (`user_id`),
  KEY `user_wallets_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户钱包表';

-- 2. 钱包流水表（审计日志）
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `wallet_id` bigint unsigned NOT NULL COMMENT '钱包ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL COMMENT '类型：topup=充值，consume=消费，adjust=调整，freeze=冻结，unfreeze=解冻',
  `amount` decimal(10,2) NOT NULL COMMENT '变动金额（正数增加，负数减少）',
  `balance_before` decimal(10,2) NOT NULL COMMENT '变更前余额',
  `balance_after` decimal(10,2) NOT NULL COMMENT '变更后余额',
  `source` varchar(50) NOT NULL COMMENT '来源：wechat=微信充值，mall_order=商城订单，meal_order=订餐订单，admin=后台操作',
  `source_id` bigint unsigned DEFAULT NULL COMMENT '关联业务ID（订单ID等）',
  `transaction_id` varchar(64) DEFAULT NULL COMMENT '微信交易号',
  `status` varchar(20) NOT NULL DEFAULT 'success' COMMENT '状态：pending=处理中，success=成功，failed=失败',
  `operator_id` bigint unsigned DEFAULT NULL COMMENT '操作管理员ID（后台操作时）',
  `reason` varchar(255) DEFAULT NULL COMMENT '变更原因/备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_wallet_id_index` (`wallet_id`),
  KEY `wallet_transactions_user_id_created_index` (`user_id`, `created_at`),
  KEY `wallet_transactions_source_index` (`source`, `source_id`),
  KEY `wallet_transactions_type_index` (`type`),
  KEY `wallet_transactions_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包流水表';

-- 3. 充值订单表
CREATE TABLE IF NOT EXISTS `recharge_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '充值订单ID',
  `order_no` varchar(64) NOT NULL COMMENT '充值单号',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `wallet_id` bigint unsigned NOT NULL COMMENT '钱包ID',
  `amount` decimal(10,2) NOT NULL COMMENT '充值金额（元）',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=待支付，1=成功，2=失败/取消',
  `transaction_id` varchar(64) DEFAULT NULL COMMENT '微信支付交易号',
  `prepay_id` varchar(64) DEFAULT NULL COMMENT '微信预支付ID',
  `paid_at` datetime DEFAULT NULL COMMENT '支付成功时间',
  `expired_at` datetime DEFAULT NULL COMMENT '订单过期时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recharge_orders_order_no_unique` (`order_no`),
  KEY `recharge_orders_user_id_index` (`user_id`),
  KEY `recharge_orders_status_index` (`status`),
  KEY `recharge_orders_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值订单表';

-- 4. 钱包配置表（可选，也可复用 system_configs）
CREATE TABLE IF NOT EXISTS `wallet_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` varchar(255) NOT NULL COMMENT '配置值',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新管理员ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_configs_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包配置表';

-- 5. 插入默认配置
INSERT INTO `wallet_configs` (`config_key`, `config_value`, `description`) VALUES
('topup_min', '1.00', '最低充值金额（元）'),
('topup_max', '200.00', '最高充值金额（元）'),
('password_max_fail', '5', '支付密码最大错误次数'),
('password_lock_minutes', '30', '密码锁定时长（分钟）');
