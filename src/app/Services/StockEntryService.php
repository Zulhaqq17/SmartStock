<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Collection;

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
        return null;
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
        return new Collection();
    }
}
