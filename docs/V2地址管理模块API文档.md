# V2 地址管理模块 API 文档

## 基础信息

- **Base URL**: `/v2/user/addresses`
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **认证方式**: 需要V2用户认证（中间件：`v2.user.auth`）

---

## 1. 获取地址列表

### 接口信息
- **URL**: `/v2/user/addresses`
- **Method**: `GET`
- **认证**: 需要认证

### 请求参数
无

### 请求示例
```
GET /v2/user/addresses
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
      "user_id": 10,
      "receiver_name": "张三",
      "receiver_phone": "13800138000",
      "province": "广东省",
      "city": "深圳市",
      "district": "南山区",
      "detail_address": "科技园南区某某大厦10楼",
      "is_default": 1,
      "created_at": "2025-12-20T10:30:00.000000Z",
      "updated_at": "2025-12-24T15:20:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 10,
      "receiver_name": "李四",
      "receiver_phone": "13900139000",
      "province": "广东省",
      "city": "深圳市",
      "district": "福田区",
      "detail_address": "华强北某某商场3楼",
      "is_default": 0,
      "created_at": "2025-12-22T14:15:00.000000Z",
      "updated_at": "2025-12-22T14:15:00.000000Z"
    }
  ]
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
| `id` | integer | 地址ID |
| `user_id` | integer | 用户ID |
| `receiver_name` | string | 收货人姓名 |
| `receiver_phone` | string | 收货人手机号 |
| `province` | string | 省份 |
| `city` | string | 城市 |
| `district` | string | 区/县 |
| `detail_address` | string | 详细地址 |
| `is_default` | integer | 是否默认地址（1=是，0=否） |
| `created_at` | string | 创建时间（ISO 8601格式） |
| `updated_at` | string | 更新时间（ISO 8601格式） |

---

## 2. 获取地址详情

### 接口信息
- **URL**: `/v2/user/addresses/{id}`
- **Method**: `GET`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 地址ID |

### 请求示例
```
GET /v2/user/addresses/1
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "获取成功",
  "data": {
    "id": 1,
    "user_id": 10,
    "receiver_name": "张三",
    "receiver_phone": "13800138000",
    "province": "广东省",
    "city": "深圳市",
    "district": "南山区",
    "detail_address": "科技园南区某某大厦10楼",
    "is_default": 1,
    "created_at": "2025-12-20T10:30:00.000000Z",
    "updated_at": "2025-12-24T15:20:00.000000Z"
  }
}
```

**地址不存在 (404)**:
```json
{
  "code": 404,
  "message": "地址不存在",
  "data": null
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

## 3. 创建地址

### 接口信息
- **URL**: `/v2/user/addresses`
- **Method**: `POST`
- **认证**: 需要认证

### 请求参数（Body）
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `receiver_name` | string | 是 | 收货人姓名（最大50字符） |
| `receiver_phone` | string | 是 | 收货人手机号（11位数字） |
| `province` | string | 是 | 省份（最大50字符） |
| `city` | string | 是 | 城市（最大50字符） |
| `district` | string | 是 | 区/县（最大50字符） |
| `detail_address` | string | 是 | 详细地址（最大200字符） |
| `is_default` | boolean | 否 | 是否设为默认地址（默认false） |

### 请求示例
```json
POST /v2/user/addresses
Content-Type: application/json

{
  "receiver_name": "王五",
  "receiver_phone": "13700137000",
  "province": "广东省",
  "city": "深圳市",
  "district": "宝安区",
  "detail_address": "西乡街道某某小区5栋201",
  "is_default": false
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "创建成功",
  "data": {
    "id": 3,
    "user_id": 10,
    "receiver_name": "王五",
    "receiver_phone": "13700137000",
    "province": "广东省",
    "city": "深圳市",
    "district": "宝安区",
    "detail_address": "西乡街道某某小区5栋201",
    "is_default": 0,
    "created_at": "2025-12-25T09:30:00.000000Z",
    "updated_at": "2025-12-25T09:30:00.000000Z"
  }
}
```

**参数验证失败 (400)**:
```json
{
  "code": 400,
  "message": "收货人姓名不能为空",
  "data": null
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

### 业务说明
- 如果设置为默认地址，系统会自动将该用户的其他地址设为非默认
- 如果是用户的第一个地址，会自动设为默认地址

---

## 4. 更新地址

### 接口信息
- **URL**: `/v2/user/addresses/{id}`
- **Method**: `PUT`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 地址ID |

### 请求参数（Body）
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `receiver_name` | string | 否 | 收货人姓名（最大50字符） |
| `receiver_phone` | string | 否 | 收货人手机号（11位数字） |
| `province` | string | 否 | 省份（最大50字符） |
| `city` | string | 否 | 城市（最大50字符） |
| `district` | string | 否 | 区/县（最大50字符） |
| `detail_address` | string | 否 | 详细地址（最大200字符） |
| `is_default` | boolean | 否 | 是否设为默认地址 |

### 请求示例
```json
PUT /v2/user/addresses/3
Content-Type: application/json

{
  "receiver_name": "王五",
  "receiver_phone": "13700137001",
  "detail_address": "西乡街道某某小区5栋202"
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "更新成功",
  "data": {
    "id": 3,
    "user_id": 10,
    "receiver_name": "王五",
    "receiver_phone": "13700137001",
    "province": "广东省",
    "city": "深圳市",
    "district": "宝安区",
    "detail_address": "西乡街道某某小区5栋202",
    "is_default": 0,
    "created_at": "2025-12-25T09:30:00.000000Z",
    "updated_at": "2025-12-25T10:15:00.000000Z"
  }
}
```

**地址不存在 (404)**:
```json
{
  "code": 404,
  "message": "地址不存在",
  "data": null
}
```

**参数验证失败 (400)**:
```json
{
  "code": 400,
  "message": "手机号格式不正确",
  "data": null
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

### 业务说明
- 只能更新当前用户自己的地址
- 如果设置为默认地址，系统会自动将该用户的其他地址设为非默认

---

## 5. 删除地址

### 接口信息
- **URL**: `/v2/user/addresses/{id}`
- **Method**: `DELETE`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 地址ID |

### 请求示例
```
DELETE /v2/user/addresses/3
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

**地址不存在 (404)**:
```json
{
  "code": 404,
  "message": "地址不存在",
  "data": null
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

### 业务说明
- 只能删除当前用户自己的地址
- 如果删除的是默认地址，系统会自动将最早创建的地址设为默认

---

## 6. 设置默认地址

### 接口信息
- **URL**: `/v2/user/addresses/{id}/default`
- **Method**: `POST`
- **认证**: 需要认证

### 路径参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `id` | integer | 是 | 地址ID |

### 请求参数
无

### 请求示例
```
POST /v2/user/addresses/2/default
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "设置成功",
  "data": {
    "id": 2,
    "user_id": 10,
    "receiver_name": "李四",
    "receiver_phone": "13900139000",
    "province": "广东省",
    "city": "深圳市",
    "district": "福田区",
    "detail_address": "华强北某某商场3楼",
    "is_default": 1,
    "created_at": "2025-12-22T14:15:00.000000Z",
    "updated_at": "2025-12-25T10:30:00.000000Z"
  }
}
```

**地址不存在 (404)**:
```json
{
  "code": 404,
  "message": "地址不存在",
  "data": null
}
```

**未认证响应 (401)**:
```json
{
  "code": 401,
  "message": "用户未认证"
}
```

### 业务说明
- 只能设置当前用户自己的地址为默认
- 设置成功后，该用户的其他地址会自动设为非默认

---

## 通用错误码

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 400 | 请求参数错误或验证失败 |
| 401 | 用户未认证 |
| 404 | 地址不存在 |
| 500 | 服务器内部错误 |

---

## 业务规则说明

### 默认地址规则
1. 每个用户只能有一个默认地址
2. 创建第一个地址时，自动设为默认地址
3. 设置新的默认地址时，原默认地址自动变为非默认
4. 删除默认地址时，系统自动将最早创建的地址设为默认

### 数据验证规则
- 收货人姓名：必填，最大50字符
- 收货人手机号：必填，11位数字
- 省份/城市/区县：必填，最大50字符
- 详细地址：必填，最大200字符

### 权限控制
- 用户只能查看、创建、修改、删除自己的地址
- 所有接口都需要用户认证
