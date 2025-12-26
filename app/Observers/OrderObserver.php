<?php

namespace App\Observers;

use App\Services\CacheService;
use App\Models\V2\Order;

class OrderObserver
{
    public function created(Order $order)
    {
        CacheService::clearDashboard();
    }

    public function updated(Order $order)
    {
        CacheService::clearDashboard();
    }

    public function deleted(Order $order)
    {
        CacheService::clearDashboard();
    }
}
