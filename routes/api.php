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
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{id}/confirm', [OrderController::class, 'confirm']);
});

// 家庭套餐路由
Route::prefix('family-meals')->group(function () {
    Route::get('/', [FamilyMealController::class, 'index']);
    Route::get('/{id}', [FamilyMealController::class, 'show']);
});

// 积分路由
Route::prefix('points')->group(function () {
    Route::get('/', [PointsController::class, 'index']);
    Route::get('/balance', [PointsController::class, 'balance']);
});
