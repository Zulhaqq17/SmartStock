<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * MODULE 3: Stock Entry and Batch Management
 * Owner: TODO (name)
 */
class StockEntryService
{
    /**
     * TODO: create a batch (current_quantity = initial_quantity) AND a 'received' StockTransaction.
     * Do both inside DB::transaction(function () { ... }) so they succeed or fail together.
     */
    public function addBatch(array $data, int $userId): ?Batch
{
    return DB::transaction(function () use ($data, $userId) {
        $batch = Batch::create([
            'product_id'       => $data['product_id'],
            'batch_number'     => $data['batch_number'],
            'initial_quantity' => $data['initial_quantity'],
            'current_quantity' => $data['initial_quantity'],
            'manufacture_date' => $data['manufacture_date'] ?? null,
            'expiry_date'      => $data['expiry_date'] ?? null,
            'status'           => 'active',
        ]);

        $batch->stockTransactions()->create([
            'user_id'          => $userId,
            'transaction_type' => 'received',
            'quantity_changed' => $data['initial_quantity'],
            'remarks'          => 'Initial stock received',
        ]);

        return $batch;
    });
}

    /** TODO: validate one CSV row (SKU exists, quantity > 0, valid dates). Return a list of error strings. */
    public function validateCsvRow(array $row): array
    {
        return [];
    }

    /**
     * TODO: read the uploaded CSV, validate every row, import valid rows.
     * Return ['imported' => int, 'errors' => [...]].
     */
    public function importFromCsv(string $filePath, int $userId): array
    {
        return ['imported' => 0, 'errors' => []];
    }

    /** TODO: latest batches with their product, newest first. */
    public function getRecentBatches(int $limit = 50): Collection
{
    return Batch::with('product')
        ->latest()
        ->take($limit)
        ->get();
}
}
