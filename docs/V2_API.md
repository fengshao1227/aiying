# V2 API 接口文档

**Base URL:** `/v2`

**认证方式:** 在请求Header中添加 `X-Openid: 用户openid`（登录时返回）

**通用响应格式:**
```json
{
  "code": 0,        // 0=成功，其他=错误码
  "message": "xxx",
  "data": {}
}
```

---

## 1. 用户模块 `/v2/user`

### 1.1 微信登录
```
POST /v2/user/login
```
**无需认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| code | string | 是 | 微信登录code |

**响应示例:**
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

> **重要:** 登录成功后，前端需保存 `user.openid`，后续所有需要认证的接口请求时，在Header中添加 `X-Openid: openid值`

---

### 1.2 获取用户信息
```
GET /v2/user/profile
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "id": 1,
    "nickname": "微信用户",
    "avatar": null,
    "gender": null,
    "phone": null,
    "bindPhone": "13800138000",
    "pointsBalance": 100,
    "isBound": true,
    "status": 1,
    "lastLoginAt": "2024-01-01T00:00:00.000Z",
    "customer": {
      "id": 123,
      "name": "张三",
      "phone": "13800138000",
      "packageName": "月子套餐A",
      "babyName": "宝宝",
      "checkInDate": "2024-01-01T00:00:00.000Z",
      "checkOutDate": "2024-02-01T00:00:00.000Z"
    }
  }
}
```

---

### 1.3 绑定客户
```
POST /v2/user/bindCustomer
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| phone | string | 是 | 客户手机号（11位） |

**响应示例:**
```json
{
  "code": 0,
  "message": "绑定成功",
  "data": {
    "id": 1,
    "nickname": "微信用户",
    "isBound": true,
    "customer": {
      "id": 123,
      "name": "张三",
      "phone": "13800138000"
    }
  }
}
```

---

### 1.4 解绑客户
```
POST /v2/user/unbindCustomer
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "解绑成功",
  "data": {
    "id": 1,
    "isBound": false
  }
}
```

---

## 2. 收货地址模块 `/v2/user/addresses`

### 2.1 获取地址列表
```
GET /v2/user/addresses
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "name": "张三",
      "phone": "13800138000",
      "province": "广东省",
      "city": "深圳市",
      "district": "南山区",
      "address": "科技园xxx",
      "is_default": true
    }
  ]
}
```

---

### 2.2 新增地址
```
POST /v2/user/addresses
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | 是 | 收货人姓名 |
| phone | string | 是 | 手机号（11位） |
| province | string | 是 | 省份 |
| city | string | 是 | 城市 |
| district | string | 是 | 区县 |
| address | string | 是 | 详细地址 |
| is_default | boolean | 否 | 是否默认，默认false |

---

### 2.3 获取地址详情
```
GET /v2/user/addresses/{id}
```
**需要认证**

---

### 2.4 更新地址
```
PUT /v2/user/addresses/{id}
```
**需要认证**

**请求参数:** 同新增，所有字段可选

---

### 2.5 删除地址
```
DELETE /v2/user/addresses/{id}
```
**需要认证**

---

### 2.6 设为默认地址
```
POST /v2/user/addresses/{id}/default
```
**需要认证**

---

## 3. 商品模块 `/v2/products`

### 3.1 获取商品分类
```
GET /v2/products/categories
```
**无需认证**

**响应示例:**
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

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| category_id | integer | 否 | 分类ID |
| delivery_type | string | 否 | 配送类型: express(快递)/room(送到房间) |
| per_page | integer | 否 | 每页数量，默认20 |
| page | integer | 否 | 页码 |

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "list": [
      {
        "id": 1,
        "categoryId": 1,
        "categoryName": "母婴用品",
        "name": "婴儿奶粉",
        "coverImage": "https://...",
        "deliveryType": "express",
        "originalPrice": "299.00",
        "price": "259.00",
        "pointsPrice": 2590,
        "stock": 100,
        "sales": 50,
        "unit": "罐",
        "summary": "进口奶粉",
        "supportsPoints": true,
        "supportsCash": true
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

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "id": 1,
    "categoryId": 1,
    "categoryName": "母婴用品",
    "name": "婴儿奶粉",
    "coverImage": "https://...",
    "images": ["https://...", "https://..."],
    "deliveryType": "express",
    "originalPrice": "299.00",
    "price": "259.00",
    "pointsPrice": 2590,
    "stock": 100,
    "sales": 50,
    "unit": "罐",
    "summary": "进口奶粉",
    "description": "<p>详细描述...</p>",
    "supportsPoints": true,
    "supportsCash": true
  }
}
```

---

## 4. 购物车模块 `/v2/cart`

### 4.1 获取购物车列表
```
GET /v2/cart
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "items": [
      {
        "id": 1,
        "productId": 1,
        "product": {
          "id": 1,
          "name": "婴儿奶粉",
          "coverImage": "https://...",
          "price": "259.00",
          "pointsPrice": 2590,
          "stock": 100,
          "unit": "罐",
          "deliveryType": "express",
          "status": 1
        },
        "quantity": 2,
        "selected": true,
        "subtotal": "518.00",
        "pointsSubtotal": 5180,
        "isValid": true,
        "invalidReason": null
      }
    ],
    "summary": {
      "totalAmount": "518.00",
      "totalPoints": 5180,
      "selectedCount": 1
    }
  }
}
```

---

### 4.2 添加商品到购物车
```
POST /v2/cart
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| product_id | integer | 是 | 商品ID |
| quantity | integer | 是 | 数量（1-99） |

**响应示例:**
```json
{
  "code": 0,
  "message": "添加成功",
  "data": {
    "id": 1,
    "quantity": 2
  }
}
```

---

### 4.3 更新购物车商品
```
PUT /v2/cart/{id}
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| quantity | integer | 否 | 数量（1-99） |
| selected | boolean | 否 | 是否选中 |

---

### 4.4 删除购物车商品
```
DELETE /v2/cart/{id}
```
**需要认证**

---

### 4.5 清空购物车
```
DELETE /v2/cart/clear
```
**需要认证**

---

### 4.6 批量选中/取消选中
```
PUT /v2/cart/select
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| ids | array | 是 | 购物车项ID数组 |
| selected | boolean | 是 | 是否选中 |

---

## 5. 订单模块 `/v2/orders`

### 5.1 商城订单

#### 5.1.1 创建商城订单
```
POST /v2/orders/mall
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| delivery_type | string | 是 | 配送类型: express(快递)/room(送到房间) |
| room_id | integer | 条件必填 | 房间ID（delivery_type=room时必填） |
| room_name | string | 条件必填 | 房间名称（delivery_type=room时必填） |
| receiver_name | string | 条件必填 | 收货人姓名（delivery_type=express时必填） |
| receiver_phone | string | 条件必填 | 收货人手机（delivery_type=express时必填） |
| receiver_address | string | 条件必填 | 收货地址（delivery_type=express时必填） |
| freight_amount | number | 否 | 运费，默认0 |
| points_used | integer | 否 | 使用积分数量，默认0 |
| remarks | string | 否 | 备注 |

**响应示例:**
```json
{
  "code": 0,
  "message": "订单创建成功",
  "data": {
    "id": 1,
    "order_no": "MO20241225123456789",
    "total_amount": "518.00",
    "freight_amount": "0.00",
    "points_used": 100,
    "points_discount": "10.00",
    "actual_amount": "508.00",
    "payment_status": 0,
    "order_status": 0,
    "items": [...]
  }
}
```

---

#### 5.1.2 获取商城订单列表
```
GET /v2/orders/mall
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | integer | 否 | 订单状态筛选 |
| per_page | integer | 否 | 每页数量，默认20 |

**订单状态说明:**
| 值 | 说明 |
|----|------|
| 0 | 待付款 |
| 1 | 待发货 |
| 2 | 待收货 |
| 3 | 已完成 |
| 4 | 已取消 |
| 5 | 退款中 |
| 6 | 已退款 |

---

#### 5.1.3 获取商城订单详情
```
GET /v2/orders/mall/{id}
```
**需要认证**

---

#### 5.1.4 取消商城订单
```
POST /v2/orders/mall/{id}/cancel
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 否 | 取消原因 |

---

#### 5.1.5 商城订单支付
```
POST /v2/orders/mall/{id}/pay
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取支付参数成功",
  "data": {
    "appId": "wx...",
    "timeStamp": "1703520000",
    "nonceStr": "xxx",
    "package": "prepay_id=xxx",
    "signType": "RSA",
    "paySign": "xxx"
  }
}
```

---

#### 5.1.6 申请商城订单退款
```
POST /v2/orders/mall/{id}/refund
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 是 | 退款原因（最多255字符） |

---

### 5.2 订餐订单

#### 5.2.1 创建订餐订单
```
POST /v2/orders/meal
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| items | array | 是 | 订餐项列表 |
| items[].meal_date | date | 是 | 用餐日期（格式：YYYY-MM-DD） |
| items[].meal_type | string | 是 | 餐次: breakfast/lunch/dinner |
| items[].quantity | integer | 是 | 数量（≥1） |
| room_id | integer | 否 | 房间ID |
| room_name | string | 否 | 房间名称 |
| customer_name | string | 否 | 客户姓名 |
| points_used | integer | 否 | 使用积分数量 |
| remarks | string | 否 | 备注 |

**请求示例:**
```json
{
  "items": [
    {
      "meal_date": "2024-12-26",
      "meal_type": "lunch",
      "quantity": 1
    },
    {
      "meal_date": "2024-12-26",
      "meal_type": "dinner",
      "quantity": 1
    }
  ],
  "room_name": "301房",
  "customer_name": "张三",
  "points_used": 100,
  "remarks": "少辣"
}
```

**响应示例:**
```json
{
  "code": 0,
  "message": "订餐成功",
  "data": {
    "id": 1,
    "order_no": "ME20241225123456789",
    "total_amount": "50.00",
    "points_used": 100,
    "points_discount": "10.00",
    "actual_amount": "40.00",
    "payment_status": 0,
    "order_status": 0,
    "items": [
      {
        "meal_date": "2024-12-26",
        "meal_type": "lunch",
        "meal_name": "午餐",
        "unit_price": "25.00",
        "quantity": 1,
        "subtotal": "25.00"
      }
    ]
  }
}
```

---

#### 5.2.2 获取订餐订单列表
```
GET /v2/orders/meal
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | integer | 否 | 订单状态筛选 |
| per_page | integer | 否 | 每页数量，默认20 |

---

#### 5.2.3 获取订餐订单详情
```
GET /v2/orders/meal/{id}
```
**需要认证**

---

#### 5.2.4 取消订餐订单
```
POST /v2/orders/meal/{id}/cancel
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 否 | 取消原因 |

---

#### 5.2.5 订餐订单支付
```
POST /v2/orders/meal/{id}/pay
```
**需要认证**

---

#### 5.2.6 申请订餐订单退款
```
POST /v2/orders/meal/{id}/refund
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 是 | 退款原因（最多255字符） |

---

## 6. 订餐配置模块 `/v2/meal`

### 6.1 获取订餐配置
```
GET /v2/meal/configs
```
**无需认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| meal_type | string | 否 | 餐次筛选: breakfast/lunch/dinner |
| date | string | 否 | 日期（格式：YYYY-MM-DD），返回该日期可订情况 |

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "meal_type": "breakfast",
      "meal_name": "早餐",
      "price": "20.00",
      "points_price": 200,
      "order_start_time": "17:00:00",
      "order_end_time": "20:00:00",
      "advance_days": 1,
      "status": 1,
      "is_available": true
    },
    {
      "id": 2,
      "meal_type": "lunch",
      "meal_name": "午餐",
      "price": "25.00",
      "points_price": 250,
      "order_start_time": "17:00:00",
      "order_end_time": "10:00:00",
      "advance_days": 0,
      "status": 1,
      "is_available": true
    }
  ]
}
```

---

## 7. 积分模块 `/v2/points`

### 7.1 获取积分余额
```
GET /v2/points/balance
```
**需要认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "points_balance": 1000,
    "total_earned": 1500,
    "total_used": 500
  }
}
```

---

### 7.2 获取积分历史
```
GET /v2/points/history
```
**需要认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| type | string | 否 | 类型筛选: earn/spend/refund/admin_add/admin_deduct |
| per_page | integer | 否 | 每页数量，默认20，最大100 |

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "type": "earn",
        "points": 100,
        "balance_after": 1000,
        "description": "消费赠送",
        "created_at": "2024-12-25 10:00:00"
      }
    ],
    "total": 50
  }
}
```

---

## 8. 支付模块 `/v2/payments`

### 8.1 微信支付回调
```
POST /v2/payments/notify
```
**无需认证（微信服务器调用）**

---

## 9. 系统配置模块 `/v2/config`

### 9.1 获取所有配置
```
GET /v2/config
```
**无需认证**

**请求参数:**
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| group | string | 否 | 配置分组筛选 |

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "site_name": "瑷婴月子中心",
    "contact_phone": "400-xxx-xxxx"
  }
}
```

---

### 9.2 获取单个配置
```
GET /v2/config/{key}
```
**无需认证**

---

### 9.3 获取积分配置
```
GET /v2/config/points
```
**无需认证**

**响应示例:**
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "exchange_rate": 10,
    "max_discount_rate": 50
  }
}
```

---

## 错误码说明

| code | 说明 |
|------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未认证/Token无效 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |
