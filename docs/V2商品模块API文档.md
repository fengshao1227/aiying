# V2 商品模块 API 文档

## 基础信息

- **Base URL**: `http://127.0.0.1:8000/v2/products`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

---

## 1. 获取商品分类列表

### 接口信息
- **URL**: `/v2/products/categories`
- **Method**: `GET`
- **认证**: 无需认证

### 请求参数
无

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "name": "母婴用品",
      "icon": "https://example.com/icon.png",
      "sort_order": 100
    },
    {
      "id": 2,
      "name": "护理产品",
      "icon": "https://example.com/icon2.png",
      "sort_order": 90
    }
  ]
}
```

---

## 2. 获取商品列表

### 接口信息
- **URL**: `/v2/products`
- **Method**: `GET`
- **认证**: 无需认证

### 请求参数（Query）
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `category_id` | integer | 否 | 分类ID，筛选指定分类的商品 |
| `delivery_type` | string | 否 | 配送类型：`express`=快递，`room`=送到房间 |
| `per_page` | integer | 否 | 每页数量，默认20 |
| `page` | integer | 否 | 页码，默认1 |

### 请求示例
```
GET /v2/products?category_id=1&per_page=10&page=1
GET /v2/products?delivery_type=room
```

### 响应格式
**成功响应 (200)**:
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
        "coverImage": "https://example.com/product1.jpg",
        "deliveryType": "express",
        "originalPrice": "299.00",
        "price": "199.00",
        "pointsPrice": 19900,
        "stock": 100,
        "sales": 50,
        "unit": "罐",
        "summary": "进口优质奶粉",
        "supportsPoints": true,
        "supportsCash": true
      }
    ],
    "total": 50,
    "currentPage": 1,
    "perPage": 20,
    "lastPage": 3
  }
}
```

---

## 3. 获取商品详情

### 接口信息
- **URL**: `/v2/products/{id}`
- **Method**: `GET`
- **认证**: 无需认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 商品ID |

### 请求示例
```
GET /v2/products/1
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "id": 1,
    "categoryId": 1,
    "categoryName": "母婴用品",
    "name": "婴儿奶粉",
    "coverImage": "https://example.com/product1.jpg",
    "images": [
      "https://example.com/product1-1.jpg",
      "https://example.com/product1-2.jpg",
      "https://example.com/product1-3.jpg"
    ],
    "deliveryType": "express",
    "originalPrice": "299.00",
    "price": "199.00",
    "pointsPrice": 19900,
    "stock": 100,
    "sales": 50,
    "unit": "罐",
    "summary": "进口优质奶粉",
    "description": "<p>详细的商品描述...</p>",
    "supportsPoints": true,
    "supportsCash": true
  }
}
```

**错误响应**:
```json
// 商品不存在 (404)
{
  "code": 404,
  "message": "商品不存在",
  "data": null
}

// 商品已下架 (400)
{
  "code": 400,
  "message": "商品已下架",
  "data": null
}
```

---

## 字段说明

### 商品字段
| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | integer | 商品ID |
| `categoryId` | integer | 分类ID |
| `categoryName` | string | 分类名称 |
| `name` | string | 商品名称 |
| `coverImage` | string | 商品主图URL |
| `images` | array | 商品图片列表（仅详情接口返回） |
| `deliveryType` | string | 配送类型：`express`=快递，`room`=送到房间 |
| `originalPrice` | string | 原价（划线价） |
| `price` | string | 现金价格 |
| `pointsPrice` | integer | 积分价格（null表示不支持积分兑换） |
| `stock` | integer | 库存数量 |
| `sales` | integer | 销量 |
| `unit` | string | 单位（如：件、罐、盒） |
| `summary` | string | 商品简介 |
| `description` | string | 商品详情（富文本，仅详情接口返回） |
| `supportsPoints` | boolean | 是否支持积分兑换 |
| `supportsCash` | boolean | 是否支持现金购买 |

### 配送类型说明
| 值 | 说明 | 收货信息 |
|----|------|---------|
| `express` | 快递配送 | 需要填写收货地址 |
| `room` | 送到房间 | 自动送到绑定客户的房间 |

### 支付方式说明
- **仅现金**：`price` > 0 且 `pointsPrice` = null
- **现金或积分**：`price` > 0 且 `pointsPrice` > 0（用户可选择其一）
- **仅积分**：`price` = 0 且 `pointsPrice` > 0

---

## 测试数据

### 插入测试分类
```sql
INSERT INTO categories (name, icon, sort_order, status, created_at, updated_at)
VALUES
('母婴用品', 'https://example.com/icon1.png', 100, 1, NOW(), NOW()),
('护理产品', 'https://example.com/icon2.png', 90, 1, NOW(), NOW()),
('营养保健', 'https://example.com/icon3.png', 80, 1, NOW(), NOW());
```

### 插入测试商品
```sql
INSERT INTO products (category_id, name, cover_image, delivery_type, original_price, price, points_price, stock, sales, unit, summary, description, sort_order, status, created_at, updated_at)
VALUES
(1, '婴儿奶粉', 'https://example.com/product1.jpg', 'express', 299.00, 199.00, 19900, 100, 50, '罐', '进口优质奶粉', '<p>详细描述...</p>', 100, 1, NOW(), NOW()),
(1, '婴儿纸尿裤', 'https://example.com/product2.jpg', 'room', 159.00, 99.00, 9900, 200, 80, '包', '柔软透气', '<p>详细描述...</p>', 90, 1, NOW(), NOW()),
(2, '产后护理套装', 'https://example.com/product3.jpg', 'express', 599.00, 399.00, NULL, 50, 20, '套', '专业护理', '<p>详细描述...</p>', 80, 1, NOW(), NOW());
```

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |
