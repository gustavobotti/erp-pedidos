<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductCacheService;

class ProductObserver
{
    protected $cacheService;

    public function __construct(ProductCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->cacheService->clearSupplierCache($product->supplier_id);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->cacheService->clearSupplierCache($product->supplier_id);

        // If supplier changed, clear cache for the old supplier too
        if ($product->isDirty('supplier_id')) {
            $oldSupplierId = $product->getOriginal('supplier_id');
            $this->cacheService->clearSupplierCache($oldSupplierId);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->cacheService->clearSupplierCache($product->supplier_id);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->cacheService->clearSupplierCache($product->supplier_id);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->cacheService->clearSupplierCache($product->supplier_id);
    }
}
