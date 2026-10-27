<?php
/**
 * MODULE 5: FEFO, Expiry and Low-Stock Alert Management
 * Owner: Fikri
 * Follow the Module 5 flowchart (docs/flowcharts/). Step numbers below match it.
 */

/** Flowchart: "Load active batches, sorted by earliest expiry".
 *  TODO: batches WHERE current_quantity > 0 ORDER BY expiry_date ASC,
 *  joined with products + categories (need expiry_alert_days). */
function getActiveBatchesFEFO(): array
{
    return [];
}

/** Flowchart: "Expired?" then "Near expiry?".
 *  TODO: return 'expired' | 'near_expiry' | 'ok'. Handle expiry_date NULL (non-perishable) as 'ok'. */
function classifyBatch(array $batch): string
{
    return 'ok';
}

/** TODO: update batches.status to match classification (active/near_expiry/expired/depleted). */
function updateBatchStatus(int $batchId, string $status): void
{
}

/** Flowchart: "Raise alert".
 *  TODO: INSERT into alerts ONLY if no unresolved alert with same type+product+batch exists. */
function createAlertIfNew(string $type, int $productId, ?int $batchId, string $message): bool
{
    return false;
}

/** Flowchart: "Check stock level: total quantity vs minimum".
 *  TODO: per product, SUM(current_quantity) <= min_stock_level => low_stock alert. */
function checkLowStock(): int
{
    return 0;
}

/** Flowchart: whole loop. TODO: for each FEFO batch classify, update status, raise alert; then checkLowStock().
 *  Return counts, e.g. ['expired' => 1, 'near_expiry' => 2, 'low_stock' => 1].
 *  Called from: dashboard load, after each stock movement (Module 4), and the "Run check" button. */
function runAllChecks(): array
{
    return ['expired' => 0, 'near_expiry' => 0, 'low_stock' => 0];
}

/** TODO: unresolved alerts, newest first, with product/batch names. */
function getOpenAlerts(): array
{
    return [];
}

/** TODO: set is_resolved = 1, resolved_at = NOW(). */
function resolveAlert(int $alertId): bool
{
    return false;
}
