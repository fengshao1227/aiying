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
| `start_date` | string | 否 | 日历查询起始日期（格式：`Y-m-d`） |
| `end_date` | string | 否 | 日历查询结束日期（格式：`Y-m-d`），需与start_date一起使用 |

### 请求示例
```
GET /v2/meal/configs
GET /v2/meal/configs?meal_type=lunch
GET /v2/meal/configs?start_date=2025-12-25&end_date=2026-01-31
GET /v2/meal/configs?meal_type=dinner&start_date=2025-12-26&end_date=2026-01-15
```

### 响应格式
**成功响应 (200)**:
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
        "order_end_time": "20:00:00",
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
        "order_start_time": "06:00:00",
        "order_end_time": "10:00:00",
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

## 字段说明

### 订餐配置字段
| 字段 | 类型 | 说明 |
|------|------|------|
| `meals` | array | 餐次配置列表 |
| `meals[].id` | integer | 配置ID |
| `meals[].meal_type` | string | 餐型：`breakfast`=早餐，`lunch`=午餐，`dinner`=晚餐 |
| `meals[].name` | string | 餐品名称 |
| `meals[].price` | string | 价格（元） |
| `meals[].cover_image` | string | 封面图片URL |
| `meals[].order_start_time` | string | 订餐开始时间（HH:mm:ss） |
| `meals[].order_end_time` | string | 订餐截止时间（HH:mm:ss） |
| `meals[].advance_days` | integer | 需提前预订天数（0=当天可订，1=需提前1天） |
| `meals[].description` | string | 餐品描述 |
| `meals[].status` | integer | 状态：1=启用，0=禁用 |
| `unavailable_dates` | array | 不可预订日期列表（仅当传入start_date和end_date时返回） |

### 日历不可用日期判断逻辑
当传入`start_date`和`end_date`参数时，系统会遍历日期范围内的每一天，判断该日期是否有任一餐次可预订。若所有餐次均不可预订，则该日期加入`unavailable_dates`列表。

单个餐次可预订判断规则：
1. 配置状态必须为启用（status=1）
2. 目标日期距今天数必须 >= `advance_days`
3. 若目标日期距今天数 = `advance_days`（即刚好满足提前天数要求），还需当前时间 <= `order_end_time`

**示例**：
- `advance_days=1`，`order_end_time=20:00:00`
- 今天是12月24日 15:00，查询12月25日 → 可预订
- 今天是12月24日 21:00，查询12月25日 → 不可预订（已过截止时间）
- 今天是12月24日 15:00，查询12月24日 → 不可预订（不满足提前1天）

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误 |
| 500 | 服务器内部错误 |
