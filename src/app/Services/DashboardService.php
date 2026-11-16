<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Alert;
use App\Models\StockMovement;
use App\Models\StockTransaction;

/**
 * MODULE 6: Inventory Dashboard and Reporting
 * Owner: TODO (name)
 * Reads data produced by the other modules. Build the UI with the seed data first.
 */
class DashboardService
{
    /** TODO: numbers for the summary cards (products, open alerts, expired batches...). */
    public function getSummaryCounts(): array
    {
        return [
            'total_products' => Product::count(),
            'expiring_batches' => Batch::where('expiry_date', '<=', now()->addDays(30))->count(),
            'low_stock_count' => $this->getLowStockItems()->count(),
            'total_batches' => Batch::count(),
        ];
    }

    /** TODO: products whose total stock <= min_stock_level. */
    public function getLowStockItems(): Collection
    {
        return Product::withSum('batches', 'current_quantity')
        -> get()
        ->filter(function ($product){
            return $product->batches_sum_current_quantity <= $product->min_stock_level;
        });
    }

    /** TODO: batches expiring within $days days (and already expired), earliest first. */
    public function getExpiringBatches(int $days = 30): Collection
    {
        return Batch::with('product')
        ->where('expiry_date', '<=', now()->addDays($days))
        ->orderBy('expiry_date', 'asc')
        ->get();
    }

    /** TODO: latest N stock transactions. */
    public function getRecentMovements(int $limit = 10): Collection
    {
        return StockTransaction::with(['batch.product', 'user'])
        -> latest('transaction_date')
        -> limit($limit)
        ->get();
    }

    /** TODO: rows for the basic inventory report (add CSV export later). */
    public function getInventoryReportData(array $filters = []): Collection
    {
        $query = Product::with(['category', 'batches']);

        if (!empty($filters['category_id'])){
            $query->where('category_id', $filters['category_id']);
        }

        return $query->get();
    }
}
