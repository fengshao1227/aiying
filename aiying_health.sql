-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-12-22 14:53:11
-- 服务器版本： 5.7.44-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `aiying_health`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（建议使用bcrypt加密）',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理���账户表';

--
-- 转存表中的数据 `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `password`, `real_name`, `created_at`) VALUES
(1, 'admin', 'admin123', '系统管理员', '2025-12-20 06:13:55');

-- --------------------------------------------------------

--
-- 表的结构 `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户姓名',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系电话',
  `package_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '套餐名称',
  `baby_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '宝宝姓名',
  `mother_birthday` date DEFAULT NULL COMMENT '妈妈生日',
  `baby_birthday` date DEFAULT NULL COMMENT '宝宝生日',
  `nanny_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '月嫂姓名',
  `due_date` date DEFAULT NULL COMMENT '预产期',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT '家庭住址',
  `check_in_date` datetime DEFAULT NULL COMMENT '入住时间',
  `check_out_date` datetime DEFAULT NULL COMMENT '出所时间',
  `remarks` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `phone`, `package_name`, `baby_name`, `mother_birthday`, `baby_birthday`, `nanny_name`, `due_date`, `address`, `check_in_date`, `check_out_date`, `remarks`, `created_at`, `updated_at`) VALUES
(10, '矫雨彤', '15315517613', '春', '嘻嘻', '1991-07-24', '2025-12-05', NULL, NULL, '平度市凤台世家', '2025-12-08 23:54:00', '2026-01-05 11:00:00', '', '2025-12-16 15:54:40', '2025-12-16 15:54:40'),
(11, '王嘉', '13355328444', '春', '艾拉', '1985-07-20', '2025-12-07', NULL, NULL, '平度御园新城', '2025-12-11 23:56:00', '2026-01-08 15:56:00', '', '2025-12-16 15:55:49', '2025-12-16 15:57:09'),
(12, '鞠鑫玉', '13210873647', '夏', '佑佑', '1999-10-28', '2025-12-09', NULL, NULL, '平度市上海花园', '2025-12-10 15:59:00', '2025-12-16 16:07:00', '', '2025-12-16 15:59:27', '2025-12-16 16:07:09'),
(13, '刘思宁', '13210228168', '夏', '乘乘', '2000-05-29', NULL, '', NULL, '黄岛区海信悦华里', '2025-11-24 16:06:00', '2025-12-16 16:07:00', '', '2025-12-16 16:03:29', '2025-12-16 16:29:16'),
(14, '谷英美', '18661891349', '春', '', '1996-06-28', '2025-12-04', NULL, NULL, '平度张戈庄', '2025-12-08 16:05:00', '2026-01-19 16:06:00', '', '2025-12-16 16:05:51', '2025-12-16 16:06:27'),
(15, '王英霞', '15726249109', '春', '依依', NULL, '2025-12-14', '曲芳燕', NULL, '平度锦厦新城', '2025-12-18 08:10:00', '2026-01-29 08:13:00', '', '2025-12-19 08:13:29', '2025-12-19 08:22:07'),
(16, '董燕', '15964235337', '夏', '酉宝', '1992-10-03', '2025-12-11', '徐美玉', NULL, '平度上城府邸', '2025-12-19 09:16:00', '2026-01-16 09:16:00', '', '2025-12-19 09:16:26', '2025-12-19 09:16:57'),
(17, '刘峻岐', '18237277196', '春', '', NULL, NULL, '', '2026-01-02', '', NULL, NULL, '', '2025-12-19 09:42:18', '2025-12-19 09:42:18'),
(18, '迟晓琳', '13954208656', '夏', '', NULL, NULL, '', '2026-01-11', '平度市圣泉花园', NULL, NULL, '赠送月子期间早餐', '2025-12-19 09:44:29', '2025-12-19 09:44:29'),
(19, '杨子涵', '17706398380', '春', '', NULL, NULL, '', '2026-01-27', '平度世纪商贸城', NULL, NULL, '赠送月子发汗、排湿排汗各一次', '2025-12-19 09:46:32', '2025-12-19 09:46:32'),
(20, '付晓莉', '13335001238', '秋', '', NULL, NULL, '', NULL, '平度中央美地天麓', NULL, '2026-01-31 09:48:00', '跨年费用乙方负担，腊月28日宝妈和宝妈公公过生日', '2025-12-19 09:48:43', '2025-12-19 09:48:43'),
(21, '王丹', '17685545786', '春', '', NULL, NULL, '', NULL, '小洪沟村', NULL, '2026-02-02 09:49:00', '', '2025-12-19 09:50:33', '2025-12-19 09:50:33'),
(22, '林雪琦', '13361243029', '春', '', NULL, NULL, '', NULL, '平度碧桂园桃李东方', NULL, '2026-02-04 09:50:00', '跨年费用由乙方承担', '2025-12-19 09:51:46', '2025-12-19 09:51:46'),
(23, '丁莉', '13553085766', '夏', '', NULL, NULL, '', NULL, '后八里庄', NULL, '2026-02-28 09:52:00', '', '2025-12-19 09:53:43', '2025-12-19 09:53:43'),
(24, '杨政慧', '18554871638', '秋', '', NULL, NULL, '', NULL, '平度海信城雅园', NULL, '2026-04-04 09:54:00', '月子期间家属早餐免费', '2025-12-19 09:55:56', '2025-12-19 09:55:56'),
(25, '田菁菁', '15666488467', '夏', '', NULL, NULL, '', NULL, '仁兆镇郑家管村', NULL, '2026-02-10 09:57:00', '跨年费用免费，月子期间早餐免费', '2025-12-19 09:58:23', '2025-12-19 09:58:23'),
(26, '姜雨帆', '13668852515', '春', '', NULL, NULL, '', '2026-04-30', '平度凤台世家', NULL, NULL, '赠送月子期间家属早餐', '2025-12-19 10:00:55', '2025-12-19 10:00:55'),
(27, '郝英杰', '15092294831', '春', '', NULL, NULL, '', '2026-02-06', '平度市龙宇上城', NULL, NULL, '大年30 初一 初二 月嫂回家', '2025-12-19 10:06:16', '2025-12-19 10:06:16'),
(28, '王娜', '16678699969', '夏', '', NULL, NULL, '', '2026-05-06', '平度中杰雅居', NULL, NULL, '', '2025-12-19 10:07:46', '2025-12-19 10:07:46'),
(29, '韩沂萱', '17753202926', '春', '', NULL, NULL, '', '2026-01-29', '平度安居苑', NULL, NULL, '除夕、初一、初二不要月嫂    家属餐免费15顿  四件套 待产包 月子房物品自备										(月嫂安静)  ⭐️总天数加一天', '2025-12-19 10:08:54', '2025-12-19 10:11:15'),
(30, '王佳佳', '13465849059', '春', '', NULL, NULL, '', '2026-03-06', '青岛市城阳区南疃社区', NULL, NULL, '', '2025-12-19 10:09:46', '2025-12-19 10:09:46'),
(31, '车润', '15505426063', '春', '', NULL, NULL, '', '2026-03-03', '平度青岛路', NULL, NULL, '', '2025-12-19 10:10:40', '2025-12-19 10:10:40'),
(32, '谭春倩', '15753256671', '春', '', NULL, NULL, '', '2026-04-17', '平度天悦华府', NULL, NULL, '', '2025-12-19 10:12:06', '2025-12-19 10:12:06'),
(33, '测试', '13800138000', '测试', '测试', NULL, NULL, NULL, NULL, '', NULL, NULL, '', '2025-12-20 15:24:13', '2025-12-20 15:24:13');

-- --------------------------------------------------------

--
-- 表的结构 `family_meals`
--

CREATE TABLE `family_meals` (
  `meal_id` int(11) NOT NULL COMMENT '配餐ID',
  `meal_name` varchar(100) NOT NULL COMMENT '配餐名称',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `description` text COMMENT '描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0-停用，1-启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='家属订餐配置表';

--
-- 转存表中的数据 `family_meals`
--

INSERT INTO `family_meals` (`meal_id`, `meal_name`, `price`, `description`, `status`, `created_at`) VALUES
(1, '陪护餐', '10.00', '营养均衡，精心烹饪', 1, '2025-12-20 06:49:22');

-- --------------------------------------------------------

--
-- 表的结构 `family_meal_orders`
--

CREATE TABLE `family_meal_orders` (
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `customer_id` int(11) DEFAULT NULL COMMENT '客户ID（关联customers表）',
  `room_name` varchar(50) NOT NULL COMMENT '房间名称',
  `customer_phone` varchar(20) NOT NULL COMMENT '客户电话',
  `meal_date` date NOT NULL COMMENT '用餐日期',
  `meal_time` varchar(100) NOT NULL COMMENT '用餐时间（JSON数组：breakfast/lunch/dinner）',
  `meal_count` int(11) NOT NULL DEFAULT '1' COMMENT '用餐份数',
  `total_amount` decimal(10,2) NOT NULL COMMENT '订单金额',
  `out_trade_no` varchar(100) DEFAULT NULL COMMENT '商户订单号',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT '微信交易号',
  `paid_at` datetime DEFAULT NULL COMMENT '支付完成时间',
  `notes` text COMMENT '备注',
  `payment_status` tinyint(1) DEFAULT '0' COMMENT '支付状态：0-待支付，1-已支付',
  `order_status` tinyint(1) DEFAULT '0' COMMENT '订单状态：0-待确认，1-已确认，2-已取消',
  `wechat_notified` tinyint(1) DEFAULT '0' COMMENT '企业微信通知状态：0-未发送，1-已发送',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='家属订餐订单表';

--
-- 转存表中的数据 `family_meal_orders`
--

INSERT INTO `family_meal_orders` (`order_id`, `customer_id`, `room_name`, `customer_phone`, `meal_date`, `meal_time`, `meal_count`, `total_amount`, `out_trade_no`, `transaction_id`, `paid_at`, `notes`, `payment_status`, `order_status`, `wechat_notified`, `created_at`) VALUES
(1, NULL, '31', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 06:51:30'),
(2, NULL, '123', '', '2025-12-20', '[\"breakfast\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 06:52:23'),
(3, NULL, '321', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 06:56:56'),
(4, NULL, '123', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:02:01'),
(5, NULL, '123', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:07:08'),
(6, NULL, '123', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:07:47'),
(7, NULL, '3', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:09:02'),
(8, NULL, '33', '', '2025-12-21', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:25:12'),
(9, NULL, '312', '', '2025-12-20', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 07:26:35'),
(10, NULL, '312', '', '2025-12-20', '[\"breakfast\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 08:18:58'),
(11, 33, '123', '13800138000', '2025-12-21', '[\"lunch\",\"dinner\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 08:40:40'),
(12, 33, '123', '13800138000', '2025-12-21', '[\"lunch\",\"dinner\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-20 11:55:12'),
(13, 33, '123', '13800138000', '2025-12-22', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-22 02:21:17'),
(14, 33, '123', '13800138000', '2025-12-22', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-22 02:22:50'),
(15, 33, '123', '13800138000', '2025-12-22', '[\"lunch\"]', 1, '10.00', NULL, NULL, NULL, '', 0, 0, 0, '2025-12-22 02:24:54');

-- --------------------------------------------------------

--
-- 表的结构 `goods`
--

CREATE TABLE `goods` (
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `goods_name` varchar(200) NOT NULL COMMENT '商品名称',
  `category` varchar(50) DEFAULT NULL COMMENT '商品分类',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `stock` int(11) DEFAULT '0' COMMENT '库存数量',
  `sales` int(11) DEFAULT '0' COMMENT '销量',
  `main_image` varchar(500) DEFAULT NULL COMMENT '主图URL',
  `images` text COMMENT '商品图片(JSON数组)',
  `spec_options` text COMMENT '规格选项(JSON)',
  `description` text COMMENT '商品描述',
  `details` text COMMENT '详情图片或HTML',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1=上架 0=下架',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';

--
-- 转存表中的数据 `goods`
--

INSERT INTO `goods` (`goods_id`, `goods_name`, `category`, `price`, `original_price`, `stock`, `sales`, `main_image`, `images`, `spec_options`, `description`, `details`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Premier 300 电能表', '电能表', '580.00', '998.00', 13, 9, 'https://cdn.uviewui.com/uview/album/1.jpg', '[\"https://cdn.uviewui.com/uview/swiper/swiper1.png\",\"https://cdn.uviewui.com/uview/swiper/swiper2.png\",\"https://cdn.uviewui.com/uview/swiper/swiper3.png\"]', '{\n    \"precision\": [\"0.1S级\", \"0.2S级\", \"0.5S级\"],\n    \"voltage\": [\"3x220/380V\", \"3x380/660V\"],\n    \"current\": [\"5(60)A\", \"10(100)A\", \"20(120)A\"]\n  }', '灵活多付费率 | 兼容多种协议（包括DMLS）| 接入管理系统', '<p>产品详情...</p>', 1, '2025-12-22 12:31:06', '2025-12-22 12:31:06'),
(2, 'Premier 300 电能表', '电能表', '580.00', '998.00', 13, 9, 'https://cdn.uviewui.com/uview/album/1.jpg', '[\"https://cdn.uviewui.com/uview/swiper/swiper1.png\",\"https://cdn.uviewui.com/uview/swiper/swiper2.png\",\"https://cdn.uviewui.com/uview/swiper/swiper3.png\"]', '{\r\n    \"precision\": [\"0.1S级\", \"0.2S级\", \"0.5S级\"],\r\n    \"voltage\": [\"3x220/380V\", \"3x380/660V\"],\r\n    \"current\": [\"5(60)A\", \"10(100)A\", \"20(120)A\"]\r\n  }', '灵活多付费率 | 兼容多种协议（包括DMLS）| 接入管理系统', '<p>产品详情...</p>', 1, '2025-12-22 12:31:07', '2025-12-22 12:31:07');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL COMMENT '订单号',
  `customer_phone` varchar(20) NOT NULL COMMENT '客户电话',
  `total_amount` decimal(10,2) NOT NULL COMMENT '订单总金额',
  `points_used` int(11) DEFAULT '0' COMMENT '使用的积分',
  `delivery_address` varchar(255) DEFAULT NULL COMMENT '配送地址',
  `notes` text COMMENT '备注',
  `payment_status` tinyint(1) DEFAULT '0' COMMENT '支付状态：0-待支付，1-已支付，2-已取消',
  `order_status` tinyint(1) DEFAULT '0' COMMENT '订单状态：0-待发货，1-配送中，2-已完成',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- --------------------------------------------------------

--
-- 表的结构 `orders_unified`
--

CREATE TABLE `orders_unified` (
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `order_no` varchar(50) NOT NULL COMMENT '订单编号(唯一)',
  `order_type` enum('goods','family_meal') NOT NULL DEFAULT 'goods' COMMENT '订单类型',
  `customer_phone` varchar(20) DEFAULT NULL COMMENT '客户电话',
  `customer_id` int(11) DEFAULT NULL COMMENT '关联客户ID(可选)',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  `payment_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态(0=待支付,1=已支付,2=已取消,3=已退款)',
  `out_trade_no` varchar(64) DEFAULT NULL COMMENT '微信商户订单号',
  `transaction_id` varchar(64) DEFAULT NULL COMMENT '微信支付交易号',
  `pay_time` datetime DEFAULT NULL COMMENT '支付完成时间',
  `order_data` text COMMENT '订单详细数据(JSON格式)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='统一订单表';

--
-- 转存表中的数据 `orders_unified`
--

INSERT INTO `orders_unified` (`order_id`, `order_no`, `order_type`, `customer_phone`, `customer_id`, `total_amount`, `payment_status`, `out_trade_no`, `transaction_id`, `pay_time`, `order_data`, `created_at`, `updated_at`) VALUES
(1, '202512221153216697', 'family_meal', '13800138000', 33, '10.00', 0, NULL, NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 03:53:21', '2025-12-22 03:53:21'),
(2, '202512221157302946', 'family_meal', '13800138000', 33, '10.00', 0, NULL, NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 03:57:30', '2025-12-22 03:57:30'),
(3, '202512221200144205', 'family_meal', '13800138000', 33, '10.00', 0, 'ORDER_202512221200144205', NULL, NULL, '{\"room_name\":\"312\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:00:14', '2025-12-22 04:00:16'),
(4, '202512221204399572', 'family_meal', '13800138000', 33, '10.00', 0, 'ORDER_202512221204399572', NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:04:39', '2025-12-22 04:04:42'),
(5, '202512221208361971', 'family_meal', '13800138000', 33, '10.00', 0, 'ORDER_202512221208361971', NULL, NULL, '{\"room_name\":\"31\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:08:36', '2025-12-22 04:08:38'),
(6, '202512221211133161', 'family_meal', '13800138000', 33, '10.00', 0, 'ORDER_202512221211133161', NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:11:13', '2025-12-22 04:11:15'),
(7, '202512221212492110', 'family_meal', '', NULL, '10.00', 0, NULL, NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:12:49', '2025-12-22 04:12:49'),
(8, '202512221214088256', 'family_meal', '', NULL, '10.00', 0, NULL, NULL, NULL, '{\"room_name\":\"321\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 04:14:08', '2025-12-22 04:14:08'),
(9, '202512221325591108', 'family_meal', '13800138000', 33, '10.00', 0, NULL, NULL, NULL, '{\"room_name\":\"311\",\"meal_date\":\"2025-12-22\",\"meal_times\":[\"lunch\"],\"quantity\":1,\"remarks\":\"\"}', '2025-12-22 05:25:59', '2025-12-22 05:25:59'),
(10, '202512221344206863', 'goods', '13800138000', 33, '0.01', 0, 'ORDER_202512221344206863', NULL, NULL, '{\"product_name\":\"商品订单(1件商品)\",\"specifications\":\"测试 x1\",\"quantity\":1,\"unit_price\":0.01,\"subtotal\":0.01,\"points_used\":0,\"shipping_address\":\"312324124多少啊 18790688888 321\",\"remarks\":\"{\\\"goods_list\\\":[{\\\"product_id\\\":5,\\\"product_name\\\":\\\"测试\\\",\\\"price\\\":0.01,\\\"quantity\\\":1,\\\"image_url\\\":\\\"/uploads/products/product_69465e2017b1e5.28706975.png\\\"}],\\\"subtotal\\\":0.01,\\\"points_used\\\":0,\\\"points_discount\\\":0,\\\"shipping_address\\\":\\\"312324124多少啊 18790688888 321\\\",\\\"receiver_name\\\":\\\"312324124多少啊\\\",\\\"receiver_phone\\\":\\\"18790688888\\\",\\\"address_detail\\\":\\\"321\\\"}\"}', '2025-12-22 05:44:20', '2025-12-22 05:44:20'),
(11, '202512221348474628', 'goods', '13800138000', 33, '0.01', 0, 'ORDER_202512221348474628', NULL, NULL, '{\"product_name\":\"商品订单(1件商品)\",\"specifications\":\"测试 x1\",\"quantity\":1,\"unit_price\":0.01,\"subtotal\":0.01,\"points_used\":0,\"shipping_address\":\"312324124多少啊 18790688888 321\",\"remarks\":\"{\\\"goods_list\\\":[{\\\"product_id\\\":5,\\\"product_name\\\":\\\"测试\\\",\\\"price\\\":0.01,\\\"quantity\\\":1,\\\"image_url\\\":\\\"/uploads/products/product_69465e2017b1e5.28706975.png\\\"}],\\\"subtotal\\\":0.01,\\\"points_used\\\":0,\\\"points_discount\\\":0,\\\"shipping_address\\\":\\\"312324124多少啊 18790688888 321\\\",\\\"receiver_name\\\":\\\"312324124多少啊\\\",\\\"receiver_phone\\\":\\\"18790688888\\\",\\\"address_detail\\\":\\\"321\\\"}\"}', '2025-12-22 05:48:47', '2025-12-22 05:48:47'),
(12, '202512221348596322', 'goods', '13800138000', 33, '0.01', 2, 'ORDER_202512221348596322', NULL, NULL, '{\"product_name\":\"商品订单(1件商品)\",\"specifications\":\"测试 x1\",\"quantity\":1,\"unit_price\":0.01,\"subtotal\":0.01,\"points_used\":0,\"shipping_address\":\"312324124多少啊 18790688888 321\",\"remarks\":\"{\\\"goods_list\\\":[{\\\"product_id\\\":5,\\\"product_name\\\":\\\"测试\\\",\\\"price\\\":0.01,\\\"quantity\\\":1,\\\"image_url\\\":\\\"/uploads/products/product_69465e2017b1e5.28706975.png\\\"}],\\\"subtotal\\\":0.01,\\\"points_used\\\":0,\\\"points_discount\\\":0,\\\"shipping_address\\\":\\\"312324124多少啊 18790688888 321\\\",\\\"receiver_name\\\":\\\"312324124多少啊\\\",\\\"receiver_phone\\\":\\\"18790688888\\\",\\\"address_detail\\\":\\\"321\\\"}\"}', '2025-12-22 05:48:59', '2025-12-22 06:01:44'),
(13, '202512221354051587', 'goods', '13800138000', 33, '0.01', 2, 'ORDER_202512221354051587', NULL, NULL, '{\"product_name\":\"商品订单(1件商品)\",\"specifications\":\"测试 x1\",\"quantity\":1,\"unit_price\":0.01,\"subtotal\":0.01,\"points_used\":0,\"points_discount\":0,\"shipping_address\":\"312324124多少啊 18790688888 321\",\"remarks\":\"{\\\"goods_list\\\":[{\\\"product_id\\\":5,\\\"product_name\\\":\\\"测试\\\",\\\"price\\\":0.01,\\\"quantity\\\":1,\\\"image_url\\\":\\\"/uploads/products/product_69465e2017b1e5.28706975.png\\\"}],\\\"subtotal\\\":0.01,\\\"points_used\\\":0,\\\"points_discount\\\":0,\\\"shipping_address\\\":\\\"312324124多少啊 18790688888 321\\\",\\\"receiver_name\\\":\\\"312324124多少啊\\\",\\\"receiver_phone\\\":\\\"18790688888\\\",\\\"address_detail\\\":\\\"321\\\"}\"}', '2025-12-22 05:54:05', '2025-12-22 05:56:09'),
(14, '202512221355585457', 'goods', '13800138000', 33, '0.01', 2, 'ORDER_202512221355585457', NULL, NULL, '{\"product_name\":\"商品订单(1件商品)\",\"specifications\":\"测试 x1\",\"quantity\":1,\"unit_price\":0.01,\"subtotal\":0.01,\"points_used\":0,\"points_discount\":0,\"shipping_address\":\"312324124多少啊 18790688888 321\",\"remarks\":\"{\\\"goods_list\\\":[{\\\"product_id\\\":5,\\\"product_name\\\":\\\"测试\\\",\\\"price\\\":0.01,\\\"quantity\\\":1,\\\"image_url\\\":\\\"/uploads/products/product_69465e2017b1e5.28706975.png\\\"}],\\\"subtotal\\\":0.01,\\\"points_used\\\":0,\\\"points_discount\\\":0,\\\"shipping_address\\\":\\\"312324124多少啊 18790688888 321\\\",\\\"receiver_name\\\":\\\"312324124多少啊\\\",\\\"receiver_phone\\\":\\\"18790688888\\\",\\\"address_detail\\\":\\\"321\\\"}\"}', '2025-12-22 05:55:58', '2025-12-22 05:56:06'),
(15, '202512221409404920', 'goods', '13800138000', 33, '0.01', 0, 'ORDER_202512221409404920', NULL, NULL, '{\"goods_list\":[{\"product_id\":5,\"product_name\":\"测试\",\"specifications\":\"\",\"price\":0.01,\"quantity\":1,\"image_url\":\"/uploads/products/product_69465e2017b1e5.28706975.png\"}],\"receiver_name\":\"312324124多少啊\",\"receiver_phone\":\"18790688888\",\"shipping_address\":\"312324124多少啊 18790688888 321\",\"address_detail\":\"321\",\"points_used\":0,\"points_discount\":0,\"subtotal\":0.01}', '2025-12-22 06:09:40', '2025-12-22 06:09:40');

-- --------------------------------------------------------

--
-- 表的结构 `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL COMMENT '订单项ID',
  `order_id` varchar(50) NOT NULL COMMENT '订单号',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `product_name` varchar(100) NOT NULL COMMENT '商品名称',
  `price` decimal(10,2) NOT NULL COMMENT '购买时单价',
  `quantity` int(11) NOT NULL COMMENT '购买数量'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单明细表';

-- --------------------------------------------------------

--
-- 表的结构 `points`
--

CREATE TABLE `points` (
  `customer_phone` varchar(20) NOT NULL COMMENT '客户电话',
  `points_balance` int(11) DEFAULT '0' COMMENT '当前积分余额',
  `total_earned` int(11) DEFAULT '0' COMMENT '累计获得积分',
  `total_used` int(11) DEFAULT '0' COMMENT '累计使用积分',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分账户表';

--
-- 转存表中的数据 `points`
--

INSERT INTO `points` (`customer_phone`, `points_balance`, `total_earned`, `total_used`, `updated_at`) VALUES
('13800138000', 123, 123, 0, '2025-12-20 07:26:07');

-- --------------------------------------------------------

--
-- 表的结构 `points_log`
--

CREATE TABLE `points_log` (
  `log_id` int(11) NOT NULL COMMENT '日志ID',
  `customer_phone` varchar(20) NOT NULL COMMENT '客户电话',
  `points_change` int(11) NOT NULL COMMENT '积分变动（正数为增加，负数为减少）',
  `change_type` varchar(20) NOT NULL COMMENT '变动类型：earned-消费获得，used-消费使用，admin-管理员调整',
  `order_id` varchar(50) DEFAULT NULL COMMENT '关联订单号',
  `notes` text COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分变动日志表';

--
-- 转存表中的数据 `points_log`
--

INSERT INTO `points_log` (`log_id`, `customer_phone`, `points_change`, `change_type`, `order_id`, `notes`, `created_at`) VALUES
(1, '13800138000', 123, 'admin', NULL, '', '2025-12-20 07:26:07');

-- --------------------------------------------------------

--
-- 表的结构 `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `product_name` varchar(100) NOT NULL COMMENT '商品名称',
  `description` text COMMENT '商品描述',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `image_url` varchar(255) DEFAULT NULL COMMENT '商品图片URL',
  `images` text COMMENT '商品图片(JSON数组)',
  `spec_options` text COMMENT '规格选项(JSON)',
  `category` varchar(50) DEFAULT NULL COMMENT '商品分类',
  `stock` int(11) DEFAULT '0' COMMENT '库存数量',
  `sales` int(11) DEFAULT '0' COMMENT '销量',
  `points_reward` int(11) DEFAULT '0' COMMENT '购买可获得的积分',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0-下架，1-上架',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品信息表';

--
-- 转存表中的数据 `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `original_price`, `image_url`, `images`, `spec_options`, `category`, `stock`, `sales`, `points_reward`, `status`, `created_at`) VALUES
(5, '测试', '测试', '0.01', '1.00', '/uploads/products/product_69465e2017b1e5.28706975.png', '[\"/uploads/products/product_69465e2017b1e5.28706975.png\"]', '', '', 100, 0, 0, 1, '2025-12-20 08:28:16');

-- --------------------------------------------------------

--
-- 表的结构 `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间名称',
  `floor` tinyint(4) NOT NULL COMMENT '楼层: 1=一楼, 2=二楼',
  `room_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '房型',
  `color_code` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#FFFFFF' COMMENT '颜色代码',
  `ac_group_id` int(11) DEFAULT NULL COMMENT '空调组ID',
  `display_order` int(11) DEFAULT '0' COMMENT '显示顺序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `floor`, `room_type`, `color_code`, `ac_group_id`, `display_order`, `created_at`, `updated_at`) VALUES
(1, '雨水', 1, '双床', '#ff40ff', 36, 0, '2025-12-16 11:30:38', '2025-12-16 16:30:45'),
(2, '立冬', 1, '标准间', '#00f900', 37, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:24'),
(3, '大暑', 1, '套房', '#00f900', 37, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:30'),
(4, '秋分', 1, '标准间', '#00f900', 37, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:40'),
(5, '寒露', 1, '双床房', '#ff40ff', 36, 0, '2025-12-16 11:30:38', '2025-12-16 16:30:54'),
(6, '白露', 1, '标准间', '#ff40ff', 36, 0, '2025-12-16 11:30:38', '2025-12-16 16:32:28'),
(7, '储藏间', 1, '储藏室', '#F5F5F5', 4, 7, '2025-12-16 11:30:38', '2025-12-16 11:30:38'),
(8, '员工宿舍', 1, '宿舍', '#F5F5F5', 4, 8, '2025-12-16 11:30:38', '2025-12-16 11:30:38'),
(9, '处暑', 1, '标准间', '#00f900', 37, 0, '2025-12-16 11:30:38', '2025-12-16 16:33:03'),
(10, '立秋', 1, '标准间', '#ff40ff', 36, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:05'),
(11, '小雪', 1, '标准间', '#00f900', 37, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:47'),
(12, '霜降', 1, '厨房', '#ff40ff', 36, 0, '2025-12-16 11:30:38', '2025-12-16 16:31:13'),
(13, '卫生', 1, '卫生间', '#a77b00', 34, 0, '2025-12-16 11:30:38', '2025-12-16 16:13:22'),
(14, '辰', 2, '小套房', '#9a244f', 39, 0, '2025-12-16 11:30:38', '2025-12-16 16:33:35'),
(15, '嘉和', 2, '标准间', '#791a3e', 40, 0, '2025-12-16 11:30:38', '2025-12-16 16:36:04'),
(16, '小寒', 2, '标准间', '#aa7942', 41, 0, '2025-12-16 11:30:38', '2025-12-16 16:33:55'),
(17, '冬至', 2, '标准间', '#aa7942', 41, 0, '2025-12-16 11:30:38', '2025-12-16 16:34:15'),
(18, '子', 2, '标准间', '#aa7942', 41, 0, '2025-12-16 11:30:38', '2025-12-16 16:34:23'),
(19, '惊蛰', 2, '双床房', '#fff9c4', 27, 0, '2025-12-16 11:30:38', '2025-12-16 16:04:01'),
(20, '芒种', 2, '大床房', '#fffcb3', 28, 0, '2025-12-16 11:30:38', '2025-12-16 16:04:19'),
(21, '春分', 2, '双床房', '#fffcb3', 28, 0, '2025-12-16 11:30:38', '2025-12-16 16:04:40'),
(22, '寅', 2, '标准间', '#00fdff', 42, 0, '2025-12-16 11:30:38', '2025-12-16 16:34:56'),
(23, '夏至', 2, '标准间', '#00fdff', 42, 0, '2025-12-16 11:30:38', '2025-12-16 16:35:03'),
(24, '小暑', 2, '标准间', '#00fdff', 42, 0, '2025-12-16 11:30:38', '2025-12-16 16:35:09'),
(25, '谷雨', 2, '标准间', '#00fdff', 42, 0, '2025-12-16 11:30:38', '2025-12-16 16:35:20'),
(26, '电梯厅', 2, '公共区域', '#F5F5F5', 14, 13, '2025-12-16 11:30:38', '2025-12-16 11:30:38'),
(27, '酉', 2, '小套房', '#0433ff', 38, 0, '2025-12-16 16:09:55', '2025-12-16 16:33:19');

-- --------------------------------------------------------

--
-- 表的结构 `room_status`
--

CREATE TABLE `room_status` (
  `record_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL COMMENT '房间ID',
  `customer_id` int(11) DEFAULT NULL COMMENT '客户ID',
  `check_in_date` date DEFAULT NULL COMMENT '入住日期',
  `check_out_date` date DEFAULT NULL COMMENT '退房日期',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=空闲, 1=已入住, 2=维护中',
  `record_month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '记录月份 YYYY-MM',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `room_status`
--

INSERT INTO `room_status` (`record_id`, `room_id`, `customer_id`, `check_in_date`, `check_out_date`, `status`, `record_month`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, NULL, 1, '2025-06', '2025-12-16 11:36:39', '2025-12-16 11:36:39'),
(2, 2, NULL, NULL, NULL, 0, '2025-12', '2025-12-16 11:37:36', '2025-12-16 14:53:56'),
(3, 3, NULL, NULL, NULL, 0, '2025-12', '2025-12-16 11:47:22', '2025-12-16 14:53:59'),
(4, 1, 16, '2025-12-19', '2026-01-16', 1, '2025-12', '2025-12-16 12:23:06', '2025-12-19 09:17:37'),
(5, 1, NULL, '2025-12-04', '2026-02-12', 1, '2026-02', '2025-12-16 12:43:37', '2025-12-16 12:43:37'),
(6, 1, NULL, '2025-12-14', '2026-01-28', 0, '2026-01', '2025-12-16 13:11:46', '2025-12-16 13:12:55'),
(7, 2, NULL, '2026-01-14', '2026-02-12', 1, '2026-01', '2025-12-16 14:11:16', '2025-12-16 14:11:16'),
(8, 2, NULL, '2026-01-14', '2026-02-12', 1, '2026-02', '2025-12-16 14:11:16', '2025-12-16 14:11:16'),
(9, 21, 13, '2025-11-24', '2025-12-16', 1, '2025-12', '2025-12-16 16:07:36', '2025-12-16 16:29:16'),
(10, 14, 12, '2025-12-10', '2025-12-16', 1, '2025-12', '2025-12-16 16:07:59', '2025-12-16 16:07:59'),
(11, 24, 14, '2025-12-08', '2026-01-19', 1, '2025-12', '2025-12-16 16:09:23', '2025-12-16 16:09:23'),
(12, 24, 14, '2025-12-08', '2026-01-19', 1, '2026-01', '2025-12-16 16:09:23', '2025-12-16 16:09:23'),
(13, 23, 10, '2025-12-08', '2026-01-05', 1, '2025-12', '2025-12-16 16:28:48', '2025-12-16 16:28:48'),
(14, 23, 10, '2025-12-08', '2026-01-05', 1, '2026-01', '2025-12-16 16:28:48', '2025-12-16 16:28:48'),
(15, 16, 11, '2025-12-11', '2026-01-08', 1, '2025-12', '2025-12-16 16:30:17', '2025-12-16 16:30:17'),
(16, 16, 11, '2025-12-11', '2026-01-08', 1, '2026-01', '2025-12-16 16:30:17', '2025-12-16 16:30:17'),
(17, 6, 15, '2025-12-18', '2026-01-29', 1, '2025-12', '2025-12-19 08:15:10', '2025-12-19 08:15:10'),
(18, 6, 15, '2025-12-18', '2026-01-29', 1, '2026-01', '2025-12-19 08:15:10', '2025-12-19 08:15:10');

-- --------------------------------------------------------

--
-- 表的结构 `score_card_records`
--

CREATE TABLE `score_card_records` (
  `record_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT '客户ID',
  `card_number` int(11) NOT NULL COMMENT '第几张评分卡',
  `record_date` date NOT NULL COMMENT '记录日期',
  `score_data` json DEFAULT NULL COMMENT '评分数据',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `score_card_records`
--

INSERT INTO `score_card_records` (`record_id`, `customer_id`, `card_number`, `record_date`, `score_data`, `created_at`) VALUES
(4, 13, 1, '2025-11-24', '{}', '2025-12-19 15:48:04'),
(5, 13, 2, '2025-11-30', '{}', '2025-12-19 15:48:12'),
(6, 13, 3, '2025-12-07', '{}', '2025-12-19 15:48:15'),
(7, 14, 1, '2025-12-08', '{}', '2025-12-19 15:50:49'),
(8, 14, 2, '2025-12-14', '{}', '2025-12-19 15:50:52'),
(9, 10, 1, '2025-12-08', '{}', '2025-12-19 15:50:58'),
(10, 10, 2, '2025-12-14', '{}', '2025-12-19 15:51:00'),
(11, 12, 1, '2025-12-10', '{}', '2025-12-19 15:51:04'),
(12, 12, 2, '2025-12-14', '{}', '2025-12-19 15:51:06'),
(13, 11, 1, '2025-12-11', '{}', '2025-12-19 15:51:10'),
(14, 11, 2, '2025-12-17', '{}', '2025-12-19 15:51:12'),
(15, 15, 1, '2025-12-18', '{}', '2025-12-19 15:51:17'),
(16, 16, 1, '2025-12-19', '{}', '2025-12-19 15:51:29');

-- --------------------------------------------------------

--
-- 表的结构 `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `address_id` int(11) NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户电话',
  `receiver_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收货人姓名',
  `receiver_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收货人电话',
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详细地址',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认地址 (0=否, 1=是)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址表';

--
-- 转存表中的数据 `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`address_id`, `customer_phone`, `receiver_name`, `receiver_phone`, `address`, `is_default`, `created_at`, `updated_at`) VALUES
(1, '13800138000', '312324124多少啊', '18790688888', '321', 1, '2025-12-22 13:34:18', '2025-12-22 13:34:18');

-- --------------------------------------------------------

--
-- 表的结构 `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户电话',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品名称',
  `price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `quantity` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品图片',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车表';

--
-- 转存表中的数据 `shopping_cart`
--

INSERT INTO `shopping_cart` (`cart_id`, `customer_phone`, `product_id`, `product_name`, `price`, `quantity`, `image_url`, `created_at`, `updated_at`) VALUES
(1, '13800138000', 5, '测试', '0.01', 1, '/uploads/products/product_69465e2017b1e5.28706975.png', '2025-12-22 13:34:28', '2025-12-22 13:34:28');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '手机号（登录账号）',
  `openid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '微信openid',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户昵称',
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像URL',
  `points_balance` int(11) DEFAULT '0' COMMENT '积分余额',
  `last_selected_address_id` int(11) DEFAULT NULL COMMENT '最后选择的收货地址ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户信息表';

--
-- 转储表的索引
--

--
-- 表的索引 `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `idx_username` (`username`);

--
-- 表的索引 `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_check_in` (`check_in_date`),
  ADD KEY `idx_customer_name` (`customer_name`);

--
-- 表的索引 `family_meals`
--
ALTER TABLE `family_meals`
  ADD PRIMARY KEY (`meal_id`);

--
-- 表的索引 `family_meal_orders`
--
ALTER TABLE `family_meal_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_room_name` (`room_name`),
  ADD KEY `idx_meal_date` (`meal_date`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_out_trade_no` (`out_trade_no`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

--
-- 表的索引 `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`goods_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- 表的索引 `orders_unified`
--
ALTER TABLE `orders_unified`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `customer_phone` (`customer_phone`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `created_at` (`created_at`);

--
-- 表的索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- 表的索引 `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`customer_phone`);

--
-- 表的索引 `points_log`
--
ALTER TABLE `points_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- 表的索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `idx_floor` (`floor`),
  ADD KEY `idx_ac_group` (`ac_group_id`);

--
-- 表的索引 `room_status`
--
ALTER TABLE `room_status`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `idx_room_month` (`room_id`,`record_month`),
  ADD KEY `idx_month` (`record_month`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- 表的索引 `score_card_records`
--
ALTER TABLE `score_card_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_card_number` (`card_number`);

--
-- 表的索引 `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- 表的索引 `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `uk_customer_product` (`customer_phone`,`product_id`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_openid` (`openid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID', AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- 使用表AUTO_INCREMENT `family_meals`
--
ALTER TABLE `family_meals`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '配餐ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `family_meal_orders`
--
ALTER TABLE `family_meal_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID', AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `goods`
--
ALTER TABLE `goods`
  MODIFY `goods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID', AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `orders_unified`
--
ALTER TABLE `orders_unified`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID', AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单项ID';

--
-- 使用表AUTO_INCREMENT `points_log`
--
ALTER TABLE `points_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- 使用表AUTO_INCREMENT `room_status`
--
ALTER TABLE `room_status`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用表AUTO_INCREMENT `score_card_records`
--
ALTER TABLE `score_card_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `room_status`
--
ALTER TABLE `room_status`
  ADD CONSTRAINT `room_status_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_status_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL;

--
-- 限制表 `score_card_records`
--
ALTER TABLE `score_card_records`
  ADD CONSTRAINT `score_card_records_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
