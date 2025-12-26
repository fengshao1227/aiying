<?php

namespace App\Observers;

use App\Services\CacheService;
use App\Models\V2\MealOrder;

class MealOrderObserver
{
    public function created(MealOrder $order)
    {
        CacheService::clearDashboard();
    }

    public function updated(MealOrder $order)
    {
        CacheService::clearDashboard();
    }

    public function deleted(MealOrder $order)
    {
        CacheService::clearDashboard();
    }
}
