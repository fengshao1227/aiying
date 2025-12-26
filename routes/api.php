<?php

use Illuminate\Support\Facades\Route;

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
        Route::get('/vacant', [\App\Http\Controllers\Admin\RoomStatusController::class, 'vacantRooms']);
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
        Route::post('/{id}/refund/approve', [\App\Http\Controllers\Admin\OrderAdminController::class, 'approveRefund']);
        Route::post('/{id}/refund/reject', [\App\Http\Controllers\Admin\OrderAdminController::class, 'rejectRefund']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'destroy']);
    });

    // 家属订餐订单管理
    Route::prefix('family-meal-orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'show']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'updateStatus']);
        Route::post('/{id}/refund/approve', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'approveRefund']);
        Route::post('/{id}/refund/reject', [\App\Http\Controllers\Admin\FamilyMealOrderController::class, 'rejectRefund']);
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

    // 报表统计
    Route::prefix('reports')->group(function () {
        Route::get('/meal-orders', [\App\Http\Controllers\Admin\Reports\MealOrderReportController::class, 'index']);
    });
});

// ============================================================
// V2 后台管理API路由（基于 V2 模型）
// ============================================================

Route::prefix('admin/v2')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    // 用户管理
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\UserController::class, 'index']);
        Route::get('/stats', [\App\Http\Controllers\Admin\V2\UserController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\UserController::class, 'show']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\V2\UserController::class, 'updateStatus']);
        Route::post('/{id}/adjust-points', [\App\Http\Controllers\Admin\V2\UserController::class, 'adjustPoints']);
        Route::get('/{id}/orders', [\App\Http\Controllers\Admin\V2\UserController::class, 'orders']);
    });

    // 商品分类管理
    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'update']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'updateStatus']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\V2\CategoryController::class, 'destroy']);
    });

    // 商品管理
    Route::prefix('products')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\ProductController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\ProductController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\V2\ProductController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\V2\ProductController::class, 'update']);
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\V2\ProductController::class, 'updateStatus']);
        Route::put('/{id}/stock', [\App\Http\Controllers\Admin\V2\ProductController::class, 'updateStock']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\V2\ProductController::class, 'destroy']);
    });

    // 商城订单管理
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\OrderController::class, 'index']);
        Route::get('/stats', [\App\Http\Controllers\Admin\V2\OrderController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\OrderController::class, 'show']);
        Route::post('/{id}/ship', [\App\Http\Controllers\Admin\V2\OrderController::class, 'ship']);
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\V2\OrderController::class, 'complete']);
        Route::post('/{id}/refund/approve', [\App\Http\Controllers\Admin\V2\OrderController::class, 'approveRefund']);
        Route::post('/{id}/refund/reject', [\App\Http\Controllers\Admin\V2\OrderController::class, 'rejectRefund']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\V2\OrderController::class, 'destroy']);
    });

    // 餐饮订单管理
    Route::prefix('meal-orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'index']);
        Route::get('/stats', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'show']);
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'complete']);
        Route::post('/{id}/refund/approve', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'approveRefund']);
        Route::post('/{id}/refund/reject', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'rejectRefund']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\V2\MealOrderController::class, 'destroy']);
    });

    // 系统配置管理
    Route::prefix('configs')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'index']);
        Route::get('/groups', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'groups']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'update']);
        Route::post('/batch', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'batchUpdate']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\V2\SystemConfigController::class, 'destroy']);
    });

    // 餐饮配置管理
    Route::prefix('meal-configs')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\V2\MealConfigController::class, 'index']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\V2\MealConfigController::class, 'update']);
    });
});
