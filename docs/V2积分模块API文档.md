# V2 积分模块 API 文档

## 基础信息

- **Base URL**: `/v2/points`
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **认证方式**: 需要V2用户认证（中间件：`v2.user.auth`）

---

## 1. 获取积分余额

### 接口信息
- **URL**: `/v2/points/balance`
- **Method**: `GET`
- **认证**: 需要认证

### 请求参数
无

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "points_balance": 1500,
    "total_earned": 2000,
    "total_used": 500
  }
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

### 响应字段说明
| 字段 | 类型 | 说明 |
|------|------|------|
| `points_balance` | integer | 当前积分余额 |
| `total_earned` | integer | 累计获得积分 |
| `total_used` | integer | 累计使用积分（正数） |

---

## 2. 获取积分历史记录

### 接口信息
- **URL**: `/v2/points/history`
- **Method**: `GET`
- **认证**: 需要认证

### 请求参数（Query）
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `type` | string | 否 | 记录类型筛选（见类型说明） |
| `per_page` | integer | 否 | 每页数量（1-100，默认20） |

### 记录类型说明
| 值 | 说明 |
|----|------|
| `earn` | 获得积分（订单消费返积分等） |
| `spend` | 消费积分（积分兑换商品等） |
| `refund` | 退还积分（订单退款返还） |
| `admin_add` | 管理员增加 |
| `admin_deduct` | 管理员扣减 |

### 请求示例
```
GET /v2/points/history
GET /v2/points/history?type=earn
GET /v2/points/history?type=spend&per_page=10
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 100,
        "user_id": 1,
        "customer_id": 5,
        "type": "earn",
        "points": 100,
        "balance_before": 1400,
        "balance_after": 1500,
        "source": "order",
        "source_id": 12345,
        "description": "订单消费返积分",
        "operator_id": null,
        "created_at": "2025-12-24T10:30:00.000000Z"
      },
      {
        "id": 99,
        "user_id": 1,
        "customer_id": 5,
        "type": "spend",
        "points": -200,
        "balance_before": 1600,
        "balance_after": 1400,
        "source": "order",
        "source_id": 12340,
        "description": "积分兑换商品",
        "operator_id": null,
        "created_at": "2025-12-23T15:20:00.000000Z"
      }
    ],
    "first_page_url": "/v2/points/history?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "/v2/points/history?page=5",
    "next_page_url": "/v2/points/history?page=2",
    "path": "/v2/points/history",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 98
  }
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

---

## 字段说明

### 积分历史记录字段
| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | integer | 记录ID |
| `user_id` | integer | 用户ID |
| `customer_id` | integer/null | 关联客户ID（可为空） |
| `type` | string | 记录类型（见类型说明） |
| `points` | integer | 积分变动值（正数=增加，负数=扣减） |
| `balance_before` | integer | 变动前余额 |
| `balance_after` | integer | 变动后余额 |
| `source` | string/null | 来源类型：`order`=订单，`meal`=订餐，`admin`=管理员操作 |
| `source_id` | integer/null | 来源关联ID（如订单ID） |
| `description` | string/null | 变动描述 |
| `operator_id` | integer/null | 操作人ID（管理员操作时有值） |
| `created_at` | string | 创建时间（ISO 8601格式） |

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 401 | 用户未认证 |
| 500 | 服务器内部错误 |
