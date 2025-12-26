<?php

namespace App\Providers;

use App\Models\CachedPersonalAccessToken;
use App\Models\V2\MealOrder;
use App\Models\V2\Order;
use App\Models\V2\Product;
use App\Observers\MealOrderObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 使用带 Redis 缓存的 Token 模型
        Sanctum::usePersonalAccessTokenModel(CachedPersonalAccessToken::class);

        // 注册 Model Observers 用于缓存失效
        Order::observe(OrderObserver::class);
        MealOrder::observe(MealOrderObserver::class);
        Product::observe(ProductObserver::class);
    }
}
