-- ============================================================
-- 瑷婴月子中心 - 商城系统测试数据
-- 生成时间：2025-12-22
-- 说明：包含用户、商品、订单、套餐等测试数据
-- ============================================================

USE `aiying_health`;

-- ============================================================
-- 1. 测试用户数据 (10个用户)
-- ============================================================
INSERT INTO `users` (`openid`, `phone`, `name`, `avatar`, `gender`, `points_balance`, `last_login_at`, `status`, `created_at`, `updated_at`) VALUES
('wx_test_user_001', '13800138001', '张小美', 'https://via.placeholder.com/150/FFB6C1/FFF?text=ZXM', 2, 500, NOW(), 1, NOW(), NOW()),
('wx_test_user_002', '13800138002', '李优雅', 'https://via.placeholder.com/150/DDA0DD/FFF?text=LYY', 2, 320, NOW(), 1, NOW(), NOW()),
('wx_test_user_003', '13800138003', '王美丽', 'https://via.placeholder.com/150/98FB98/FFF?text=WML', 2, 150, NOW(), 1, NOW(), NOW()),
('wx_test_user_004', '13800138004', '刘温柔', 'https://via.placeholder.com/150/87CEEB/FFF?text=LWR', 2, 0, NOW(), 1, NOW(), NOW()),
('wx_test_user_005', '13800138005', '陈静雅', 'https://via.placeholder.com/150/F0E68C/FFF?text=CJY', 2, 820, NOW(), 1, NOW(), NOW()),
('wx_test_user_006', '13800138006', '赵婉儿', 'https://via.placeholder.com/150/FFA07A/FFF?text=ZWE', 2, 200, NOW(), 1, NOW(), NOW()),
('wx_test_user_007', '13800138007', '孙慧敏', 'https://via.placeholder.com/150/DB7093/FFF?text=SHM', 2, 680, NOW(), 1, NOW(), NOW()),
('wx_test_user_008', '13800138008', '周雅婷', 'https://via.placeholder.com/150/FFE4B5/FFF?text=ZYT', 2, 0, NOW(), 1, NOW(), NOW()),
('wx_test_user_009', '13800138009', '吴秀丽', 'https://via.placeholder.com/150/E0FFFF/FFF?text=WXL', 2, 1200, NOW(), 1, NOW(), NOW()),
('wx_test_user_010', '13800138010', '郑玉兰', 'https://via.placeholder.com/150/FFDAB9/FFF?text=ZYL', 2, 450, NOW(), 1, NOW(), NOW());

-- ============================================================
-- 2. 商品数据 (20个商品)
-- ============================================================

-- 分类1: 医疗器械 (7个商品)
INSERT INTO `products` (`category_id`, `name`, `cover_image`, `original_price`, `price`, `stock`, `sales`, `unit`, `summary`, `description`, `tech_params`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '电动吸奶器', 'https://via.placeholder.com/400x400/FF6B9D/FFF?text=吸奶器', 268.00, 158.00, 50, 156, '件', '医用级静音吸奶器，舒适高效', '<p>产品特点：<br>1. 医用级材质，安全无毒<br>2. 超静音设计，使用更舒适<br>3. 9档吸力调节，适合不同需求<br>4. 可充电设计，方便携带</p>', '{"品牌":"美德乐","产地":"瑞士","功率":"5W","电池容量":"1800mAh","档位":"9档","适用人群":"哺乳期妈妈"}', 1, 1, NOW(), NOW()),
(1, '红外线体温计', 'https://via.placeholder.com/400x400/87CEEB/FFF?text=体温计', 159.00, 89.00, 100, 328, '件', '非接触式红外测温，1秒快速测温', '<p>产品优势：<br>1. 非接触式测温，更卫生<br>2. 1秒快速测温，准确可靠<br>3. 高清LCD显示，夜间可视<br>4. 32组数据记忆</p>', '{"品牌":"博朗","测温范围":"32-42.9℃","精度":"±0.2℃","测温距离":"3-5cm","显示":"LCD液晶"}', 2, 1, NOW(), NOW()),
(1, '婴儿雾化器', 'https://via.placeholder.com/400x400/98FB98/FFF?text=雾化器', 358.00, 218.00, 35, 89, '台', '静音设计雾化器，宝宝不哭闹', '<p>功能特点：<br>1. 超静音设计<50dB<br>2. 雾化颗粒细腻<5μm<br>3. 雾化速度快≥0.25ml/min<br>4. 医用材质，安全可靠</p>', '{"品牌":"欧姆龙","雾化颗粒":"≤5μm","雾化速度":"≥0.25ml/min","噪音":"<50dB","容量":"12ml"}', 3, 1, NOW(), NOW()),
(1, '婴儿理发器', 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=理发器', 129.00, 79.00, 80, 245, '套', '静音防水理发器，宝宝专用', '<p>产品特色：<br>1. R型钝角陶瓷刀头，不伤头皮<br>2. 超低噪音<45dB<br>3. IPX7防水可水洗<br>4. 续航时间长达90分钟</p>', '{"品牌":"飞利浦","刀头材质":"陶瓷","防水等级":"IPX7","噪音":"<45dB","续航":"90分钟"}', 4, 1, NOW(), NOW()),
(1, '智能婴儿秤', 'https://via.placeholder.com/400x400/F0E68C/FFF?text=婴儿秤', 199.00, 128.00, 45, 167, '台', '高精度婴儿体重秤，记录成长', '<p>智能功能：<br>1. 精度10g，精准测量<br>2. 蓝牙连接APP，记录成长曲线<br>3. 智能去皮功能<br>4. 承重范围0-20kg</p>', '{"品牌":"香山","精度":"10g","最大称重":"20kg","显示":"LED","连接":"蓝牙4.0"}', 5, 1, NOW(), NOW()),
(1, '医用酒精消毒液', 'https://via.placeholder.com/400x400/FFB6C1/FFF?text=消毒液', 39.00, 26.00, 200, 521, '瓶', '75%医用酒精，居家消毒必备', '<p>产品说明：<br>1. 医用级75%酒精浓度<br>2. 有效杀灭99.9%细菌病毒<br>3. 500ml大容量<br>4. 适用于皮肤、物品表面消毒</p>', '{"浓度":"75%","规格":"500ml","成分":"医用乙醇","保质期":"2年","用途":"消毒杀菌"}', 6, 1, NOW(), NOW()),
(1, '婴儿指甲剪套装', 'https://via.placeholder.com/400x400/FFA07A/FFF?text=指甲剪', 49.00, 29.00, 150, 412, '套', '婴儿专用指甲护理套装', '<p>套装包含：<br>1. 安全指甲剪×1<br>2. 指甲锉×1<br>3. 指甲钳×1<br>4. 便携收纳盒×1<br>圆润防刮设计，保护宝宝稚嫩肌肤</p>', '{"品牌":"贝亲","材质":"不锈钢+ABS","件数":"4件套","适用":"0-3岁","特点":"圆润防刮"}', 7, 1, NOW(), NOW());

-- 分类2: 母婴用品 (8个商品)
INSERT INTO `products` (`category_id`, `name`, `cover_image`, `original_price`, `price`, `stock`, `sales`, `unit`, `summary`, `description`, `tech_params`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(2, '新生儿纸尿裤', 'https://via.placeholder.com/400x400/87CEEB/FFF?text=纸尿裤', 168.00, 118.00, 200, 856, '包', '超薄透气纸尿裤，12小时干爽', '<p>产品优势：<br>1. 日本进口绒毛浆，柔软亲肤<br>2. 3D立体剪裁，防侧漏<br>3. 超薄设计，透气不闷<br>4. 弱酸性表层，保护肌肤</p>', '{"品牌":"花王","型号":"NB码","数量":"90片","适用体重":"0-5kg","吸水量":"≥400ml"}', 8, 1, NOW(), NOW()),
(2, '婴儿湿巾', 'https://via.placeholder.com/400x400/98FB98/FFF?text=湿巾', 79.00, 49.00, 300, 1024, '箱', '无香型婴儿湿巾，温和清洁', '<p>安全配方：<br>1. 99%纯水配方<br>2. 无酒精、无香精<br>3. 加厚加大设计<br>4. 密封盖设计，保持湿润</p>', '{"品牌":"全棉时代","规格":"80抽×10包","成分":"纯水99%+植物提取物","材质":"100%棉","pH值":"5.5弱酸性"}', 9, 1, NOW(), NOW()),
(2, '婴儿玻璃奶瓶', 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=奶瓶', 128.00, 79.00, 120, 389, '个', '宽口径玻璃奶瓶，防胀气设计', '<p>产品特点：<br>1. 高硼硅玻璃材质，耐高温<br>2. 宽口径设计，易清洗<br>3. 防胀气奶嘴<br>4. 容量刻度清晰</p>', '{"品牌":"新安怡","容量":"240ml","材质":"高硼硅玻璃","奶嘴":"硅胶防胀气","耐温":"120℃"}', 10, 1, NOW(), NOW()),
(2, '婴儿洗护套装', 'https://via.placeholder.com/400x400/F0E68C/FFF?text=洗护', 259.00, 168.00, 85, 234, '套', '天然植物配方洗护套装', '<p>套装包含：<br>1. 婴儿沐浴露500ml<br>2. 婴儿洗发水500ml<br>3. 婴儿润肤乳500ml<br>4. 婴儿护臀膏100g<br>天然植物萃取，温和不刺激</p>', '{"品牌":"强生","件数":"4件套","配方":"无泪配方","成分":"天然植物提取","pH值":"5.5"}', 11, 1, NOW(), NOW()),
(2, '婴儿抱被', 'https://via.placeholder.com/400x400/FFB6C1/FFF?text=抱被', 189.00, 99.00, 100, 312, '条', '纯棉纱布抱被，四季通用', '<p>产品优势：<br>1. A类婴幼儿纱布面料<br>2. 6层加厚设计<br>3. 透气吸湿，越洗越软<br>4. 90×90cm大尺寸</p>', '{"品牌":"良良","材质":"100%纯棉纱布","层数":"6层","尺寸":"90×90cm","安全等级":"A类"}', 12, 1, NOW(), NOW()),
(2, '婴儿防踢被', 'https://via.placeholder.com/400x400/FFA07A/FFF?text=防踢被', 138.00, 89.00, 90, 267, '件', '防踢被睡袋，宝宝睡得香', '<p>设计特点：<br>1. 分腿式设计，活动自如<br>2. 双向拉链，方便换尿布<br>3. 纯棉内里，柔软亲肤<br>4. 四季可用</p>', '{"品牌":"迪士尼","材质":"纯棉+聚酯纤维","尺寸":"L码(90cm)","适用季节":"春秋冬","适用月龄":"6-36个月"}', 13, 1, NOW(), NOW()),
(2, '婴儿口水巾', 'https://via.placeholder.com/400x400/DB7093/FFF?text=口水巾', 59.00, 35.00, 200, 523, '套', '纯棉纱布口水巾，柔软吸水', '<p>套装特色：<br>1. 8条装，足够换洗<br>2. 六层纱布，吸水性强<br>3. 纯棉A类面料<br>4. 可爱印花图案</p>', '{"品牌":"小米米","材质":"100%纯棉","层数":"6层","尺寸":"25×25cm","数量":"8条装"}', 14, 1, NOW(), NOW()),
(2, '婴儿洗澡盆', 'https://via.placeholder.com/400x400/E0FFFF/FFF?text=洗澡盆', 129.00, 78.00, 65, 198, '个', '可折叠婴儿浴盆，省空间', '<p>实用功能：<br>1. 可折叠设计，节省空间<br>2. 防滑底座，使用安全<br>3. 温度计插槽<br>4. 加大加厚，0-6岁适用</p>', '{"品牌":"世纪宝贝","材质":"PP+TPE","尺寸":"84×50×25cm","折叠高度":"10cm","承重":"25kg"}', 15, 1, NOW(), NOW());

-- 分类3: 产康服务 (5个商品)
INSERT INTO `products` (`category_id`, `name`, `cover_image`, `original_price`, `price`, `stock`, `sales`, `unit`, `summary`, `description`, `tech_params`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(3, '产后康复理疗', 'https://via.placeholder.com/400x400/FF6B9D/FFF?text=康复', 1280.00, 980.00, 20, 45, '次', '专业产后康复理疗服务', '<p>服务内容：<br>1. 盆底肌修复理疗<br>2. 腹直肌分离修复<br>3. 子宫复旧按摩<br>4. 专业理疗师一对一服务<br>疗程：60分钟/次</p>', '{"服务时长":"60分钟","理疗师":"高级康复师","设备":"进口理疗仪","场所":"VIP理疗室","预约":"提前1天"}', 16, 1, NOW(), NOW()),
(3, '满月发汗服务', 'https://via.placeholder.com/400x400/87CEEB/FFF?text=发汗', 388.00, 288.00, 30, 78, '次', '传统满月发汗，排湿排毒', '<p>服务特色：<br>1. 中药熏蒸配方<br>2. 专业发汗房<br>3. 全程护士陪护<br>4. 发汗后休息调理<br>时长：90分钟</p>', '{"服务时长":"90分钟","配方":"传统中药","温度":"38-42℃","护理":"专业护士","适用":"产后30天"}', 17, 1, NOW(), NOW()),
(3, '催乳通乳服务', 'https://via.placeholder.com/400x400/98FB98/FFF?text=催乳', 580.00, 480.00, 25, 92, '次', '专业催乳师，解决哺乳问题', '<p>服务范围：<br>1. 开奶/催乳<br>2. 乳腺疏通<br>3. 堵奶处理<br>4. 哺乳指导<br>高级催乳师上门服务</p>', '{"服务时长":"45分钟","催乳师":"高级催乳师","手法":"无痛按摩","效果":"当次见效","上门":"可上门"}', 18, 1, NOW(), NOW()),
(3, '小儿推拿', 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=推拿', 298.00, 198.00, 30, 156, '次', '小儿推拿保健，增强体质', '<p>适用症状：<br>1. 消化不良、积食<br>2. 感冒咳嗽预防<br>3. 睡眠不安<br>4. 免疫力提升<br>专业小儿推拿师</p>', '{"服务时长":"30分钟","推拿师":"高级小儿推拿师","手法":"中医推拿","适用年龄":"0-6岁","预约":"提前预约"}', 19, 1, NOW(), NOW()),
(3, '产后瑜伽课程', 'https://via.placeholder.com/400x400/F0E68C/FFF?text=瑜伽', 1680.00, 1280.00, 15, 34, '套', '产后修复瑜伽，恢复身材', '<p>课程内容：<br>1. 10节课程（60分钟/节）<br>2. 盆底肌修复<br>3. 核心力量训练<br>4. 形体恢复<br>专业瑜伽教练小班授课</p>', '{"课程数":"10节","时长":"60分钟/节","教练":"资深瑜伽师","人数":"小班6人","场地":"专业瑜伽室"}', 20, 1, NOW(), NOW());

-- ============================================================
-- 3. 商品图片数据
-- ============================================================
INSERT INTO `product_images` (`product_id`, `image_url`, `sort_order`, `created_at`, `updated_at`)
SELECT id, CONCAT('https://via.placeholder.com/800x800/',
  CASE
    WHEN id % 6 = 1 THEN 'FF6B9D'
    WHEN id % 6 = 2 THEN '87CEEB'
    WHEN id % 6 = 3 THEN '98FB98'
    WHEN id % 6 = 4 THEN 'DDA0DD'
    WHEN id % 6 = 5 THEN 'F0E68C'
    ELSE 'FFB6C1'
  END,
  '/FFF?text=Detail', num), num, NOW(), NOW()
FROM products
CROSS JOIN (SELECT 1 AS num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) AS numbers
WHERE products.id BETWEEN 1 AND 20;

-- ============================================================
-- 4. 商品规格SKU数据 (纸尿裤、奶瓶、防踢被有规格)
-- ============================================================

-- 纸尿裤规格 (商品ID=8)
INSERT INTO `product_specifications` (`product_id`, `sku_code`, `spec_values`, `price`, `stock`, `image`, `status`, `created_at`, `updated_at`) VALUES
(8, 'NB-90P', '{"尺码":"NB码","数量":"90片","适用体重":"0-5kg"}', 118.00, 80, 'https://via.placeholder.com/400x400/87CEEB/FFF?text=NB', 1, NOW(), NOW()),
(8, 'S-84P', '{"尺码":"S码","数量":"84片","适用体重":"4-8kg"}', 128.00, 60, 'https://via.placeholder.com/400x400/87CEEB/FFF?text=S', 1, NOW(), NOW()),
(8, 'M-76P', '{"尺码":"M码","数量":"76片","适用体重":"6-11kg"}', 138.00, 50, 'https://via.placeholder.com/400x400/87CEEB/FFF?text=M', 1, NOW(), NOW());

-- 奶瓶规格 (商品ID=10)
INSERT INTO `product_specifications` (`product_id`, `sku_code`, `spec_values`, `price`, `stock`, `image`, `status`, `created_at`, `updated_at`) VALUES
(10, 'BOTTLE-120ML', '{"容量":"120ml","材质":"玻璃"}', 69.00, 40, 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=120ml', 1, NOW(), NOW()),
(10, 'BOTTLE-240ML', '{"容量":"240ml","材质":"玻璃"}', 79.00, 50, 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=240ml', 1, NOW(), NOW()),
(10, 'BOTTLE-330ML', '{"容量":"330ml","材质":"玻璃"}', 89.00, 30, 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=330ml', 1, NOW(), NOW());

-- 防踢被规格 (商品ID=13)
INSERT INTO `product_specifications` (`product_id`, `sku_code`, `spec_values`, `price`, `stock`, `image`, `status`, `created_at`, `updated_at`) VALUES
(13, 'SLEEPING-M', '{"尺寸":"M码(70cm)","适用月龄":"0-12个月"}', 79.00, 30, 'https://via.placeholder.com/400x400/FFA07A/FFF?text=M', 1, NOW(), NOW()),
(13, 'SLEEPING-L', '{"尺寸":"L码(90cm)","适用月龄":"6-36个月"}', 89.00, 40, 'https://via.placeholder.com/400x400/FFA07A/FFF?text=L', 1, NOW(), NOW()),
(13, 'SLEEPING-XL', '{"尺寸":"XL码(110cm)","适用月龄":"2-6岁"}', 99.00, 20, 'https://via.placeholder.com/400x400/FFA07A/FFF?text=XL', 1, NOW(), NOW());

-- ============================================================
-- 5. 家庭套餐数据
-- ============================================================
INSERT INTO `family_meal_packages` (`name`, `cover_image`, `price`, `duration_days`, `description`, `services`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('基础月子套餐', 'https://via.placeholder.com/600x400/FF6B9D/FFF?text=基础套餐', 19800.00, 28, '28天基础月子护理服务', '["专业月嫂24小时护理","营养月子餐（一日三餐三点）","产妇护理（伤口护理、恶露观察）","新生儿护理（洗澡、抚触、脐带护理）","每日房间清洁","产后康复咨询"]', 1, 1, NOW(), NOW()),
('标准月子套餐', 'https://via.placeholder.com/600x400/87CEEB/FFF?text=标准套餐', 28800.00, 28, '28天标准月子护理服务', '["金牌月嫂24小时护理","高级营养月子餐（一日五餐）","产妇全面护理","新生儿专业护理","每日两次房间清洁","产后康复理疗3次","催乳服务2次","满月发汗1次","宝宝满月照"]', 2, 1, NOW(), NOW()),
('豪华月子套餐', 'https://via.placeholder.com/600x400/98FB98/FFF?text=豪华套餐', 39800.00, 42, '42天豪华月子护理服务', '["高级月嫂+护士24小时护理","尊享营养月子餐（一日六餐）","VIP产妇护理","新生儿全方位护理","每日三次房间清洁","产后康复理疗8次","催乳服务5次","满月发汗2次","产后瑜伽课程5节","小儿推拿3次","宝宝满月照+百日照","家属陪护餐"]', 3, 1, NOW(), NOW()),
('至尊月子套餐', 'https://via.placeholder.com/600x400/DDA0DD/FFF?text=至尊套餐', 58800.00, 56, '56天至尊月子护理服务', '["资深月嫂+专业护士+营养师24小时护理","定制营养月子餐（一日六餐）","VIP专属产妇护理","新生儿高端护理","每日四次房间清洁","产后康复理疗15次","催乳服务不限次","满月发汗3次","产后瑜伽课程10节","小儿推拿8次","产后修复课程","心理咨询服务","宝宝满月照+百日照+专业摄影","家属陪护餐","接送服务"]', 4, 1, NOW(), NOW());

-- ============================================================
-- 6. 收货地址数据
-- ============================================================
INSERT INTO `shipping_addresses` (`user_id`, `name`, `phone`, `province`, `city`, `district`, `detail`, `is_default`, `created_at`, `updated_at`) VALUES
(1, '张小美', '13800138001', '山东省', '青岛市', '市南区', '香港中路88号阳光大厦2单元1502', 1, NOW(), NOW()),
(2, '李优雅', '13800138002', '山东省', '青岛市', '崂山区', '海尔路178号海景花园A座803', 1, NOW(), NOW()),
(3, '王美丽', '13800138003', '山东省', '青岛市', '李沧区', '金水路368号碧桂园3号楼1单元602', 1, NOW(), NOW()),
(5, '陈静雅', '13800138005', '山东省', '青岛市', '市北区', '辽宁路228号万科城市花园5栋2002', 1, NOW(), NOW()),
(7, '孙慧敏', '13800138007', '山东省', '青岛市', '黄岛区', '长江中路520号保利叶公馆8号楼1单元1201', 1, NOW(), NOW()),
(9, '吴秀丽', '13800138009', '山东省', '青岛市', '城阳区', '正阳路298号中海御城6号楼2单元901', 1, NOW(), NOW());

-- ============================================================
-- 7. 订单数据 (5个订单，不同状态)
-- ============================================================

-- 订单1: 已完成订单 (用户1)
INSERT INTO `orders` (`order_no`, `user_id`, `order_type`, `receiver_name`, `receiver_phone`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_detail`, `goods_amount`, `shipping_fee`, `points_used`, `points_discount`, `total_amount`, `order_status`, `payment_status`, `remark`, `paid_at`, `shipped_at`, `completed_at`, `created_at`, `updated_at`) VALUES
('ORD202512220001', 1, 'goods', '张小美', '13800138001', '山东省', '青岛市', '市南区', '香港中路88号阳光大厦2单元1502', 276.00, 10.00, 100, 10.00, 276.00, 3, 1, '请尽快发货，谢谢', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 13 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- 订单1的商品明细
INSERT INTO `order_items` (`order_id`, `product_id`, `sku_id`, `product_name`, `product_image`, `sku_name`, `price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '电动吸奶器', 'https://via.placeholder.com/400x400/FF6B9D/FFF?text=吸奶器', NULL, 158.00, 1, 158.00, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 9, NULL, '婴儿湿巾', 'https://via.placeholder.com/400x400/98FB98/FFF?text=湿巾', NULL, 49.00, 1, 49.00, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 7, NULL, '婴儿指甲剪套装', 'https://via.placeholder.com/400x400/FFA07A/FFF?text=指甲剪', NULL, 29.00, 1, 29.00, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 14, NULL, '婴儿口水巾', 'https://via.placeholder.com/400x400/DB7093/FFF?text=口水巾', NULL, 35.00, 1, 35.00, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

-- 订单2: 待发货订单 (用户2)
INSERT INTO `orders` (`order_no`, `user_id`, `order_type`, `receiver_name`, `receiver_phone`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_detail`, `goods_amount`, `shipping_fee`, `points_used`, `points_discount`, `total_amount`, `order_status`, `payment_status`, `remark`, `paid_at`, `created_at`, `updated_at`) VALUES
('ORD202512220002', 2, 'goods', '李优雅', '13800138002', '山东省', '青岛市', '崂山区', '海尔路178号海景花园A座803', 386.00, 0.00, 0, 0.00, 386.00, 1, 1, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());

-- 订单2的商品明细
INSERT INTO `order_items` (`order_id`, `product_id`, `sku_id`, `product_name`, `product_image`, `sku_name`, `price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(2, 3, NULL, '婴儿雾化器', 'https://via.placeholder.com/400x400/98FB98/FFF?text=雾化器', NULL, 218.00, 1, 218.00, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 11, NULL, '婴儿洗护套装', 'https://via.placeholder.com/400x400/F0E68C/FFF?text=洗护', NULL, 168.00, 1, 168.00, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- 订单3: 待收货订单 (用户5)
INSERT INTO `orders` (`order_no`, `user_id`, `order_type`, `receiver_name`, `receiver_phone`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_detail`, `goods_amount`, `shipping_fee`, `points_used`, `points_discount`, `total_amount`, `order_status`, `payment_status`, `paid_at`, `shipped_at`, `created_at`, `updated_at`) VALUES
('ORD202512220003', 5, 'goods', '陈静雅', '13800138005', '山东省', '青岛市', '市北区', '辽宁路228号万科城市花园5栋2002', 543.00, 10.00, 200, 20.00, 533.00, 2, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), NOW());

-- 订单3的商品明细
INSERT INTO `order_items` (`order_id`, `product_id`, `sku_id`, `product_name`, `product_image`, `sku_name`, `price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(3, 8, 2, '新生儿纸尿裤', 'https://via.placeholder.com/400x400/87CEEB/FFF?text=S', 'S码 84片 4-8kg', 128.00, 2, 256.00, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 10, 5, '婴儿玻璃奶瓶', 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=240ml', '240ml 玻璃', 79.00, 2, 158.00, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 5, NULL, '智能婴儿秤', 'https://via.placeholder.com/400x400/F0E68C/FFF?text=婴儿秤', NULL, 128.00, 1, 128.00, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 订单4: 待支付订单 (用户3)
INSERT INTO `orders` (`order_no`, `user_id`, `order_type`, `receiver_name`, `receiver_phone`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_detail`, `goods_amount`, `shipping_fee`, `points_used`, `points_discount`, `total_amount`, `order_status`, `payment_status`, `created_at`, `updated_at`) VALUES
('ORD202512220004', 3, 'goods', '王美丽', '13800138003', '山东省', '青岛市', '李沧区', '金水路368号碧桂园3号楼1单元602', 295.00, 10.00, 0, 0.00, 305.00, 0, 0, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW());

-- 订单4的商品明细
INSERT INTO `order_items` (`order_id`, `product_id`, `sku_id`, `product_name`, `product_image`, `sku_name`, `price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(4, 2, NULL, '红外线体温计', 'https://via.placeholder.com/400x400/87CEEB/FFF?text=体温计', NULL, 89.00, 1, 89.00, DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(4, 12, NULL, '婴儿抱被', 'https://via.placeholder.com/400x400/FFB6C1/FFF?text=抱被', NULL, 99.00, 2, 198.00, DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- 订单5: 已取消订单 (用户7)
INSERT INTO `orders` (`order_no`, `user_id`, `order_type`, `receiver_name`, `receiver_phone`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_detail`, `goods_amount`, `shipping_fee`, `points_used`, `points_discount`, `total_amount`, `order_status`, `payment_status`, `remark`, `cancelled_at`, `created_at`, `updated_at`) VALUES
('ORD202512220005', 7, 'goods', '孙慧敏', '13800138007', '山东省', '青岛市', '黄岛区', '长江中路520号保利叶公馆8号楼1单元1201', 167.00, 10.00, 0, 0.00, 177.00, 4, 0, '暂时不需要了', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- 订单5的商品明细
INSERT INTO `order_items` (`order_id`, `product_id`, `sku_id`, `product_name`, `product_image`, `sku_name`, `price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(5, 13, 5, '婴儿防踢被', 'https://via.placeholder.com/400x400/FFA07A/FFF?text=L', 'L码(90cm) 6-36个月', 89.00, 1, 89.00, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 4, NULL, '婴儿理发器', 'https://via.placeholder.com/400x400/DDA0DD/FFF?text=理发器', NULL, 79.00, 1, 79.00, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================================
-- 8. 积分历史数据
-- ============================================================

-- 用户1的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 'earn', 400, 500, 'order', 1, '订单完成奖励积分', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 'spend', -100, 400, 'order', 1, '订单使用积分抵扣', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'earn', 100, 500, 'sign_in', NULL, '连续签到7天奖励', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 用户2的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(2, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(2, 'earn', 220, 320, 'order', 2, '待发货订单预分配积分', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- 用户3的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(3, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(3, 'earn', 50, 150, 'share', NULL, '分享商品奖励', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

-- 用户5的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(5, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(5, 'earn', 920, 1020, 'order', 3, '历史订单积分累积', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 'spend', -200, 820, 'order', 3, '订单使用积分抵扣', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 用户7的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(7, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(7, 'earn', 580, 680, 'order', NULL, '历史订单积分', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY));

-- 用户9的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(9, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 90 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(9, 'earn', 1100, 1200, 'order', NULL, 'VIP客户历史订单积分', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY));

-- 用户10的积分记录
INSERT INTO `points_history` (`user_id`, `type`, `points`, `balance_after`, `source`, `source_id`, `description`, `created_at`, `updated_at`) VALUES
(10, 'earn', 100, 100, 'register', NULL, '新用户注册赠送', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(10, 'earn', 350, 450, 'order', NULL, '订单完成积分奖励', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ============================================================
-- 9. 购物车数据
-- ============================================================
INSERT INTO `shopping_cart` (`user_id`, `product_id`, `sku_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 16, NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW()),
(1, 17, NULL, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
(3, 8, 1, 2, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW()),
(3, 15, NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW()),
(4, 2, NULL, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), NOW()),
(4, 9, NULL, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
(6, 11, NULL, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW()),
(8, 18, NULL, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW());

-- ============================================================
-- 数据导入完成
-- ============================================================
-- 测试数据统计：
-- - 10个测试用户
-- - 20个商品（3个分类）
-- - 80张商品图片（每个商品4张）
-- - 9个SKU规格
-- - 4个家庭套餐
-- - 6个收货地址
-- - 5个订单（不同状态：已完成、待发货、待收货、待支付、已取消）
-- - 13条订单商品明细
-- - 15条积分历史记录
-- - 8条购物车记录
-- ============================================================
