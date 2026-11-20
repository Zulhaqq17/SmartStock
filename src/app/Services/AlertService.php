<?php

namespace App\Services;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Collection;

/**
 * MODULE 5: FEFO, Expiry and Low-Stock Alert Management
 * Owner: Fikri
 *
 * Follow the Module 5 flowchart (docs/flowcharts/). Each method says which flowchart box it is.
 *
 * Hint for the FEFO query (Eloquent):
 *   Batch::active()->with('product.category')->orderBy('expiry_date')->get();
 */
class AlertService
{
    /** Flowchart: "Load active batches, sorted by earliest expiry". */
    public function getActiveBatchesFefo(): Collection
    {
        // TODO
        return new Collection();
    }

    /**
     * Flowchart: "Expired?" then "Near expiry?".
     * TODO: return 'expired' | 'near_expiry' | 'ok'. A null expiry_date (non-perishable) is 'ok'.
     * The threshold is $batch->product->category->expiry_alert_days.
     */
    public function classifyBatch(\App\Models\Batch $batch): string
    {
        return 'ok';
    }

    /** TODO: update batches.status to match the classification (active / near_expiry / expired / depleted). */
    public function updateBatchStatus(\App\Models\Batch $batch, string $status): void
    {
    }

    /**
     * Flowchart: "Raise alert".
     * TODO: create the Alert ONLY if no unresolved alert of the same type + product + batch exists
     * (hint: Alert::firstOrCreate([...lookup fields...], [...extra fields...])).
     */
    public function createAlertIfNew(string $type, int $productId, ?int $batchId, string $message): bool
    {
        return false;
    }

    /**
     * Flowchart: "Check stock level: total quantity vs minimum".
     * TODO: per product, sum of current_quantity <= min_stock_level => low_stock alert. Return how many alerts were created.
     * (Decide if expired batches count toward the total, and write the decision in the report.)
     */
    public function checkLowStock(): int
    {
        return 0;
    }

    /**
     * Flowchart: the whole loop.
     * TODO: for each FEFO batch classify, update status, raise alert; then checkLowStock().
     * Return counts, e.g. ['expired' => 1, 'near_expiry' => 2, 'low_stock' => 1].
     * Called from: the "Run check" button, the `php artisan alerts:check` command,
     * the dashboard load and after each stock movement (Module 4).
     */
    public function runAllChecks(): array
    {
        return ['expired' => 0, 'near_expiry' => 0, 'low_stock' => 0];
    }

    /** TODO: unresolved alerts, newest first, with product and batch loaded. */
    public function getOpenAlerts(): Collection
    {
        return new Collection();
    }

    /** TODO: set is_resolved = true and resolved_at = now(). */
    public function resolveAlert(Alert $alert): bool
    {
        return false;
    }
}
