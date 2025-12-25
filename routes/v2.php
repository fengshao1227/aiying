<?php

use App\Http\Controllers\V2\UserController;
use App\Http\Controllers\V2\ProductController;
use App\Http\Controllers\V2\CartController;
use App\Http\Controllers\V2\OrderController;
use App\Http\Controllers\V2\MealConfigController;
use App\Http\Controllers\V2\PointsController;
use App\Http\Controllers\V2\PaymentController;
use App\Http\Controllers\V2\AddressController;
use App\Http\Controllers\V2\ConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V2 API Routes
|--------------------------------------------------------------------------
|
| 这里定义 V2 版本的 API 路由
| 所有路由都以 /v2 为前缀
|
*/

// 用户模块路由
Route::prefix('user')->group(function () {
    // 无需认证的路由
    Route::post('/login', [UserController::class, 'login']);

    // 需要认证的路由
    Route::middleware('v2.user.auth')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/bindCustomer', [UserController::class, 'bindCustomer']);
        Route::post('/unbindCustomer', [UserController::class, 'unbindCustomer']);

        // 收货地址管理
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
            Route::post('/{id}/default', [AddressController::class, 'setDefault']);
        });
    });
});

// 商品模块路由
Route::prefix('products')->group(function () {
    // 无需认证的路由
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

// 购物车模块路由（需要认证）
Route::prefix('cart')->middleware('v2.user.auth')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/select', [CartController::class, 'select']);
    Route::put('/{id}', [CartController::class, 'update']);
    Route::delete('/clear', [CartController::class, 'clear']);
    Route::delete('/{id}', [CartController::class, 'destroy']);
});

// 订单模块路由（需要认证）
Route::prefix('orders')->middleware('v2.user.auth')->group(function () {
    // 商城订单
    Route::post('/mall', [OrderController::class, 'createMallOrder']);
    Route::get('/mall', [OrderController::class, 'getMallOrders']);
    Route::get('/mall/{id}', [OrderController::class, 'getMallOrderDetail']);
    Route::post('/mall/{id}/cancel', [OrderController::class, 'cancelMallOrder']);
    Route::post('/mall/{id}/pay', [PaymentController::class, 'payMallOrder']);
    Route::post('/mall/{id}/refund', [OrderController::class, 'refundMallOrder']);
    Route::post('/mall/{id}/confirm', [OrderController::class, 'confirmReceipt']);

    // 订餐订单
    Route::post('/meal', [OrderController::class, 'createMealOrder']);
    Route::get('/meal', [OrderController::class, 'getMealOrders']);
    Route::get('/meal/{id}', [OrderController::class, 'getMealOrderDetail']);
    Route::post('/meal/{id}/cancel', [OrderController::class, 'cancelMealOrder']);
    Route::post('/meal/{id}/pay', [PaymentController::class, 'payMealOrder']);
    Route::post('/meal/{id}/refund', [OrderController::class, 'refundMealOrder']);
});

// 支付回调（无需认证）
Route::post('/payments/notify', [PaymentController::class, 'notify']);

// 订餐配置路由
Route::get('/meal/configs', [MealConfigController::class, 'index']);

// 积分模块路由（需要认证）
Route::prefix('points')->middleware('v2.user.auth')->group(function () {
    Route::get('/balance', [PointsController::class, 'balance']);
    Route::get('/history', [PointsController::class, 'history']);
});

// 系统配置路由（公开）
Route::prefix('config')->group(function () {
    Route::get('/', [ConfigController::class, 'index']);
    Route::get('/points', [ConfigController::class, 'getPointsConfig']);
    Route::get('/{key}', [ConfigController::class, 'show']);
});