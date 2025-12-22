# 数据库表结构文档

## 1. orders (订单表)

| 字段名 | 类型 | 是否必填 | 默认值 | 说明 |
|--------|------|----------|--------|------|
| `id` | bigint unsigned | 是 | - | 主键,自增 |
| `order_no` | varchar(255) | 是 | - | 订单号,唯一 |
| `user_id` | bigint unsigned | 是 | - | 用户ID |
| `order_type` | enum('goods','family_meal') | 是 | - | 订单类型:goods=商品订单,family_meal=家属套餐 |
| `room_number` | varchar(50) | 否 | NULL | 房间号(仅家属套餐使用) |
| `receiver_name` | varchar(255) | 否 | '' | 收货人姓名 |
| `receiver_phone` | varchar(255) | 否 | '' | 收货人电话 |
| `receiver_province` | varchar(255) | 否 | '' | 省份 |
| `receiver_city` | varchar(255) | 否 | '' | 城市 |
| `receiver_district` | varchar(255) | 否 | '' | 区县 |
| `receiver_detail` | varchar(255) | 否 | '' | 详细地址 |
| `goods_amount` | decimal(10,2) | 是 | 0.00 | 商品总额 |
| `shipping_fee` | decimal(10,2) | 是 | 0.00 | 运费 |
| `points_used` | int | 是 | 0 | 使用积分数 |
| `points_discount` | decimal(10,2) | 是 | 0.00 | 积分抵扣金额 |
| `total_amount` | decimal(10,2) | 是 | - | 订单总额 |
| `order_status` | tinyint | 是 | 0 | 订单状态:0=待支付,1=待发货,2=待收货,3=已完成,4=已取消 |
| `payment_status` | tinyint | 是 | 0 | 支付状态:0=未支付,1=已支付 |
| `remark` | text | 否 | NULL | 备注 |
| `paid_at` | timestamp | 否 | NULL | 支付时间 |
| `shipped_at` | timestamp | 否 | NULL | 发货时间 |
| `completed_at` | timestamp | 否 | NULL | 完成时间 |
| `cancelled_at` | timestamp | 否 | NULL | 取消时间 |
| `created_at` | timestamp | 否 | NULL | 创建时间 |
| `updated_at` | timestamp | 否 | NULL | 更新时间 |
| `deleted_at` | timestamp | 否 | NULL | 软删除时间 |

## 2. order_items (订单明细表)

| 字段名 | 类型 | 是否必填 | 说明 |
|--------|------|----------|------|
| `id` | bigint unsigned | 是 | 主键,自增 |
| `order_id` | bigint unsigned | 是 | 订单ID |
| `product_id` | bigint unsigned | 是 | 商品ID |
| `sku_id` | bigint unsigned | 否 | SKU ID(规格ID) |
| `product_name` | varchar(255) | 是 | 商品名称 |
| `product_image` | varchar(255) | 否 | 商品图片 |
| `sku_name` | varchar(255) | 否 | 规格名称 |
| `price` | decimal(10,2) | 是 | 商品单价 |
| `quantity` | int | 是 | 购买数量 |
| `subtotal` | decimal(10,2) | 是 | 小计金额 |
| `created_at` | timestamp | 否 | 创建时间 |
| `updated_at` | timestamp | 否 | 更新时间 |

## 3. shopping_cart (购物车表)

| 字段名 | 类型 | 是否必填 | 默认值 | 说明 |
|--------|------|----------|--------|------|
| `id` | bigint unsigned | 是 | - | 主键,自增 |
| `user_id` | bigint unsigned | 是 | - | 用户ID |
| `product_id` | bigint unsigned | 是 | - | 商品ID |
| `specification_id` | bigint unsigned | 否 | NULL | 规格ID |
| `quantity` | int | 是 | 1 | 数量 |
| `price` | decimal(10,2) | 否 | NULL | 商品单价 |
| `created_at` | timestamp | 否 | NULL | 创建时间 |
| `updated_at` | timestamp | 否 | NULL | 更新时间 |

## 4. shipping_addresses (收货地址表)

| 字段名 | 类型 | 是否必填 | 默认值 | 说明 |
|--------|------|----------|--------|------|
| `id` | bigint unsigned | 是 | - | 主键,自增 |
| `user_id` | bigint unsigned | 是 | - | 用户ID |
| `receiver_name` | varchar(255) | 否 | '' | 收货人姓名 |
| `receiver_phone` | varchar(255) | 是 | - | 收货人电话 |
| `province` | varchar(255) | 否 | '' | 省份 |
| `city` | varchar(255) | 否 | '' | 城市 |
| `district` | varchar(255) | 否 | '' | 区县 |
| `detail_address` | varchar(255) | 是 | - | 详细地址 |
| `is_default` | tinyint(1) | 是 | 0 | 是否默认地址 |
| `created_at` | timestamp | 否 | NULL | 创建时间 |
| `updated_at` | timestamp | 否 | NULL | 更新时间 |

## 5. products (商品表)

| 字段名 | 类型 | 是否必填 | 默认值 | 说明 |
|--------|------|----------|--------|------|
| `id` | bigint unsigned | 是 | - | 主键,自增 |
| `category_id` | bigint unsigned | 是 | - | 分类ID |
| `name` | varchar(255) | 是 | - | 商品名称 |
| `cover_image` | varchar(255) | 否 | NULL | 封面图片 |
| `original_price` | decimal(10,2) | 是 | 0.00 | 原价 |
| `price` | decimal(10,2) | 是 | - | 现价 |
| `stock` | int | 是 | 0 | 库存 |
| `sales` | int | 是 | 0 | 销量 |
| `unit` | varchar(255) | 是 | '件' | 单位 |
| `summary` | text | 否 | NULL | 简介 |
| `description` | text | 否 | NULL | 详细描述 |
| `tech_params` | json | 否 | NULL | 技术参数 |
| `sort_order` | int | 是 | 0 | 排序 |
| `status` | tinyint | 是 | 1 | 状态:0=下架,1=上架 |
| `created_at` | timestamp | 否 | NULL | 创建时间 |
| `updated_at` | timestamp | 否 | NULL | 更新时间 |
| `deleted_at` | timestamp | 否 | NULL | 软删除时间 |

## 6. product_specifications (商品规格表)

| 字段名 | 类型 | 是否必填 | 默认值 | 说明 |
|--------|------|----------|--------|------|
| `id` | bigint unsigned | 是 | - | 主键,自增 |
| `product_id` | bigint unsigned | 是 | - | 商品ID |
| `sku_code` | varchar(255) | 是 | - | SKU编码,唯一 |
| `spec_values` | json | 否 | NULL | 规格值 |
| `price` | decimal(10,2) | 否 | NULL | 规格价格 |
| `stock` | int | 是 | 0 | 规格库存 |
| `image` | varchar(255) | 否 | NULL | 规格图片 |
| `status` | tinyint | 是 | 1 | 状态:0=停用,1=启用 |
| `created_at` | timestamp | 否 | NULL | 创建时间 |
| `updated_at` | timestamp | 否 | NULL | 更新时间 |

---

# 字段名称映射对照

## 重要提醒

### 购物车 (shopping_cart)
- ✅ 使用 `specification_id` (不是 sku_id)

### 订单明细 (order_items)
- ✅ 使用 `sku_id` (不是 specification_id)
- ✅ 使用 `sku_name` (不是 spec_name)
- ✅ 使用 `subtotal` (不是 total_amount)

### 收货地址 (shipping_addresses)
- ✅ 使用 `receiver_name` (不是 name)
- ✅ 使用 `receiver_phone` (不是 phone)
- ✅ 使用 `detail_address` (不是 detail)

### 订单 (orders)
- ✅ 字段已正确设置

---

# 后端代码检查结果

## ✅ 已修复的问题

1. **OrderController.php 第241-251行**:
   - ✅ 已修正为使用 `sku_id`, `sku_name`, `subtotal`

2. **ShoppingCart Model**:
   - ✅ 使用 `specification_id`

3. **ShippingAddress Model**:
   - ✅ 使用 `receiver_name`, `receiver_phone`, `detail_address`

## 当前无字段不匹配问题

所有后端Model和Controller已与数据库表结构保持一致。
