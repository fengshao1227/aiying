# V2 购物车模块 API 文档

## 基础信息

- **Base URL**: `http://127.0.0.1:8000/v2/cart`
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **认证**: 所有接口需要认证（Header: `X-Openid`）

---

## 1. 获取购物车列表

### 接口信息
- **URL**: `/v2/cart`
- **Method**: `GET`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 响应格式
**成功响应 (200)**:
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
          "coverImage": "https://example.com/product1.jpg",
          "price": "199.00",
          "pointsPrice": 19900,
          "stock": 100,
          "unit": "罐",
          "deliveryType": "express",
          "status": 1
        },
        "quantity": 2,
        "selected": true,
        "subtotal": 398.00,
        "pointsSubtotal": 39800,
        "isValid": true,
        "invalidReason": null
      }
    ],
    "summary": {
      "totalAmount": "398.00",
      "totalPoints": 39800,
      "selectedCount": 1
    }
  }
}
```

### 字段说明
| 字段 | 类型 | 说明 |
|------|------|------|
| `items` | array | 购物车商品列表 |
| `items[].isValid` | boolean | 商品是否有效（上架且库存充足） |
| `items[].invalidReason` | string | 无效原因：`商品已删除`、`商品已下架`、`库存不足` |
| `summary.totalAmount` | string | 选中商品的总金额 |
| `summary.totalPoints` | integer | 选中商品的总积分 |
| `summary.selectedCount` | integer | 选中商品数量 |

---

## 2. 添加商品到购物车

### 接口信息
- **URL**: `/v2/cart`
- **Method**: `POST`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 请求参数
```json
{
  "product_id": 1,   // 商品ID，必填
  "quantity": 2      // 数量，必填，最小1
}
```

### 响应格式
**成功响应 - 新增 (200)**:
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

**成功响应 - 已存在，更新数量 (200)**:
```json
{
  "code": 0,
  "message": "已更新购物车数量",
  "data": {
    "id": 1,
    "quantity": 4
  }
}
```

**错误响应**:
```json
// 商品不存在或已下架 (400)
{
  "code": 400,
  "message": "商品不存在或已下架",
  "data": null
}

// 库存不足 (400)
{
  "code": 400,
  "message": "库存不足",
  "data": null
}
```

---

## 3. 更新购物车商品

### 接口信息
- **URL**: `/v2/cart/{id}`
- **Method**: `PUT`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 购物车项ID |

### 请求头
```
X-Openid: user_openid_from_login
```

### 请求参数
```json
{
  "quantity": 3,      // 新数量，必填，最小1
  "selected": true    // 是否选中，选填
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "更新成功",
  "data": {
    "id": 1,
    "quantity": 3,
    "selected": true
  }
}
```

**错误响应**:
```json
// 购物车项不存在 (404)
{
  "code": 404,
  "message": "购物车项不存在",
  "data": null
}

// 库存不足 (400)
{
  "code": 400,
  "message": "库存不足",
  "data": null
}
```

---

## 4. 删除购物车商品

### 接口信息
- **URL**: `/v2/cart/{id}`
- **Method**: `DELETE`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 购物车项ID |

### 请求头
```
X-Openid: user_openid_from_login
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "删除成功",
  "data": null
}
```

**错误响应**:
```json
// 购物车项不存在 (404)
{
  "code": 404,
  "message": "购物车项不存在",
  "data": null
}
```

---

## 5. 清空购物车

### 接口信息
- **URL**: `/v2/cart/clear`
- **Method**: `DELETE`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "购物车已清空",
  "data": null
}
```

---

## 6. 批量选中/取消选中

### 接口信息
- **URL**: `/v2/cart/select`
- **Method**: `PUT`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 请求参数
```json
{
  "ids": [1, 2, 3],   // 购物车项ID数组，必填
  "selected": true    // 是否选中，必填
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "操作成功",
  "data": null
}
```

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未认证或认证失败 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

---

## 业务规则

### 添加到购物车
1. 商品必须存在且已上架
2. 库存必须充足
3. 如果商品已在购物车中，自动累加数量

### 购物车显示
1. 商品下架后，购物车中仍显示但标记为无效
2. 库存不足时，购物车中标记为无效
3. 只有有效且选中的商品参与总价计算

### 结算规则
1. 只能结算有效且选中的商品
2. 结算时再次校验库存
