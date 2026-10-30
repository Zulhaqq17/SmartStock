<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

/**
 * MODULE 4: Stock Movement and Quantity Tracking
 * Owner: TODO (name)
 */
class StockMovementService
{
    /**
     * TODO: record a movement. $type: 'received' | 'used' | 'adjustment'.
     * Rules: 'used' is stored as a NEGATIVE quantity_changed; current_quantity must never go below 0.
     * Update batches.current_quantity AND insert the StockTransaction inside ONE DB::transaction().
     * After success, call AlertService::runAllChecks() (Module 5).
     */
    public function recordMovement(int $batchId, int $userId, string $type, float $quantity, ?string $remarks = null): bool
    {
        return false;
    }

    /** TODO: transaction history with filters (product, batch, type, date range). */
    public function getHistory(array $filters = []): Collection
    {
        return new Collection();
    }

    /** TODO: batches with stock for a product, earliest expiry first (for the form dropdown). */
    public function getAvailableBatches(int $productId): Collection
    {
        return new Collection();
    }
}
