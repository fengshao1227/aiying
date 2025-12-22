# 瑷婴月子中心小程序后端 API 开发计划

> 生成时间：2025-12-22
> 项目：瑷婴月子中心小程序后端（Laravel 12）
> 目标：整合老系统业务模块，实现完整的小程序端和后台管理API

---

## 一、项目概述

### 1.1 背景
- 原有PHP后端包含房态管理、客户管理等核心业务
- 现需要迁移到Laravel 12框架，整合所有业务模块
- 同时支持小程序端和后台管理系统

### 1.2 技术栈
- **后端框架**：Laravel 12.x
- **数据库**：MySQL 8.0+
- **API风格**：RESTful API
- **认证方式**：
  - 小程序端：微信 openid（Header: `X-Openid`）
  - 后台管理：JWT Token（Header: `Authorization: Bearer {token}`）

---

## 二、数据库设计

### 2.1 现有表结构（已实现）

| 表名 | 说明 | 状态 |
|------|------|------|
| users | 用户表 | ✅ 已创建 |
| product_categories | 商品分类 | ✅ 已创建 |
| products | 商品信息 | ✅ 已创建 |
| product_images | 商品图片 | ✅ 已创建 |
| product_specifications | 商品规格SKU | ✅ 已创建 |
| shopping_cart | 购物车 | ✅ 已创建 |
| shipping_addresses | 收货地址 | ✅ 已创建 |
| orders | 订单表 | ✅ 已创建 |
| order_items | 订单明细 | ✅ 已创建 |
| family_meal_packages | 家庭套餐 | ✅ 已创建 |
| payments | 支付记录 | ✅ 已创建 |
| points_history | 积分历史 | ✅ 已创建 |

### 2.2 新增表结构（待实现）

| 表名 | 说明 | 优先级 |
|------|------|--------|
| customers | 客户档案表 | 🔴 高 |
| rooms | 房间信息表 | 🔴 高 |
| room_status | 房态记录表 | 🔴 高 |
| score_card_records | 评分卡表 | 🟡 中 |
| admins | 管理员表 | 🔴 高 |

### 2.3 表关联设计

```
users (phone) ← 1:1 → customers (phone)  【通过手机号关联】
customers ← 1:N → room_status (customer_id)
rooms ← 1:N → room_status (room_id)
customers ← 1:N → score_card_records (customer_id)
customers ← 1:N → orders (user_id通过phone关联)
```

---

## 三、API 接口设计

### 3.1 小程序端 API（/api）

#### 3.1.1 认证与用户模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/auth/wechat-login` | POST | 微信登录 | ✅ 已实现 |
| `/api/auth/user-info` | GET | 获取用户信息 | ✅ 已实现 |
| `/api/auth/user` | PUT | 更新用户信息 | ✅ 已实现 |

#### 3.1.2 商品模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/categories` | GET | 分类列表 | ✅ 已实现 |
| `/api/categories/{id}` | GET | 分类详情 | ✅ 已实现 |
| `/api/products` | GET | 商品列表（分页、筛选） | ✅ 已实现 |
| `/api/products/hot` | GET | 热门商品 | ✅ 已实现 |
| `/api/products/{id}` | GET | 商品详情 | ✅ 已实现 |

#### 3.1.3 购物车模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/cart` | GET | 购物车列表 | ✅ 已实现 |
| `/api/cart` | POST | 添加到购物车 | ✅ 已实现 |
| `/api/cart/{id}` | PUT | 更新数量 | ✅ 已实现 |
| `/api/cart/{id}` | DELETE | 删除单项 | ✅ 已实现 |
| `/api/cart` | DELETE | 清空购物车 | ✅ 已实现 |

#### 3.1.4 收货地址模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/addresses` | GET | 地址列表 | ✅ 已实现 |
| `/api/addresses/default` | GET | 默认地址 | ✅ 已实现 |
| `/api/addresses` | POST | 新增地址 | ✅ 已实现 |
| `/api/addresses/{id}` | PUT | 更新地址 | ✅ 已实现 |
| `/api/addresses/{id}` | DELETE | 删除地址 | ✅ 已实现 |
| `/api/addresses/{id}/set-default` | POST | 设为默认 | ✅ 已实现 |

#### 3.1.5 订单模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/orders` | GET | 订单列表 | ✅ 已实现 |
| `/api/orders/{id}` | GET | 订单详情 | ✅ 已实现 |
| `/api/orders` | POST | 创建订单 | ✅ 已实现 |
| `/api/orders/{id}/pay` | POST | 发起支付 | ⏳ 待实现 |
| `/api/orders/{id}/cancel` | POST | 取消订单 | ✅ 已实现 |
| `/api/orders/{id}/confirm` | POST | 确认收货 | ✅ 已实现 |

#### 3.1.6 家庭套餐模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/family-meals` | GET | 套餐列表 | ✅ 已实现 |
| `/api/family-meals/{id}` | GET | 套餐详情 | ✅ 已实现 |

#### 3.1.7 积分模块
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/points` | GET | 积分历史 | ✅ 已实现 |
| `/api/points/balance` | GET | 积分余额 | ✅ 已实现 |

#### 3.1.8 支付回调
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/payments/wechat/notify` | POST | 微信支付回调 | ⏳ 待实现 |

#### 3.1.9 🆕 客户相关（新增）
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/api/customer/info` | GET | 获取关联的客户档案 | ⏳ 待实现 |
| `/api/customer/room-status` | GET | 查看入住房间状态 | ⏳ 待实现 |
| `/api/customer/score-cards` | GET | 查看评分卡记录 | ⏳ 待实现 |

---

### 3.2 后台管理 API（/admin）

#### 3.2.1 管理员认证
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/login` | POST | 管理员登录 | ⏳ 待实现 |
| `/admin/logout` | POST | 退出登录 | ⏳ 待实现 |
| `/admin/info` | GET | 管理员信息 | ⏳ 待实现 |

#### 3.2.2 商品管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/products` | GET | 商品列表 | ⏳ 待实现 |
| `/admin/products` | POST | 创建商品 | ⏳ 待实现 |
| `/admin/products/{id}` | GET | 商品详情 | ⏳ 待实现 |
| `/admin/products/{id}` | PUT | 更新商品 | ⏳ 待实现 |
| `/admin/products/{id}` | DELETE | 删除商品 | ⏳ 待实现 |
| `/admin/products/{id}/images` | POST | 上传商品图片 | ⏳ 待实现 |
| `/admin/products/{id}/specs` | POST | 添加SKU规格 | ⏳ 待实现 |

#### 3.2.3 分类管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/categories` | GET | 分类列表 | ⏳ 待实现 |
| `/admin/categories` | POST | 创建分类 | ⏳ 待实现 |
| `/admin/categories/{id}` | PUT | 更新分类 | ⏳ 待实现 |
| `/admin/categories/{id}` | DELETE | 删除分类 | ⏳ 待实现 |

#### 3.2.4 订单管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/orders` | GET | 订单列表 | ⏳ 待实现 |
| `/admin/orders/{id}` | GET | 订单详情 | ⏳ 待实现 |
| `/admin/orders/{id}/ship` | POST | 订单发货 | ⏳ 待实现 |
| `/admin/orders/{id}/refund` | POST | 订单退款 | ⏳ 待实现 |

#### 3.2.5 用户管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/users` | GET | 用户列表 | ⏳ 待实现 |
| `/admin/users/{id}` | GET | 用户详情 | ⏳ 待实现 |
| `/admin/users/{id}/status` | PUT | 禁用/启用用户 | ⏳ 待实现 |
| `/admin/users/{id}/points` | POST | 调整积分 | ⏳ 待实现 |

#### 3.2.6 🆕 客户管理（CRM）
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/customers` | GET | 客户列表 | ⏳ 待实现 |
| `/admin/customers` | POST | 创建客户档案 | ⏳ 待实现 |
| `/admin/customers/{id}` | GET | 客户详情 | ⏳ 待实现 |
| `/admin/customers/{id}` | PUT | 更新客户档案 | ⏳ 待实现 |
| `/admin/customers/{id}` | DELETE | 删除客户 | ⏳ 待实现 |

#### 3.2.7 🆕 房态管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/rooms` | GET | 房间列表 | ⏳ 待实现 |
| `/admin/rooms` | POST | 创建房间 | ⏳ 待实现 |
| `/admin/rooms/{id}` | GET | 房间详情 | ⏳ 待实现 |
| `/admin/rooms/{id}` | PUT | 更新房间 | ⏳ 待实现 |
| `/admin/rooms/{id}` | DELETE | 删除房间 | ⏳ 待实现 |
| `/admin/rooms/status` | GET | 房态查询（按月份） | ⏳ 待实现 |
| `/admin/rooms/{id}/checkin` | POST | 办理入住 | ⏳ 待实现 |
| `/admin/rooms/{id}/checkout` | POST | 办理退房 | ⏳ 待实现 |

#### 3.2.8 🆕 评分卡管理
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/score-cards` | GET | 评分卡列表 | ⏳ 待实现 |
| `/admin/customers/{id}/score-cards` | GET | 客户评分卡 | ⏳ 待实现 |
| `/admin/score-cards` | POST | 创建评分记录 | ⏳ 待实现 |
| `/admin/score-cards/{id}` | PUT | 更新评分 | ⏳ 待实现 |

#### 3.2.9 统计报表
| 接口 | 方法 | 说明 | 状态 |
|------|------|------|------|
| `/admin/stats/overview` | GET | 总览统计 | ⏳ 待实现 |
| `/admin/stats/sales` | GET | 销售统计 | ⏳ 待实现 |
| `/admin/stats/occupancy` | GET | 🆕 入住率统计 | ⏳ 待实现 |

---

## 四、开发任务分解

### 阶段一：数据库准备（优先级：🔴 高）
**预计工时：4小时**

- [ ] 创建 `customers` 表迁移文件
- [ ] 创建 `rooms` 表迁移文件
- [ ] 创建 `room_status` 表迁移文件
- [ ] 创建 `score_card_records` 表迁移文件
- [ ] 创建 `admins` 表迁移文件
- [ ] 执行迁移，验证表结构

### 阶段二：Models 创建（优先级：🔴 高）
**预计工时：3小时**

- [ ] 创建 `Customer` Model
- [ ] 创建 `Room` Model
- [ ] 创建 `RoomStatus` Model
- [ ] 创建 `ScoreCardRecord` Model
- [ ] 创建 `Admin` Model
- [ ] 定义Model关系（hasMany, belongsTo等）
- [ ] 配置模型属性（fillable, casts, dates等）

### 阶段三：后台管理认证系统（优先级：🔴 高）
**预计工时：6小时**

- [ ] 安装并配置 JWT 认证
- [ ] 创建 `AdminAuthController`
- [ ] 实现管理员登录逻辑
- [ ] 实现管理员退出逻辑
- [ ] 创建 `AdminMiddleware` 鉴权中间件
- [ ] 配置后台路由保护

### 阶段四：客户管理模块（优先级：🔴 高）
**预计工时：8小时**

- [ ] 创建 `Admin/CustomerController`
- [ ] 实现客户列表API（分页、搜索、筛选）
- [ ] 实现创建客户档案API
- [ ] 实现客户详情API
- [ ] 实现更新客户档案API
- [ ] 实现删除客户API
- [ ] 添加客户与用户关联查询
- [ ] 编写API文档

### 阶段五：房态管理模块（优先级：🔴 高）
**预计工时：10小时**

- [ ] 创建 `Admin/RoomController`
- [ ] 实现房间列表API（分页、按楼层筛选）
- [ ] 实现创建房间API
- [ ] 实现房间详情API
- [ ] 实现更新房间API
- [ ] 实现删除房间API
- [ ] 创建 `Admin/RoomStatusController`
- [ ] 实现房态查询API（按月份查询）
- [ ] 实现办理入住API
- [ ] 实现办理退房API
- [ ] 实现房态日历视图数据API
- [ ] 编写API文档

### 阶段六：评分卡模块（优先级：🟡 中）
**预计工时：4小时**

- [ ] 创建 `Admin/ScoreCardController`
- [ ] 实现评分卡列表API
- [ ] 实现客户评分卡查询API
- [ ] 实现创建评分记录API
- [ ] 实现更新评分API
- [ ] 编写API文档

### 阶段七：商品与分类管理（优先级：🟡 中）
**预计工时：8小时**

- [ ] 创建 `Admin/ProductController`
- [ ] 实现商品列表API（分页、搜索、筛选）
- [ ] 实现创建商品API
- [ ] 实现商品详情API
- [ ] 实现更新商品API
- [ ] 实现删除商品API（软删除）
- [ ] 实现商品图片上传API
- [ ] 实现SKU规格管理API
- [ ] 创建 `Admin/CategoryController`
- [ ] 实现分类CRUD API
- [ ] 编写API文档

### 阶段八：订单管理（优先级：🟡 中）
**预计工时：6小时**

- [ ] 创建 `Admin/OrderController`
- [ ] 实现订单列表API（分页、状态筛选）
- [ ] 实现订单详情API
- [ ] 实现订单发货API
- [ ] 实现订单退款API
- [ ] 实现订单状态更新逻辑
- [ ] 编写API文档

### 阶段九：用户与积分管理（优先级：🟡 中）
**预计工时：4小时**

- [ ] 创建 `Admin/UserController`
- [ ] 实现用户列表API
- [ ] 实现用户详情API
- [ ] 实现用户状态切换API
- [ ] 实现积分调整API
- [ ] 实现积分日志查询API
- [ ] 编写API文档

### 阶段十：统计报表（优先级：🟢 低）
**预计工时：6小时**

- [ ] 创建 `Admin/StatsController`
- [ ] 实现总览统计API（订单、用户、销售额）
- [ ] 实现销售统计API（按日期范围）
- [ ] 实现入住率统计API（按月份）
- [ ] 实现商品销量排行API
- [ ] 编写API文档

### 阶段十一：小程序端客户相关API（优先级：🟡 中）
**预计工时：4小时**

- [ ] 创建 `Api/CustomerController`
- [ ] 实现获取关联客户档案API
- [ ] 实现查看入住房间状态API
- [ ] 实现查看评分卡记录API
- [ ] 编写API文档

### 阶段十二：支付功能完善（优先级：🔴 高）
**预计工时：8小时**

- [ ] 配置微信支付SDK
- [ ] 实现订单支付API
- [ ] 实现微信支付回调处理
- [ ] 实现支付状态查询API
- [ ] 实现支付记录管理
- [ ] 测试支付流程
- [ ] 编写API文档

### 阶段十三：前端API调用更新（优先级：🔴 高）
**预计工时：12小时**

- [ ] 更新前端 `request.js` 为RESTful风格
- [ ] 重写 `users.js` API调用
- [ ] 重写 `products.js` API调用
- [ ] 重写 `shopping_cart.js` API调用
- [ ] 重写 `shipping_addresses.js` API调用
- [ ] 重写 `goods_order_new.js` API调用
- [ ] 重写 `family_meals_new.js` API调用
- [ ] 重写 `points.js` API调用
- [ ] 添加新的客户相关API调用
- [ ] 测试所有API调用
- [ ] 更新前端错误处理

### 阶段十四：测试与优化（优先级：🔴 高）
**预计工时：8小时**

- [ ] API接口联调测试
- [ ] 性能优化（数据库查询优化）
- [ ] 添加API限流保护
- [ ] 添加日志记录
- [ ] 安全性检查
- [ ] 文档完善
- [ ] 部署到生产环境

---

## 五、技术要点

### 5.1 认证机制

**小程序端**：
```php
// 中间件: app/Http/Middleware/WechatAuth.php
// 从Header获取openid
$openid = $request->header('X-Openid');
$user = User::where('openid', $openid)->firstOrFail();
$request->user = $user;
```

**后台管理**：
```php
// 使用JWT Token认证
// 中间件: app/Http/Middleware/AdminAuth.php
$token = $request->bearerToken();
$admin = JWTAuth::parseToken()->authenticate();
```

### 5.2 数据关联

**通过手机号关联用户和客户**：
```php
// Customer Model
public function user()
{
    return $this->hasOne(User::class, 'phone', 'phone');
}

// User Model
public function customer()
{
    return $this->hasOne(Customer::class, 'phone', 'phone');
}
```

### 5.3 统一响应格式

```php
// 成功响应
return response()->json([
    'code' => 200,
    'message' => '操作成功',
    'data' => $data
]);

// 错误响应
return response()->json([
    'code' => 400,
    'message' => '参数错误',
    'data' => null
], 400);
```

### 5.4 房态查询优化

```php
// 按月份查询房态，使用索引优化
RoomStatus::where('record_month', '2025-12')
    ->with(['room', 'customer'])
    ->get();
```

---

## 六、时间规划

### 6.1 总体工时估算
- 数据库准备：4小时
- Models创建：3小时
- 后台认证系统：6小时
- 客户管理：8小时
- 房态管理：10小时
- 评分卡管理：4小时
- 商品与分类管理：8小时
- 订单管理：6小时
- 用户与积分管理：4小时
- 统计报表：6小时
- 小程序端客户API：4小时
- 支付功能：8小时
- 前端API更新：12小时
- 测试与优化：8小时

**总计：91小时**

### 6.2 开发周期（按每天8小时工作）
- 🔴 高优先级任务：约52小时（7个工作日）
- 🟡 中优先级任务：约30小时（4个工作日）
- 🟢 低优先级任务：约9小时（1个工作日）

**预计总开发周期：12个工作日**

---

## 七、风险与注意事项

### 7.1 技术风险
1. **数据迁移风险**：老数据库数据需要完整迁移到新表结构
2. **性能风险**：房态查询可能涉及大量数据，需要优化查询
3. **并发风险**：房间入住/退房可能存在并发操作，需要加锁

### 7.2 业务风险
1. **客户关联**：需要确保手机号的唯一性和准确性
2. **房态冲突**：同一房间可能被重复分配，需要业务逻辑校验
3. **评分卡逻辑**：评分卡号的生成规则需要明确

### 7.3 注意事项
1. 所有敏感数据需要加密存储（如管理员密码）
2. API需要做限流保护，防止恶意请求
3. 订单和支付相关操作需要添加事务保护
4. 房态操作需要记录操作日志
5. 测试环境和生产环境需要分离配置

---

## 八、后续扩展计划

### 8.1 短期扩展（1个月内）
- 添加消息推送功能（企业微信通知）
- 实现数据导出功能（Excel导出）
- 添加数据统计图表（ECharts）

### 8.2 中期扩展（3个月内）
- 开发后台管理前端（Vue 3 + Element Plus）
- 实现移动端H5管理页面
- 添加实时监控Dashboard

### 8.3 长期扩展（6个月内）
- 实现多门店支持
- 添加BI数据分析模块
- 实现客户画像系统

---

## 九、联系方式

**技术负责人**：Claude Code AI
**项目地址**：`/Users/li/Desktop/work/aiying-backend`
**数据库**：`aiying_health` @ `82.157.47.215:3306`
**服务器**：`82.157.47.215`
**域名**：`https://aiying.qdhs.cloud`

---

## 十、版本记录

| 版本 | 日期 | 说明 | 作者 |
|------|------|------|------|
| v1.0 | 2025-12-22 | 初始版本，完整规划 | Claude |

---

**文档状态**：✅ 已完成
**下一步行动**：等待用户确认后开始实施
