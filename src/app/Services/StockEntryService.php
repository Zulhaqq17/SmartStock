<?php

namespace App\Services;

use App\Models\Product;
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
    $errors = [];

    $sku      = trim($row['sku_code'] ?? '');
    $batchNo  = trim($row['batch_number'] ?? '');
    $quantity = trim($row['quantity'] ?? '');
    $mfg      = trim($row['manufacture_date'] ?? '');
    $exp      = trim($row['expiry_date'] ?? '');

    $product = null;
    if ($sku === '') {
        $errors[] = 'sku_code is required';
    } else {
        $product = Product::where('sku_code', $sku)->first();
        if (!$product) {
            $errors[] = "SKU '{$sku}' does not exist";
        }
    }

    if ($batchNo === '') {
        $errors[] = 'batch_number is required';
    } elseif (strlen($batchNo) > 100) {
        $errors[] = 'batch_number is longer than 100 characters';
    } elseif ($product && Batch::where('product_id', $product->id)->where('batch_number', $batchNo)->exists()) {
        $errors[] = "batch '{$batchNo}' already exists for this product";
    }

    if (!is_numeric($quantity) || (float) $quantity <= 0) {
        $errors[] = 'quantity must be a number greater than 0';
    }

    $mfgOk = $mfg === '' || $this->isValidDate($mfg);
    $expOk = $exp === '' || $this->isValidDate($exp);
    if (!$mfgOk) {
        $errors[] = 'manufacture_date must be a valid date (YYYY-MM-DD)';
    }
    if (!$expOk) {
        $errors[] = 'expiry_date must be a valid date (YYYY-MM-DD)';
    }
    if ($mfg !== '' && $exp !== '' && $mfgOk && $expOk && $exp < $mfg) {
        $errors[] = 'expiry_date cannot be before manufacture_date';
    }

    return $errors;
}

    /**
     * TODO: read the uploaded CSV, validate every row, import valid rows.
     * Return ['imported' => int, 'errors' => [...]].
     */
    public function importFromCsv(string $filePath, int $userId): array
{
    $imported = 0;
    $errors = [];

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        return ['imported' => 0, 'errors' => ['Could not read the uploaded file.']];
    }

    // Header row: strip the UTF-8 BOM that Excel adds, trim and lowercase the names.
    $header = fgetcsv($handle);
    if ($header === false || $header === [null]) {
        fclose($handle);
        return ['imported' => 0, 'errors' => ['The file is empty.']];
    }
    $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);

    $required = ['sku_code', 'batch_number', 'quantity', 'manufacture_date', 'expiry_date'];
    $missing = array_diff($required, $header);
    if ($missing) {
        fclose($handle);
        return ['imported' => 0, 'errors' => ['Missing column(s): ' . implode(', ', $missing)]];
    }

    $line = 1;
    $seen = [];   // catches duplicates inside the same file

    while (($cells = fgetcsv($handle)) !== false) {
        $line++;

        if ($cells === [null]) {   // blank line
            continue;
        }
        if (count($cells) !== count($header)) {
            $errors[] = "Line {$line}: expected " . count($header) . ' columns but found ' . count($cells);
            continue;
        }

        $row = array_combine($header, $cells);
        $rowErrors = $this->validateCsvRow($row);

        $key = strtolower(trim($row['sku_code'])) . '|' . strtolower(trim($row['batch_number']));
        if (isset($seen[$key])) {
            $rowErrors[] = "duplicate of line {$seen[$key]} in this file";
        }

        if ($rowErrors) {
            foreach ($rowErrors as $message) {
                $errors[] = "Line {$line}: {$message}";
            }
            continue;
        }

        $seen[$key] = $line;
        $product = Product::where('sku_code', trim($row['sku_code']))->first();

        $this->addBatch([
            'product_id'       => $product->id,
            'batch_number'     => trim($row['batch_number']),
            'initial_quantity' => trim($row['quantity']),
            'manufacture_date' => trim($row['manufacture_date']) ?: null,
            'expiry_date'      => trim($row['expiry_date']) ?: null,
        ], $userId);

        $imported++;
    }

    fclose($handle);

    return ['imported' => $imported, 'errors' => $errors];
}
    private function isValidDate(string $value): bool
{
    $date = \DateTime::createFromFormat('Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
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
