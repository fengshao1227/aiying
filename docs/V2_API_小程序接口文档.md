# 爱婴月子中心小程序 V2 API 接口文档

## 📋 概述

### 基础信息

| 项目 | 说明 |
|------|------|
| **API 基础 URL** | `https://aiying.qdhs.cloud/v2` |
| **数据格式** | JSON |
| **字符编码** | UTF-8 |

### 认证方式

需认证接口在请求头中添加：

```
X-Openid: 用户openid
```

### 统一响应格式

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {}
}
```

> `code` 为 0 表示成功，其他值表示错误码

### HTTP 状态码

| 状态码 | 说明 |
|--------|------|
| 200 | 请求成功 |
| 400 | 请求参数错误 |
| 401 | 未认证/Token过期 |
| 403 | 账号已禁用 |
| 404 | 资源不存在 |
| 500 | 服务器错误 |

---

## 1. 用户模块 `/v2/user`

### 1.1 微信登录

```
POST /v2/user/login
```

**无需认证**

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| code | string | 是 | 微信登录code（wx.login获取） |

**响应示例：**

```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token": "xxxxxx",
    "user": {
      "id": 1,
      "openid": "oXXXX...",
      "nickname": "微信用户",
      "avatar": null,
      "gender": null,
      "phone": null,
      "pointsBalance": 0,
      "isBound": false,
      "status": 1,
      "customer": {
        "id": 123,
        "name": "张三",
        "phone": "13800138000"
      }
    }
  }
}
```

**响应字段说明：**

| 字段 | 类型 | 说明 |
|------|------|------|
| token | string | 认证令牌 |
| user.id | int | 用户ID |
| user.openid | string | 微信openid |
| user.nickname | string | 昵称 |
| user.avatar | string | 头像URL |
| user.gender | int | 性别 |
| user.phone | string | 手机号 |
| user.pointsBalance | int | 积分余额 |
| user.isBound | bool | 是否已绑定客户 |
| user.status | int | 状态(1=正常) |
| user.customer | object | 客户信息(已绑定时返回) |

---

### 1.2 获取用户信息

```
GET /v2/user/profile
```

**需要认证**

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 用户ID |
| openid | string | 微信openid |
| nickname | string | 昵称 |
| avatar | string | 头像URL |
| gender | int | 性别 |
| phone | string | 手机号 |
| bindPhone | string | 绑定手机号 |
| pointsBalance | int | 积分余额 |
| isBound | bool | 是否已绑定客户 |
| status | int | 状态(1=正常) |
| lastLoginAt | string | 最后登录时间(ISO8601) |
| customer | object | 客户信息(已绑定时返回) |

**customer 对象字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 客户ID |
| name | string | 客户姓名 |
| phone | string | 手机号 |
| packageName | string | 套餐名称 |
| babyName | string | 宝宝名字 |
| checkInDate | string | 入住日期(ISO8601) |
| checkOutDate | string | 离店日期(ISO8601) |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "id": 1,
    "openid": "oXXXX...",
    "nickname": "微信用户",
    "avatar": "https://...",
    "gender": 1,
    "phone": "13800138000",
    "bindPhone": "13800138000",
    "pointsBalance": 500,
    "isBound": true,
    "status": 1,
    "lastLoginAt": "2024-01-15T10:30:00.000Z",
    "customer": {
      "id": 123,
      "name": "张三",
      "phone": "13800138000",
      "packageName": "尊享月子套餐",
      "babyName": "小明",
      "checkInDate": "2024-01-01T00:00:00.000Z",
      "checkOutDate": "2024-02-01T00:00:00.000Z"
    }
  }
}
```

---

### 1.3 绑定月子中心客户

```
POST /v2/user/bindCustomer
```

**需要认证**

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| phone | string | 是 | 手机号（正则: `^1[3-9]\d{9}$`） |

**请求示例：**

```json
{
  "phone": "13800138000"
}
```

**响应：** 返回更新后的完整用户信息

**错误响应：**

| code | message |
|------|---------|
| 400 | 您已绑定客户，无需重复绑定 |
| 400 | 该客户已被其他用户绑定 |
| 404 | 未找到该手机号对应的客户记录，请联系前台确认 |

---

### 1.4 解绑客户

```
POST /v2/user/unbindCustomer
```

**需要认证**

**请求参数：** 无

**响应：** 返回更新后的用户信息（customer 字段为空）

**错误响应：**

| code | message |
|------|---------|
| 400 | 您尚未绑定客户 |

---

## 2. 收货地址模块 `/v2/user/addresses`

> 所有接口需要认证

### 2.1 获取地址列表

```
GET /v2/user/addresses
```

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 地址ID |
| name | string | 收货人姓名 |
| phone | string | 手机号 |
| province | string | 省份 |
| city | string | 城市 |
| district | string | 区县 |
| address | string | 详细地址 |
| is_default | bool | 是否默认 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "name": "张三",
      "phone": "13800138000",
      "province": "山东省",
      "city": "青岛市",
      "district": "市南区",
      "address": "香港中路100号",
      "is_default": true
    }
  ]
}
```

---

### 2.2 添加地址

```
POST /v2/user/addresses
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 收货人姓名(max:50) |
| phone | string | 是 | 手机号(正则: `^1[3-9]\d{9}$`) |
| province | string | 是 | 省份(max:50) |
| city | string | 是 | 城市(max:50) |
| district | string | 是 | 区县(max:50) |
| address | string | 是 | 详细地址(max:255) |
| is_default | bool | 否 | 是否设为默认(默认false) |

**请求示例：**

```json
{
  "name": "张三",
  "phone": "13800138000",
  "province": "山东省",
  "city": "青岛市",
  "district": "市南区",
  "address": "香港中路100号",
  "is_default": true
}
```

---

### 2.3 获取地址详情

```
GET /v2/user/addresses/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 地址ID |

---

### 2.4 更新地址

```
PUT /v2/user/addresses/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 地址ID |

**请求参数：** 同添加地址，所有字段可选

---

### 2.5 删除地址

```
DELETE /v2/user/addresses/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 地址ID |

---

### 2.6 设为默认地址

```
POST /v2/user/addresses/{id}/default
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 地址ID |

---

## 3. 商品模块 `/v2/products`

### 3.1 获取商品分类

```
GET /v2/products/categories
```

**无需认证**

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 分类ID |
| name | string | 分类名称 |
| icon | string | 图标URL |
| sort_order | int | 排序值 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "name": "母婴用品",
      "icon": "https://...",
      "sort_order": 1
    },
    {
      "id": 2,
      "name": "营养食品",
      "icon": "https://...",
      "sort_order": 2
    }
  ]
}
```

---

### 3.2 获取商品列表

```
GET /v2/products
```

**无需认证**

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| category_id | int | 否 | 分类ID筛选 |
| delivery_type | string | 否 | 配送类型(express=快递/room=送至房间) |
| page | int | 否 | 页码(默认1) |
| per_page | int | 否 | 每页数量(默认20) |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "list": [
      {
        "id": 1,
        "category_id": 1,
        "name": "婴儿纸尿裤",
        "cover_image": "https://...",
        "price": "99.00",
        "original_price": "129.00",
        "points_price": 500,
        "stock": 100,
        "unit": "包",
        "delivery_type": "express",
        "category": {
          "id": 1,
          "name": "母婴用品"
        }
      }
    ],
    "total": 100,
    "currentPage": 1,
    "perPage": 20,
    "lastPage": 5
  }
}
```

---

### 3.3 获取商品详情

```
GET /v2/products/{id}
```

**无需认证**

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 商品ID |

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 商品ID |
| category_id | int | 分类ID |
| name | string | 商品名称 |
| cover_image | string | 封面图URL |
| images | array | 商品图片列表 |
| delivery_type | string | 配送类型(express/room) |
| original_price | decimal | 原价 |
| price | decimal | 售价 |
| points_price | int | 积分价格(可用积分兑换) |
| stock | int | 库存数量 |
| unit | string | 单位 |
| summary | string | 商品简介 |
| description | string | 商品详情(富文本) |
| status | int | 状态(1=上架) |
| category | object | 分类信息 |

**错误响应：**

| code | message |
|------|---------|
| 400 | 商品已下架 |
| 404 | 商品不存在 |

---

## 4. 购物车模块 `/v2/cart`

> 所有接口需要认证

### 4.1 获取购物车列表

```
GET /v2/cart
```

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 购物车项ID |
| product_id | int | 商品ID |
| quantity | int | 数量 |
| selected | bool | 是否选中 |
| isValid | bool | 商品是否有效(上架且有库存) |
| product | object | 商品信息 |
| subtotal | decimal | 小计金额 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "items": [
      {
        "id": 1,
        "product_id": 10,
        "quantity": 2,
        "selected": true,
        "isValid": true,
        "subtotal": "198.00",
        "product": {
          "id": 10,
          "name": "婴儿纸尿裤",
          "cover_image": "https://...",
          "price": "99.00",
          "stock": 100,
          "status": 1
        }
      }
    ],
    "totalCount": 1,
    "selectedCount": 1,
    "totalAmount": "198.00"
  }
}
```

---

### 4.2 添加商品到购物车

```
POST /v2/cart
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| product_id | int | 是 | 商品ID |
| quantity | int | 是 | 数量(1-99) |

**请求示例：**

```json
{
  "product_id": 10,
  "quantity": 2
}
```

**错误响应：**

| code | message |
|------|---------|
| 400 | 商品不存在或已下架 |
| 400 | 库存不足 |

---

### 4.3 更新购物车

```
PUT /v2/cart/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 购物车项ID |

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| quantity | int | 否 | 数量(1-99) |
| selected | bool | 否 | 是否选中 |

> 至少提供一个参数

---

### 4.4 批量选择

```
PUT /v2/cart/select
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| ids | array | 是 | 购物车项ID数组 |
| selected | bool | 是 | 是否选中 |

**请求示例：**

```json
{
  "ids": [1, 2, 3],
  "selected": true
}
```

---

### 4.5 删除购物车项

```
DELETE /v2/cart/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 购物车项ID |

---

### 4.6 清空购物车

```
DELETE /v2/cart/clear
```

---

## 5. 订单模块 `/v2/orders`

> 所有接口需要认证

### 5.1 商城订单

#### 5.1.1 创建商城订单

```
POST /v2/orders/mall
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| delivery_type | string | 是 | 配送类型(express=快递/room=送至房间) |
| room_id | int | 条件必填 | 房间ID(delivery_type=room时必填) |
| room_name | string | 条件必填 | 房间名(delivery_type=room时必填) |
| receiver_name | string | 条件必填 | 收货人(delivery_type=express时必填) |
| receiver_phone | string | 条件必填 | 收货电话(delivery_type=express时必填) |
| receiver_address | string | 条件必填 | 收货地址(delivery_type=express时必填) |
| freight_amount | decimal | 否 | 运费(默认0) |
| points_used | int | 否 | 使用积分抵扣(默认0) |
| remarks | string | 否 | 订单备注 |

**请求示例（快递配送）：**

```json
{
  "delivery_type": "express",
  "receiver_name": "张三",
  "receiver_phone": "13800138000",
  "receiver_address": "山东省青岛市市南区香港中路100号",
  "freight_amount": 10,
  "points_used": 100,
  "remarks": "请尽快发货"
}
```

**请求示例（送至房间）：**

```json
{
  "delivery_type": "room",
  "room_id": 101,
  "room_name": "101房",
  "points_used": 0,
  "remarks": "下午3点送达"
}
```

**响应示例：**

```json
{
  "code": 0,
  "message": "订单创建成功",
  "data": {
    "id": 1,
    "order_no": "M202401150001",
    "user_id": 1,
    "delivery_type": "express",
    "goods_amount": "198.00",
    "freight_amount": "10.00",
    "points_used": 100,
    "points_discount": "10.00",
    "actual_amount": "198.00",
    "order_status": 0,
    "payment_status": 0,
    "items": [...]
  }
}
```

---

#### 5.1.2 获取商城订单列表

```
GET /v2/orders/mall
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | int | 否 | 订单状态筛选 |
| page | int | 否 | 页码(默认1) |
| per_page | int | 否 | 每页数量(默认20) |

**订单状态说明：**

| status | 说明 |
|--------|------|
| 0 | 待支付 |
| 1 | 已支付/待发货 |
| 2 | 已发货/待收货 |
| 3 | 已完成 |
| 4 | 已取消 |
| 5 | 退款中 |
| 6 | 已退款 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "order_no": "ML20251227100000001",
        "order_status": 0,
        "actual_amount": "99.00",
        "created_at": "2025-12-27T10:00:00.000000Z",
        "items": [
          { "id": 1, "order_id": 1, "product_name": "商品名称", "product_image": "https://...", "quantity": 2, "subtotal": "99.00" }
        ]
      }
    ],
    "total": 1
  }
}
```

> **说明**：`items` 仅包含列表展示所需字段。详情接口返回完整 item 信息（含 `product_id`, `price` 等）。

---

#### 5.1.3 获取商城订单详情

```
GET /v2/orders/mall/{id}
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

---

#### 5.1.4 取消商城订单

```
POST /v2/orders/mall/{id}/cancel
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 否 | 取消原因 |

---

#### 5.1.5 确认收货

```
POST /v2/orders/mall/{id}/confirm
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

---

#### 5.1.6 申请退款

```
POST /v2/orders/mall/{id}/refund
```

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 是 | 退款原因(max:255) |

---

### 5.2 订餐订单

#### 5.2.1 创建订餐订单

```
POST /v2/orders/meal
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| items | array | 是 | 订餐项列表 |
| items.*.meal_date | date | 是 | 用餐日期(格式: Y-m-d) |
| items.*.meal_type | string | 是 | 餐次(breakfast/lunch/dinner) |
| items.*.quantity | int | 是 | 数量(min:1) |
| room_id | int | 否 | 房间ID |
| room_name | string | 否 | 房间名称 |
| customer_name | string | 否 | 客户姓名 |
| points_used | int | 否 | 使用积分抵扣 |
| remarks | string | 否 | 订单备注 |

**餐次类型说明：**

| meal_type | 说明 |
|-----------|------|
| breakfast | 早餐 |
| lunch | 午餐 |
| dinner | 晚餐 |

**请求示例：**

```json
{
  "items": [
    {
      "meal_date": "2024-01-16",
      "meal_type": "lunch",
      "quantity": 1
    },
    {
      "meal_date": "2024-01-16",
      "meal_type": "dinner",
      "quantity": 2
    }
  ],
  "room_id": 101,
  "room_name": "101房",
  "customer_name": "张三",
  "points_used": 50,
  "remarks": "少辣"
}
```

---

#### 5.2.2 获取订餐订单列表

```
GET /v2/orders/meal
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | int | 否 | 订单状态筛选 |
| page | int | 否 | 页码(默认1) |
| per_page | int | 否 | 每页数量(默认20) |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 101,
        "order_no": "ME20251227100000001",
        "order_status": 0,
        "actual_amount": "30.00",
        "created_at": "2025-12-27T10:00:00.000000Z",
        "items": [
          { "id": 1, "meal_order_id": 101, "meal_date": "2025-12-28", "meal_type": "breakfast", "meal_name": "营养早餐" },
          { "id": 2, "meal_order_id": 101, "meal_date": "2025-12-29", "meal_type": "breakfast", "meal_name": "营养早餐" }
        ]
      }
    ],
    "total": 1
  }
}
```

> **说明**：`items` 按 `meal_date` 升序排列，仅包含列表展示所需字段。详情接口返回完整 item 信息。

---

#### 5.2.3 获取订餐订单详情

```
GET /v2/orders/meal/{id}
```

---

#### 5.2.4 取消订餐订单

```
POST /v2/orders/meal/{id}/cancel
```

---

#### 5.2.5 申请退款

```
POST /v2/orders/meal/{id}/refund
```

---

## 6. 支付模块

### 6.1 商城订单支付

```
POST /v2/orders/mall/{id}/pay
```

**需要认证**

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

**响应（需微信支付）：**

```json
{
  "code": 0,
  "message": "获取支付参数成功",
  "data": {
    "appId": "wxccf8804f6f48fb46",
    "timeStamp": "1703520000",
    "nonceStr": "xxxxx",
    "package": "prepay_id=xxx",
    "signType": "RSA",
    "paySign": "xxxxx"
  }
}
```

> 使用返回的参数调用 `wx.requestPayment` 发起支付

**响应（纯积分支付）：**

```json
{
  "code": 0,
  "message": "支付成功（纯积分）",
  "data": {
    "paid": true
  }
}
```

---

### 6.2 订餐订单支付

```
POST /v2/orders/meal/{id}/pay
```

**需要认证**

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 订单ID |

**响应格式同商城订单支付**

---

### 6.3 微信支付回调

```
POST /v2/payments/notify
```

**无需认证（微信服务器调用）**

> 此接口由微信支付服务器调用，小程序端无需关注

---

## 7. 积分模块 `/v2/points`

> 所有接口需要认证

### 7.1 获取积分余额

```
GET /v2/points/balance
```

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| points_balance | int | 当前积分余额 |
| total_earned | int | 累计获得积分 |
| total_used | int | 累计使用积分 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "points_balance": 500,
    "total_earned": 1000,
    "total_used": 500
  }
}
```

---

### 7.2 获取积分历史

```
GET /v2/points/history
```

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| type | string | 否 | 类型筛选 |
| page | int | 否 | 页码(默认1) |
| per_page | int | 否 | 每页数量(1-100, 默认20) |

**积分类型说明：**

| type | 说明 |
|------|------|
| earn | 消费获得 |
| spend | 积分抵扣 |
| refund | 退款返还 |
| admin_add | 管理员增加 |
| admin_deduct | 管理员扣减 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "type": "earn",
        "points": 50,
        "balance_after": 550,
        "description": "订单消费返积分",
        "created_at": "2024-01-15T10:30:00.000Z"
      }
    ],
    "total": 10,
    "per_page": 20,
    "last_page": 1
  }
}
```

---

## 8. 订餐配置 `/v2/meal/configs`

### 获取订餐配置

```
GET /v2/meal/configs
```

**无需认证**

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| meal_type | string | 否 | 餐次筛选(breakfast/lunch/dinner) |
| start_date | string | 否 | 日历查询起始日期(格式: Y-m-d) |
| end_date | string | 否 | 日历查询结束日期(格式: Y-m-d)，需与start_date一起使用 |

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| meals | array | 餐次配置列表 |
| meals[].id | int | 配置ID |
| meals[].meal_type | string | 餐次类型 |
| meals[].name | string | 餐次名称 |
| meals[].price | decimal | 价格 |
| meals[].cover_image | string | 封面图片URL |
| meals[].order_start_time | time | 订餐开始时间(HH:mm:ss) |
| meals[].order_end_time | time | 订餐截止时间(HH:mm:ss) |
| meals[].advance_days | int | 需提前天数(0=当天可订) |
| meals[].description | string | 描述 |
| meals[].status | int | 状态(1=启用) |
| unavailable_dates | array | 不可预订日期列表(传入start_date和end_date时返回) |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "meals": [
      {
        "id": 1,
        "meal_type": "breakfast",
        "name": "营养早餐",
        "price": "10.00",
        "cover_image": "https://example.com/breakfast.jpg",
        "order_start_time": "06:00:00",
        "order_end_time": "08:00:00",
        "advance_days": 1,
        "description": "每日固定营养搭配",
        "status": 1
      },
      {
        "id": 2,
        "meal_type": "lunch",
        "name": "营养午餐",
        "price": "10.00",
        "cover_image": "https://example.com/lunch.jpg",
        "order_start_time": "09:00:00",
        "order_end_time": "11:00:00",
        "advance_days": 0,
        "description": "当天可订",
        "status": 1
      }
    ],
    "unavailable_dates": [
      "2025-12-30",
      "2026-01-01"
    ]
  }
}
```

---

## 9. 系统配置 `/v2/config`

### 9.1 获取配置列表

```
GET /v2/config
```

**无需认证**

**请求参数：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| group | string | 否 | 配置分组筛选 |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "site_name": "爱婴月子中心",
    "contact_phone": "400-123-4567",
    "points_exchange_rate": "0.1"
  }
}
```

---

### 9.2 获取单个配置

```
GET /v2/config/{key}
```

**无需认证**

**路径参数：**

| 字段 | 类型 | 说明 |
|------|------|------|
| key | string | 配置键名 |

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| key | string | 配置键 |
| value | mixed | 配置值 |
| type | string | 配置类型 |
| group | string | 分组 |
| description | string | 描述 |

---

### 9.3 获取积分配置

```
GET /v2/config/points
```

**无需认证**

**响应字段：**

| 字段 | 类型 | 说明 |
|------|------|------|
| exchange_rate | decimal | 积分兑换比例(如0.1表示10积分=1元) |
| max_discount_rate | decimal | 最大抵扣比例(如0.5表示最多抵扣50%) |

**响应示例：**

```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "exchange_rate": "0.1",
    "max_discount_rate": "0.5"
  }
}
```

---

## 📌 附录

### 错误码说明

| code | 说明 |
|------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未登录/认证失败 |
| 403 | 账号已被禁用 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

### 接口统计

| 模块 | 接口数量 | 认证要求 |
|------|----------|----------|
| 用户模块 | 4个 | 登录无需认证，其他需要 |
| 收货地址 | 6个 | 全部需要 |
| 商品模块 | 3个 | 无需认证 |
| 购物车 | 6个 | 全部需要 |
| 订单模块 | 12个 | 全部需要 |
| 支付模块 | 3个 | 回调无需认证 |
| 积分模块 | 2个 | 全部需要 |
| 订餐配置 | 1个 | 无需认证 |
| 系统配置 | 3个 | 无需认证 |

**共计 40 个接口**

---

> 文档生成时间: 2024年12月
>
> 如有疑问请联系后端开发人员
