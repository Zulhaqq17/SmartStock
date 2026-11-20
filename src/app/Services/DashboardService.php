<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

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
        return [];
    }

    /** TODO: products whose total stock <= min_stock_level. */
    public function getLowStockItems(): Collection
    {
        return new Collection();
    }

    /** TODO: batches expiring within $days days (and already expired), earliest first. */
    public function getExpiringBatches(int $days = 30): Collection
    {
        return new Collection();
    }

    /** TODO: latest N stock transactions. */
    public function getRecentMovements(int $limit = 10): Collection
    {
        return new Collection();
    }

    /** TODO: rows for the basic inventory report (add CSV export later). */
    public function getInventoryReportData(array $filters = []): Collection
    {
        return new Collection();
    }
}
