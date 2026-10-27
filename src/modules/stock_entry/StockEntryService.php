<?php
/**
 * MODULE 3: Stock Entry and Batch Management
 * Owner: TODO (name)
 */

/**
 * TODO: insert a batch (current_quantity = initial_quantity) AND a 'received'
 * row in stock_transactions. Do both inside one DB transaction
 * (beginTransaction / commit / rollBack). Return new batch_id or null.
 */
function addBatch(array $data, int $userId): ?int
{
    return null;
}

/** TODO: validate one CSV row (product exists via SKU, quantity > 0, valid dates). Return list of error strings. */
function validateCsvRow(array $row): array
{
    return [];
}

/**
 * TODO: read uploaded CSV, validate every row first, import only if all valid
 * (or report per-row errors). Return ['imported' => n, 'errors' => [...]].
 */
function importFromCsv(string $filePath, int $userId): array
{
    return ['imported' => 0, 'errors' => []];
}

/** TODO: list batches with product name, newest first */
function getRecentBatches(int $limit = 50): array
{
    return [];
}
