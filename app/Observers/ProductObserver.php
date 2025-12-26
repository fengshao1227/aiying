<?php

namespace App\Observers;

use App\Services\CacheService;
use App\Models\V2\Product;

class ProductObserver
{
    public function created(Product $product)
    {
        CacheService::clearProduct();
    }

    public function updated(Product $product)
    {
        CacheService::clearProduct();
    }

    public function deleted(Product $product)
    {
        CacheService::clearProduct();
    }
}
