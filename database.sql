-- MySQL 8.0+ 数据库初始化脚本
-- 瑷婴月子中心小程序后端数据库
-- 生成时间: 2025-12-22

-- ============================================
-- 创建数据库
-- ============================================
CREATE DATABASE IF NOT EXISTS `aiying_health` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aiying_health`;

-- ============================================
-- 1. 用户表 (users)
-- ============================================
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) NOT NULL COMMENT '微信openid',
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号',
  `name` varchar(255) DEFAULT NULL COMMENT '姓名',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像URL',
  `gender` tinyint NOT NULL DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
  `points_balance` int NOT NULL DEFAULT '0' COMMENT '积分余额',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=正常',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_openid_unique` (`openid`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_phone_index` (`phone`),
  KEY `users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ============================================
-- 2. 商品分类表 (product_categories)
-- ============================================
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '分类名称',
  `parent_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '父级分类ID,0=顶级分类',
  `icon` varchar(255) DEFAULT NULL COMMENT '分类图标URL',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序,数字越小越靠前',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_categories_parent_id_index` (`parent_id`),
  KEY `product_categories_status_index` (`status`),
  KEY `product_categories_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品分类表';

-- ============================================
-- 3. 商品表 (products)
-- ============================================
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL COMMENT '分类ID',
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '商品主图',
  `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `price` decimal(10,2) NOT NULL COMMENT '现价/售价',
  `stock` int NOT NULL DEFAULT '0' COMMENT '库存数量',
  `sales` int NOT NULL DEFAULT '0' COMMENT '销量',
  `unit` varchar(255) NOT NULL DEFAULT '件' COMMENT '单位',
  `summary` text COLLATE utf8mb4_unicode_ci COMMENT '商品简介',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '商品详情',
  `tech_params` json DEFAULT NULL COMMENT '技术参数JSON',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0=下架,1=上架',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_index` (`category_id`),
  KEY `products_status_index` (`status`),
  KEY `products_sort_order_index` (`sort_order`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品表';

-- ============================================
-- 4. 商品图片表 (product_images)
-- ============================================
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `image_url` varchar(255) NOT NULL COMMENT '图片URL',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_index` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品图片表';

-- ============================================
-- 5. 商品规格SKU表 (product_specifications)
-- ============================================
CREATE TABLE `product_specifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_code` varchar(255) NOT NULL COMMENT 'SKU编码',
  `spec_values` json DEFAULT NULL COMMENT '规格值JSON,如{"精度":"0.01","电压":"220V"}',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'SKU价格,null=使用商品默认价格',
  `stock` int NOT NULL DEFAULT '0' COMMENT 'SKU库存',
  `image` varchar(255) DEFAULT NULL COMMENT 'SKU图片',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_specifications_sku_code_unique` (`sku_code`),
  KEY `product_specifications_product_id_index` (`product_id`),
  KEY `product_specifications_status_index` (`status`),
  CONSTRAINT `product_specifications_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品规格SKU表';

-- ============================================
-- 6. 购物车表 (shopping_cart)
-- ============================================
CREATE TABLE `shopping_cart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_id` bigint unsigned DEFAULT NULL COMMENT 'SKU ID,null=无规格商品',
  `quantity` int NOT NULL DEFAULT '1' COMMENT '数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shopping_cart_user_id_product_id_sku_id_unique` (`user_id`,`product_id`,`sku_id`),
  KEY `shopping_cart_user_id_index` (`user_id`),
  CONSTRAINT `shopping_cart_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_sku_id_foreign` FOREIGN KEY (`sku_id`) REFERENCES `product_specifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车表';

-- ============================================
-- 7. 收货地址表 (shipping_addresses)
-- ============================================
CREATE TABLE `shipping_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `name` varchar(255) NOT NULL COMMENT '收货人姓名',
  `phone` varchar(255) NOT NULL COMMENT '收货人电话',
  `province` varchar(255) NOT NULL COMMENT '省份',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `district` varchar(255) NOT NULL COMMENT '区县',
  `detail` varchar(255) NOT NULL COMMENT '详细地址',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认地址',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipping_addresses_user_id_index` (`user_id`),
  KEY `shipping_addresses_is_default_index` (`is_default`),
  CONSTRAINT `shipping_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址表';

-- ============================================
-- 8. 订单表 (orders)
-- ============================================
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(255) NOT NULL COMMENT '订单号',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `order_type` enum('goods','family_meal') NOT NULL COMMENT '订单类型:goods=商品订单,family_meal=家庭套餐',

  -- 收货地址快照
  `receiver_name` varchar(255) NOT NULL COMMENT '收货人姓名',
  `receiver_phone` varchar(255) NOT NULL COMMENT '收货人电话',
  `receiver_province` varchar(255) NOT NULL COMMENT '省份',
  `receiver_city` varchar(255) NOT NULL COMMENT '城市',
  `receiver_district` varchar(255) NOT NULL COMMENT '区县',
  `receiver_detail` varchar(255) NOT NULL COMMENT '详细地址',

  -- 金额信息
  `goods_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总金额',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `points_used` int NOT NULL DEFAULT '0' COMMENT '使用积分数量',
  `points_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
  `total_amount` decimal(10,2) NOT NULL COMMENT '订单总金额(实付)',

  -- 订单状态
  `order_status` tinyint NOT NULL DEFAULT '0' COMMENT '订单状态:0=待支付,1=待发货,2=待收货,3=已完成,4=已取消,5=已退款',
  `payment_status` tinyint NOT NULL DEFAULT '0' COMMENT '支付状态:0=未支付,1=已支付,2=已退款',

  -- 备注与时间
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '订单备注',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `shipped_at` timestamp NULL DEFAULT NULL COMMENT '发货时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT '取消时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_no_unique` (`order_no`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_order_no_index` (`order_no`),
  KEY `orders_order_type_order_status_index` (`order_type`,`order_status`),
  KEY `orders_payment_status_index` (`payment_status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- ============================================
-- 9. 订单商品明细表 (order_items)
-- ============================================
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_id` bigint unsigned DEFAULT NULL COMMENT 'SKU ID',

  -- 商品快照
  `product_name` varchar(255) NOT NULL COMMENT '商品名称',
  `product_image` varchar(255) DEFAULT NULL COMMENT '商品图片',
  `sku_name` varchar(255) DEFAULT NULL COMMENT 'SKU规格名称',
  `price` decimal(10,2) NOT NULL COMMENT '商品单价',
  `quantity` int NOT NULL COMMENT '购买数量',
  `subtotal` decimal(10,2) NOT NULL COMMENT '小计金额',

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单商品明细表';

-- ============================================
-- 10. 家庭套餐表 (family_meal_packages)
-- ============================================
CREATE TABLE `family_meal_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '套餐名称',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图',
  `price` decimal(10,2) NOT NULL COMMENT '套餐价格',
  `duration_days` int NOT NULL DEFAULT '1' COMMENT '服务天数',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '套餐描述',
  `services` json DEFAULT NULL COMMENT '服务项目JSON',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0=下架,1=上架',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_meal_packages_status_index` (`status`),
  KEY `family_meal_packages_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='家庭套餐表';

-- ============================================
-- 11. 支付记录表 (payments)
-- ============================================
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `payment_no` varchar(255) NOT NULL COMMENT '支付流水号',
  `transaction_id` varchar(255) DEFAULT NULL COMMENT '第三方交易号(微信支付)',
  `payment_method` enum('wechat') NOT NULL DEFAULT 'wechat' COMMENT '支付方式',
  `amount` decimal(10,2) NOT NULL COMMENT '支付金额',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '支付状态:0=待支付,1=已支付,2=已退款,3=支付失败',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付完成时间',
  `payment_data` json DEFAULT NULL COMMENT '支付原始数据JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_no_unique` (`payment_no`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `payments_payment_no_index` (`payment_no`),
  KEY `payments_transaction_id_index` (`transaction_id`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付记录表';

-- ============================================
-- 12. 积分历史表 (points_history)
-- ============================================
CREATE TABLE `points_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `type` enum('earn','spend','refund') NOT NULL COMMENT '类型:earn=获得,spend=消费,refund=退还',
  `points` int NOT NULL COMMENT '积分数量(正数=增加,负数=减少)',
  `balance_after` int NOT NULL COMMENT '变动后余额',
  `source` varchar(255) NOT NULL COMMENT '来源:order=订单,refund=退款,admin=后台调整等',
  `source_id` bigint unsigned DEFAULT NULL COMMENT '来源ID(如订单ID)',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `points_history_user_id_index` (`user_id`),
  KEY `points_history_type_index` (`type`),
  KEY `points_history_source_source_id_index` (`source`,`source_id`),
  CONSTRAINT `points_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分历史表';

-- ============================================
-- 插入测试数据(可选)
-- ============================================

-- 商品分类
INSERT INTO `product_categories` (`name`, `parent_id`, `sort_order`, `status`) VALUES
('医疗器械', 0, 1, 1),
('母婴用品', 0, 2, 1),
('产康服务', 0, 3, 1);

-- ============================================
-- 完成
-- ============================================
-- 所有表创建完成！
--
-- 执行方式：
-- 1. 登录MySQL: mysql -uroot -p
-- 2. 执行脚本: source /path/to/database.sql
--
-- 或者直接执行: mysql -uroot -p < database.sql
