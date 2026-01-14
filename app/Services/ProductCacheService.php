<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductCacheService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'products_supplier_';

    /**
     * Get products by supplier from cache
     *
     * @param int $supplierId
     * @return array
     */
    public function getProductsBySupplier(int $supplierId): array
    {
        $cacheKey = $this->getCacheKey($supplierId);

        try {
            return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($supplierId) {
                Log::info("Cache MISS: Loading products from database for supplier {$supplierId}");

                return Product::where('supplier_id', $supplierId)
                    ->select('id', 'reference', 'name', 'color', 'price', 'supplier_id')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::error("Cache error for supplier {$supplierId}: " . $e->getMessage());

            // Fallback to database if cache fails
            return Product::where('supplier_id', $supplierId)
                ->select('id', 'reference', 'name', 'color', 'price', 'supplier_id')
                ->orderBy('name')
                ->get()
                ->toArray();
        }
    }

    /**
     * Clear cache for a specific supplier
     *
     * @param int $supplierId
     * @return void
     */
    public function clearSupplierCache(int $supplierId): void
    {
        $cacheKey = $this->getCacheKey($supplierId);

        try {
            Cache::forget($cacheKey);
            Log::info("Cache cleared for supplier {$supplierId}");
        } catch (\Exception $e) {
            Log::error("Error clearing cache for supplier {$supplierId}: " . $e->getMessage());
        }
    }

    /**
     * Clear all product caches
     *
     * @return void
     */
    public function clearAllProductCaches(): void
    {
        try {
            $suppliers = \App\Models\Supplier::pluck('id');

            foreach ($suppliers as $supplierId) {
                $this->clearSupplierCache($supplierId);
            }

            Log::info("All product caches cleared");
        } catch (\Exception $e) {
            Log::error("Error clearing all product caches: " . $e->getMessage());
        }
    }

    /**
     * Warm up cache for a specific supplier
     * Pre-load products into cache
     *
     * @param int $supplierId
     * @return void
     */
    public function warmUpCache(int $supplierId): void
    {
        $this->clearSupplierCache($supplierId);
        $this->getProductsBySupplier($supplierId);
        Log::info("Cache warmed up for supplier {$supplierId}");
    }

    /**
     * Get cache key for a supplier
     *
     * @param int $supplierId
     * @return string
     */
    protected function getCacheKey(int $supplierId): string
    {
        return self::CACHE_PREFIX . $supplierId;
    }

    /**
     * Check if cache exists for a supplier
     *
     * @param int $supplierId
     * @return bool
     */
    public function hasCachedProducts(int $supplierId): bool
    {
        return Cache::has($this->getCacheKey($supplierId));
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        $suppliers = \App\Models\Supplier::pluck('id');
        $stats = [
            'total_suppliers' => $suppliers->count(),
            'cached_suppliers' => 0,
            'cache_keys' => [],
        ];

        foreach ($suppliers as $supplierId) {
            $cacheKey = $this->getCacheKey($supplierId);
            if (Cache::has($cacheKey)) {
                $stats['cached_suppliers']++;
                $stats['cache_keys'][] = $cacheKey;
            }
        }

        return $stats;
    }
}

