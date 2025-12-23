<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShoppingCartController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FamilyMealController;
use App\Http\Controllers\Api\PointsController;
use Illuminate\Support\Facades\Route;

// 认证相关路由
Route::prefix('auth')->group(function () {
    Route::post('/wechat-login', [AuthController::class, 'wechatLogin']);
    Route::get('/user-info', [AuthController::class, 'getUserInfo']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/bind-phone', [AuthController::class, 'bindPhone']);
});

// 商品分类路由
Route::prefix('categories')->group(function () {
    Route::get('/', [ProductCategoryController::class, 'index']);
    Route::get('/{id}', [ProductCategoryController::class, 'show']);
});

// 商品路由
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/hot', [ProductController::class, 'hot']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

// 购物车路由
Route::prefix('cart')->group(function () {
    Route::get('/', [ShoppingCartController::class, 'index']);
    Route::post('/', [ShoppingCartController::class, 'store']);
    Route::put('/{id}', [ShoppingCartController::class, 'update']);
    Route::delete('/{id}', [ShoppingCartController::class, 'destroy']);
    Route::delete('/', [ShoppingCartController::class, 'clear']);
});

// 收货地址路由
Route::prefix('addresses')->group(function () {
    Route::get('/', [ShippingAddressController::class, 'index']);
    Route::get('/default', [ShippingAddressController::class, 'getDefault']);
    Route::post('/', [ShippingAddressController::class, 'store']);
    Route::put('/{id}', [ShippingAddressController::class, 'update']);
    Route::delete('/{id}', [ShippingAddressController::class, 'destroy']);
    Route::post('/{id}/set-default', [ShippingAddressController::class, 'setDefault']);
});

// 订单路由
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/{id}/pay', [\App\Http\Controllers\Api\PaymentController::class, 'pay']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{id}/confirm', [OrderController::class, 'confirm']);
});

// 家庭套餐路由
Route::prefix('family-meals')->group(function () {
    Route::get('/', [FamilyMealController::class, 'index']);
    Route::get('/{id}', [FamilyMealController::class, 'show']);
    // 家属订餐订单
    Route::post('/orders', [FamilyMealController::class, 'storeOrder']);
    Route::get('/orders/list', [FamilyMealController::class, 'orderList']);
    Route::get('/orders/{id}', [FamilyMealController::class, 'orderDetail']);
    Route::post('/orders/{id}/pay', [\App\Http\Controllers\Api\PaymentController::class, 'payFamilyMeal']);
    Route::post('/orders/{id}/cancel', [FamilyMealController::class, 'cancelOrder']);
});

// 积分路由
Route::prefix('points')->group(function () {
    Route::get('/', [PointsController::class, 'index']);
    Route::get('/balance', [PointsController::class, 'balance']);
});

// 支付回调路由（无需认证）
Route::post('/payments/wechat/notify', [\App\Http\Controllers\Api\PaymentController::class, 'wechatNotify']);

// ============================================================
// 后台管理API路由
// ============================================================

use App\Http\Controllers\Admin\AdminAuthController;

// 管理员认证路由（无需认证）
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// 管理员受保护路由（需要认证）
Route::prefix('admin')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    // 管理员信息
    Route::get('/info', [AdminAuthController::class, 'info']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);

    // 客户管理
    Route::prefix('customers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CustomerController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\CustomerController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'destroy']);
    });

    // 房间管理
    Route::prefix('rooms')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RoomController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\RoomController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'destroy']);
    });

    // 房态管理
    Route::prefix('room-status')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RoomStatusController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\RoomStatusController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\RoomStatusController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\RoomStatusController::class, 'destroy']);
        Route::post('/check-in', [\App\Http\Controllers\Admin\RoomStatusController::class, 'checkIn']);
        Route::post('/check-out', [\App\Http\Controllers\Admin\RoomStatusController::class, 'checkOut']);
    });

    // 评分卡管理
    Route::prefix('score-cards')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ScoreCardRecordController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\ScoreCardRecordController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\ScoreCardRecordController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\ScoreCardRecordController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ScoreCardRecordController::class, 'destroy']);
    });

    // 文件上传
    Route::prefix('upload')->group(function () {
        Route::post('/image', [\App\Http\Controllers\Admin\UploadController::class, 'uploadImage']);
        Route::delete('/image', [\App\Http\Controllers\Admin\UploadController::class, 'deleteImage']);
    });

    // 用户管理
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update']);
        Route::put('/{id}/points', [\App\Http\Controllers\Admin\UserController::class, 'updatePoints']);
        Route::get('/{id}/orders', [\App\Http\Controllers\Admin\UserController::class, 'orders']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    });

    // 商品管理
    Route::prefix('products')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProductAdminController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\ProductAdminController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\ProductAdminController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\ProductAdminController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ProductAdminController::class, 'destroy']);
    });

    // 商品分类管理
    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy']);
    });

    // 订单管理
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OrderAdminController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'show']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\OrderAdminController::class, 'updateStatus']);
        Route::put('/{id}/delivery', [\App\Http\Controllers\Admin\OrderAdminController::class, 'updateDelivery']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'destroy']);
    });

    // 家属订餐订单管理
    Route::prefix('family-meal-orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'show']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'updateStatus']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'destroy']);
    });

    // 套餐管理
    Route::prefix('packages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PackageController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\PackageController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\PackageController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\PackageController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\PackageController::class, 'destroy']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\PackageController::class, 'updateStatus']);
    });
});
