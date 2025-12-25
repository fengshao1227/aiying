-- ============================================================================
-- 瑷婴月子中心 - 保留表（月子中心管理）
-- 创建日期：2025-12-24
-- 说明：这些表是月子中心管理系统的核心表，需要保留
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1. admins - 管理员表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `admin_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名（登录账号）',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码（bcrypt加密）',
  `real_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '真实姓名',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系电话',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '最后登录IP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- 插入默认管理员（密码：admin123）
INSERT INTO `admins` (`username`, `password`, `real_name`, `status`, `created_at`, `updated_at`) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 1, NOW(), NOW());

-- ---------------------------------------------------------------------------
-- 2. customers - 客户档案表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `customer_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户姓名',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系电话',
  `package_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '套餐名称（春/夏/秋）',
  `baby_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '宝宝姓名',
  `mother_birthday` date DEFAULT NULL COMMENT '妈妈生日',
  `baby_birthday` date DEFAULT NULL COMMENT '宝宝生日',
  `nanny_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '月嫂姓名',
  `due_date` date DEFAULT NULL COMMENT '预产期',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT '家庭住址',
  `check_in_date` datetime DEFAULT NULL COMMENT '入住时间',
  `check_out_date` datetime DEFAULT NULL COMMENT '出所时间',
  `remarks` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_check_in_date` (`check_in_date`),
  KEY `idx_check_out_date` (`check_out_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户档案表';

-- ---------------------------------------------------------------------------
-- 3. rooms - 房间信息表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `room_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '房间ID',
  `room_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间名称（24节气命名）',
  `floor` tinyint NOT NULL COMMENT '楼层：1=一楼，2=二楼',
  `room_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '房型（标准间/套房/双床房等）',
  `color_code` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#FFFFFF' COMMENT '颜色代码（用于房态展示）',
  `ac_group_id` int DEFAULT NULL COMMENT '空调组ID',
  `display_order` int NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `uk_room_name` (`room_name`),
  KEY `idx_floor` (`floor`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='房间信息表';

-- ---------------------------------------------------------------------------
-- 4. room_status - 房态记录表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `room_status`;
CREATE TABLE `room_status` (
  `record_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `room_id` bigint unsigned NOT NULL COMMENT '房间ID',
  `customer_id` bigint unsigned DEFAULT NULL COMMENT '客户ID',
  `check_in_date` date DEFAULT NULL COMMENT '入住日期',
  `check_out_date` date DEFAULT NULL COMMENT '退房日期',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0=空闲，1=已入住，2=维护中',
  `record_month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '记录月份（格式：YYYY-MM）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `idx_room_month` (`room_id`,`record_month`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_check_dates` (`check_in_date`,`check_out_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_room_status_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_room_status_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='房态记录表';

-- ---------------------------------------------------------------------------
-- 5. score_card_records - 评分卡记录表
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `score_card_records`;
CREATE TABLE `score_card_records` (
  `record_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `customer_id` bigint unsigned NOT NULL COMMENT '客户ID',
  `card_number` int NOT NULL COMMENT '第几张评分卡（1,2,3...）',
  `record_date` date NOT NULL COMMENT '记录日期',
  `score_data` json DEFAULT NULL COMMENT '评分数据（JSON格式存储）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`record_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_record_date` (`record_date`),
  KEY `idx_customer_card` (`customer_id`,`card_number`),
  CONSTRAINT `fk_score_card_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评分卡记录表';

SET FOREIGN_KEY_CHECKS = 1;
