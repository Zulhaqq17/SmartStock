<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Batch;
use App\Models\DemandForecast;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MODULE 5: FEFO, Expiry and Low-Stock Alert Management
 * Owner: Fikri
 *
 * Rule-based part (follows the flowchart): FEFO order, expired / near-expiry, low stock.
 * AI part: asks the Python forecast service (forecast/) for predicted demand, spoilage and reorder quantity.
 * The AI part is optional: if the service is down, the rule-based alerts still work.
 */
class AlertService
{
    /** Decision: stock inside expired batches cannot be used, so it does not count toward "low stock". */
    private const EXCLUDE_EXPIRED_FROM_STOCK = true;

    // ------------------------------------------------------------------ flowchart: rule-based part

    /** Flowchart: "Load active batches, sorted by earliest expiry" (batches without an expiry date go last). */
    public function getActiveBatchesFefo(): Collection
    {
        return Batch::active()
            ->with('product.category')
            ->orderByRaw('expiry_date IS NULL')
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Flowchart: "Expired?" then "Near expiry?".
     * Expired = expiry date is before today. A batch that expires today is still usable, so it is "near_expiry".
     * The near-expiry window is the category's expiry_alert_days.
     */
    public function classifyBatch(Batch $batch): string
    {
        if ($batch->expiry_date === null) {
            return 'ok';   // non-perishable
        }

        $today = today();

        if ($batch->expiry_date->lt($today)) {
            return 'expired';
        }

        $limit = $today->copy()->addDays($batch->product->category->expiry_alert_days);

        return $batch->expiry_date->lte($limit) ? 'near_expiry' : 'ok';
    }

    public function updateBatchStatus(Batch $batch, string $status): void
    {
        if ($batch->status !== $status) {
            $batch->update(['status' => $status]);
        }
    }

    /**
     * Flowchart: "Raise alert". Creates the alert only if no unresolved alert of the same type,
     * product and batch exists. With $refreshMessage the existing alert's text is updated instead
     * (used for AI alerts, whose numbers change from run to run). Returns true if a NEW alert was created.
     */
    public function createAlertIfNew(string $type, int $productId, ?int $batchId, string $message, bool $refreshMessage = false): bool
    {
        $lookup = [
            'alert_type' => $type,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'is_resolved' => false,
        ];

        $alert = $refreshMessage
            ? Alert::updateOrCreate($lookup, ['message' => $message])
            : Alert::firstOrCreate($lookup, ['message' => $message]);

        return $alert->wasRecentlyCreated;
    }

    /** Flowchart: "Check stock level: total quantity vs minimum". Returns how many new alerts were created. */
    public function checkLowStock(): int
    {
        $totals = Batch::query()
            ->selectRaw('product_id, SUM(current_quantity) AS total')
            ->when(self::EXCLUDE_EXPIRED_FROM_STOCK, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', today()->toDateString());
                });
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $created = 0;

        foreach (Product::where('is_active', true)->get() as $product) {
            $min = (float) $product->min_stock_level;
            if ($min <= 0) {
                continue;   // no minimum set for this product
            }

            $total = (float) ($totals[$product->id] ?? 0);

            if ($total <= $min) {
                $message = "{$product->product_name} is low: {$total} {$product->unit_of_measure} usable stock "
                    ."(minimum {$min}).";
                if ($this->createAlertIfNew(Alert::LOW_STOCK, $product->id, null, $message)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * The whole flowchart loop, then the AI forecast.
     * $withForecast = false skips the AI call (fast). Use that after every stock movement (Module 4);
     * use true for the "Run check" button and the daily scheduled run.
     */
    public function runAllChecks(bool $withForecast = true): array
    {
        $created = [
            'expired' => 0, 'near_expiry' => 0, 'low_stock' => 0,
            'predicted_spoilage' => 0, 'reorder_suggested' => 0,
        ];

        // batches that ran out
        Batch::where('current_quantity', '<=', 0)->where('status', '!=', 'depleted')->update(['status' => 'depleted']);

        foreach ($this->getActiveBatchesFefo() as $batch) {
            $result = $this->classifyBatch($batch);
            $this->updateBatchStatus($batch, $result === 'ok' ? 'active' : $result);

            $name = $batch->product->product_name;
            $unit = $batch->product->unit_of_measure;

            if ($result === 'expired') {
                $msg = "Batch {$batch->batch_number} of {$name} expired on {$batch->expiry_date->toDateString()} "
                    ."({$batch->current_quantity} {$unit} left). Remove it from use.";
                if ($this->createAlertIfNew(Alert::EXPIRED, $batch->product_id, $batch->id, $msg)) {
                    $created['expired']++;
                }
            } elseif ($result === 'near_expiry') {
                $msg = "Batch {$batch->batch_number} of {$name} expires on {$batch->expiry_date->toDateString()} "
                    ."({$batch->current_quantity} {$unit} left). Use it first.";
                if ($this->createAlertIfNew(Alert::NEAR_EXPIRY, $batch->product_id, $batch->id, $msg)) {
                    $created['near_expiry']++;
                }
            }
        }

        $created['low_stock'] = $this->checkLowStock();

        if ($withForecast) {
            $created = array_merge($created, $this->runForecastChecks());
        }

        return $created;
    }

    /** Unresolved alerts, newest first. */
    public function getOpenAlerts(): Collection
    {
        return Alert::with(['product', 'batch'])
            ->where('is_resolved', false)
            ->latest()
            ->get();
    }

    public function resolveAlert(Alert $alert): bool
    {
        return $alert->update(['is_resolved' => true, 'resolved_at' => now()]);
    }

    // ------------------------------------------------------------------ AI part (forecast service)

    /** Ask the Python service for a forecast. Returns null if the service cannot be reached. */
    public function fetchForecast(int $productId, int $horizon = 7): ?array
    {
        try {
            $url = rtrim((string) env('FORECAST_URL', 'http://forecast:8000'), '/')."/forecast/{$productId}";

            $response = Http::connectTimeout(2)->timeout(8)->acceptJson()->get($url, ['horizon' => $horizon]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('Forecast service unavailable: '.$e->getMessage());

            return null;
        }
    }

    /** One forecast row per product per day (running the check again the same day updates it). */
    public function saveForecast(int $productId, array $forecast): DemandForecast
    {
        return DemandForecast::updateOrCreate(
            ['product_id' => $productId, 'forecast_date' => today()->toDateString()],
            [
                'predicted_demand_qty' => $forecast['predicted_demand_qty'],
                'predicted_spoilage_qty' => $forecast['predicted_spoilage_qty'],
                'recommended_reorder_qty' => $forecast['recommended_reorder_qty'],
                'confidence_score' => $forecast['confidence_score'],
                'generated_at' => now(),
            ]
        );
    }

    /** For each product: get the forecast, save it, and raise AI alerts. Returns counts of NEW alerts. */
    public function runForecastChecks(int $horizon = 7): array
    {
        $counts = ['predicted_spoilage' => 0, 'reorder_suggested' => 0];

        foreach (Product::where('is_active', true)->get() as $product) {
            $forecast = $this->fetchForecast($product->id, $horizon);

            if ($forecast === null) {
                break;   // service is down: stop here so the check does not wait on every product
            }
            if (($forecast['method'] ?? '') === 'insufficient_data') {
                continue;   // not enough usage history for this product yet
            }

            $this->saveForecast($product->id, $forecast);

            $unit = $product->unit_of_measure;
            $confidence = $forecast['confidence_score'] !== null ? $forecast['confidence_score'].'%' : 'n/a';

            if ($forecast['predicted_spoilage_qty'] > 0) {
                $first = $forecast['spoilage_batches'][0] ?? null;
                $where = $first ? " Most at risk: batch {$first['batch_number']} (expires {$first['expiry']})." : '';
                $msg = "AI forecast: about {$forecast['predicted_spoilage_qty']} {$unit} of {$product->product_name} "
                    ."may spoil within {$horizon} days.{$where} Use the earliest-expiring batch first.";
                if ($this->createAlertIfNew(Alert::PREDICTED_SPOILAGE, $product->id, null, $msg, true)) {
                    $counts['predicted_spoilage']++;
                }
            }

            if ($forecast['recommended_reorder_qty'] > 0) {
                $msg = "AI forecast: expected demand is {$forecast['predicted_demand_qty']} {$unit} over the next "
                    ."{$horizon} days. Suggested reorder: {$forecast['recommended_reorder_qty']} {$unit} "
                    ."(confidence {$confidence}).";
                if ($this->createAlertIfNew(Alert::REORDER_SUGGESTED, $product->id, null, $msg, true)) {
                    $counts['reorder_suggested']++;
                }
            }
        }

        return $counts;
    }

    /** Latest saved forecast of each product (for the alerts page). */
    public function getLatestForecasts(): Collection
    {
        return DemandForecast::with('product')
            ->whereIn('id', DemandForecast::selectRaw('MAX(id)')->groupBy('product_id'))
            ->get();
    }
}
