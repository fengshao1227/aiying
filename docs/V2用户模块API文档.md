# V2 用户模块 API 文档

## 基础信息

- **Base URL**: `http://127.0.0.1:8000/v2/user`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

---

## 1. 微信登录

### 接口信息
- **URL**: `/v2/user/login`
- **Method**: `POST`
- **认证**: 无需认证

### 请求参数
```json
{
  "code": "string" // 微信登录 code，必填
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token": "random_token_string",
    "user": {
      "id": 1,
      "nickname": "微信用户",
      "avatar": null,
      "gender": 0,
      "phone": null,
      "pointsBalance": 0,
      "isBound": false,
      "status": 1,
      "customer": null // 如果已绑定客户，这里会有客户信息
    }
  }
}
```

**错误响应**:
```json
// 缺少参数 (400)
{
  "code": 400,
  "message": "The code field is required.",
  "data": null
}

// 微信接口调用失败 (500)
{
  "code": 500,
  "message": "登录失败: 微信接口错误信息",
  "data": null
}
```

---

## 2. 获取用户信息

### 接口信息
- **URL**: `/v2/user/profile`
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
    "id": 1,
    "nickname": "微信用户",
    "avatar": null,
    "gender": 0,
    "phone": null,
    "bindPhone": null,
    "pointsBalance": 0,
    "isBound": false,
    "status": 1,
    "lastLoginAt": "2025-12-24T12:00:00.000Z",
    "customer": null // 如果已绑定，包含客户详细信息
  }
}
```

**错误响应**:
```json
// 未登录 (401)
{
  "code": 401,
  "message": "未登录，请先登录",
  "data": null
}

// 用户不存在 (401)
{
  "code": 401,
  "message": "用户不存在，请重新登录",
  "data": null
}

// 账号被禁用 (403)
{
  "code": 403,
  "message": "账号已被禁用",
  "data": null
}
```

---

## 3. 绑定月子中心客户

### 接口信息
- **URL**: `/v2/user/bindCustomer`
- **Method**: `POST`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 请求参数
```json
{
  "phone": "13800138000" // 月子中心登记手机号，必填，格式：1[3-9]xxxxxxxxx
}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "绑定成功",
  "data": {
    "id": 1,
    "nickname": "微信用户",
    "avatar": null,
    "gender": 0,
    "phone": null,
    "bindPhone": "13800138000",
    "pointsBalance": 0,
    "isBound": true,
    "status": 1,
    "customer": {
      "id": 1,
      "name": "张三",
      "phone": "13800138000",
      "packageName": "春季套餐",
      "babyName": "小宝",
      "checkInDate": "2025-12-01T00:00:00.000Z",
      "checkOutDate": "2025-12-31T00:00:00.000Z"
    }
  }
}
```

**错误响应**:
```json
// 手机号格式错误 (400)
{
  "code": 400,
  "message": "The phone field must match the format 1[3-9]xxxxxxxxx.",
  "data": null
}

// 已绑定客户 (400)
{
  "code": 400,
  "message": "您已绑定客户，无需重复绑定",
  "data": null
}

// 客户已被其他用户绑定 (400)
{
  "code": 400,
  "message": "该客户已被其他用户绑定",
  "data": null
}

// 客户不存在 (404)
{
  "code": 404,
  "message": "未找到该手机号对应的客户记录，请联系前台确认",
  "data": null
}
```

---

## 4. 解绑客户

### 接口信息
- **URL**: `/v2/user/unbindCustomer`
- **Method**: `POST`
- **认证**: 需要认证

### 请求头
```
X-Openid: user_openid_from_login
```

### 请求参数
```json
{}
```

### 响应格式
**成功响应 (200)**:
```json
{
  "code": 0,
  "message": "解绑成功",
  "data": {
    "id": 1,
    "nickname": "微信用户",
    "avatar": null,
    "gender": 0,
    "phone": null,
    "bindPhone": null,
    "pointsBalance": 0,
    "isBound": false,
    "status": 1
  }
}
```

**错误响应**:
```json
// 尚未绑定客户 (400)
{
  "code": 400,
  "message": "您尚未绑定客户",
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
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

---

## 测试数据

### 测试用户
由于微信登录需要真实的 code，建议：
1. 先在数据库手动插入测试用户
2. 使用该用户的 openid 进行接口测试

### 测试客户数据
可以在 `customers` 表中插入测试数据：
```sql
INSERT INTO customers (customer_name, phone, package_name, baby_name, created_at, updated_at)
VALUES ('测试客户', '13800138000', '春季套餐', '小宝', NOW(), NOW());
```

### 测试用户数据
```sql
INSERT INTO users (openid, nickname, status, points_balance, created_at, updated_at)
VALUES ('test_openid_123', '测试用户', 1, 0, NOW(), NOW());
```

然后使用 `X-Openid: test_openid_123` 进行认证测试。