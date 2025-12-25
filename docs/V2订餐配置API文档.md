# V2 订餐配置 API 文档

## 基础信息

- **Base URL**: `/v2/meal`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

---

## 1. 获取订餐配置列表

### 接口信息
- **URL**: `/v2/meal/configs`
- **Method**: `GET`
- **认证**: 无需认证

### 请求参数（Query）
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `meal_type` | string | 否 | 餐型筛选：`breakfast`=早餐，`lunch`=午餐，`dinner`=晚餐 |
| `date` | string | 否 | 查询日期（格式：`Y-m-d`），用于计算该日期是否可预订 |

### 请求示例
```
GET /v2/meal/configs
GET /v2/meal/configs?meal_type=lunch
GET /v2/meal/configs?date=2025-12-25
GET /v2/meal/configs?meal_type=dinner&date=2025-12-26
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "meal_type": "breakfast",
      "name": "营养早餐",
      "price": "28.00",
      "order_start_time": "06:00:00",
      "order_end_time": "20:00:00",
      "advance_days": 1,
      "description": "包含牛奶、鸡蛋、面包等",
      "status": 1,
      "is_available": true
    },
    {
      "id": 2,
      "meal_type": "lunch",
      "name": "精品午餐",
      "price": "58.00",
      "order_start_time": "06:00:00",
      "order_end_time": "10:00:00",
      "advance_days": 1,
      "description": "两荤两素一汤",
      "status": 1,
      "is_available": false
    }
  ]
}
```

---

## 字段说明

### 订餐配置字段
| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | integer | 配置ID |
| `meal_type` | string | 餐型：`breakfast`=早餐，`lunch`=午餐，`dinner`=晚餐 |
| `name` | string | 餐品名称 |
| `price` | string | 价格（元） |
| `order_start_time` | string | 订餐开始时间（HH:mm:ss） |
| `order_end_time` | string | 订餐截止时间（HH:mm:ss） |
| `advance_days` | integer | 需提前预订天数（0=当天可订，1=需提前1天） |
| `description` | string | 餐品描述 |
| `status` | integer | 状态：1=启用，0=禁用 |
| `is_available` | boolean | 是否可预订（仅当传入`date`参数时返回） |

### 可预订判断逻辑
当传入`date`参数时，系统会根据以下规则判断`is_available`：
1. 配置状态必须为启用（status=1）
2. 目标日期距今天数必须 >= `advance_days`
3. 若目标日期距今天数 = `advance_days`（即刚好满足提前天数要求），还需当前时间 <= `order_end_time`

**示例**：
- `advance_days=1`，`order_end_time=20:00:00`
- 今天是12月24日 15:00，查询12月25日的配置 → `is_available=true`
- 今天是12月24日 21:00，查询12月25日的配置 → `is_available=false`（已过截止时间）
- 今天是12月24日 15:00，查询12月24日的配置 → `is_available=false`（不满足提前1天）

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 500 | 服务器内部错误 |
