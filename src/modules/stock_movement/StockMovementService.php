<?php
/**
 * MODULE 4: Stock Movement and Quantity Tracking
 * Owner: TODO (name)
 */

/**
 * TODO: record a movement. $type: 'received' | 'used' | 'adjustment'.
 * Rules: 'used' stores a NEGATIVE quantity_changed; never let current_quantity go below 0.
 * Update batches.current_quantity AND insert stock_transactions in ONE DB transaction.
 * Suggestion: suggest the earliest-expiring batch first (FEFO), see modules/alerts.
 * After success, call runAllChecks() from modules/alerts/AlertService.php.
 */
function recordMovement(int $batchId, int $userId, string $type, float $quantity, ?string $remarks = null): bool
{
    return false;
}

/** TODO: transaction history with filters (product, batch, type, date range) */
function getMovementHistory(array $filters = []): array
{
    return [];
}

/** TODO: batches with quantity > 0 for a product, earliest expiry first (for the form dropdown) */
function getAvailableBatches(int $productId): array
{
    return [];
}
