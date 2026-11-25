<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
/**
 * MODULE 3: Stock Entry and Batch Management
 * Owner: TODO (name)
 */
class StockEntryService
{
    /** Create a batch and its 'received' transaction together. */
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

    /** Filtered, sorted, paginated batch list for the Stock Entry page. */
public function searchBatches(array $filters, int $perPage = 15): LengthAwarePaginator
{
    $query = Batch::with('product');

    $status = ($filters['status'] ?? '') ?: 'current';
    if ($status === 'current') {
        $query->whereNotIn('status', ['depleted', 'deactivated']);
    } elseif ($status !== 'all') {
        $query->where('status', $status);
    }

    if (!empty($filters['product_id'])) {
        $query->where('product_id', $filters['product_id']);
    }

    if (!empty($filters['q'])) {
        $query->where('batch_number', 'like', '%' . $filters['q'] . '%');
    }

    switch (($filters['sort'] ?? '') ?: 'expiry') {
        case 'newest':
            $query->orderByDesc('id');
            break;
        case 'qty':
            $query->orderByDesc('current_quantity');
            break;
        case 'batch':
            $query->orderBy('batch_number');
            break;
        default:   // earliest expiry first; batches with no expiry go last
            $query->orderByRaw('expiry_date IS NULL')->orderBy('expiry_date')->orderBy('id');
    }

    return $query->paginate($perPage)->withQueryString();
}

    /** Validate one CSV row (already keyed by header names). Returns a list of error strings. */
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

    /** Read the CSV, validate every row, import the valid ones. */
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

    /** Latest batches with their product, newest first. */
    public function getRecentBatches(int $limit = 50): Collection
    {
        return Batch::with('product')
            ->latest()
            ->take($limit)
            ->get();
    }

    /** Update editable fields; a quantity change is recorded as an 'adjustment' transaction. */
    public function updateBatch(Batch $batch, array $data, int $userId): Batch
    {
        return DB::transaction(function () use ($batch, $data, $userId) {
            $newQty = (float) $data['current_quantity'];
            $delta  = $newQty - (float) $batch->current_quantity;

            $batch->batch_number     = $data['batch_number'];
            $batch->manufacture_date = $data['manufacture_date'] ?? null;
            $batch->expiry_date      = $data['expiry_date'] ?? null;

            if ($delta != 0) {
                $batch->current_quantity = $newQty;

                if ($newQty <= 0) {
                    $batch->status = 'depleted';
                } elseif ($batch->status === 'depleted') {
                    $batch->status = 'active';
                }

                $batch->stockTransactions()->create([
                    'user_id'          => $userId,
                    'transaction_type' => 'adjustment',
                    'quantity_changed' => $delta,
                    'remarks'          => $data['remarks'] ?: 'Manual quantity adjustment',
                ]);
            }

            $batch->save();

            return $batch;
        });
    }

    /** Deactivate a batch (soft). Returns an error message, or null on success. */
    public function deactivateBatch(Batch $batch): ?string
    {
        if ($batch->status === 'deactivated') {
            return 'This batch is already deactivated.';
        }

        $batch->status = 'deactivated';
        $batch->save();

        return null;
    }

    /** Reactivate a batch with a status that matches its stock and expiry date. */
    public function reactivateBatch(Batch $batch): ?string
    {
        if ($batch->status !== 'deactivated') {
            return 'This batch is not deactivated.';
        }

        if ((float) $batch->current_quantity <= 0) {
            $batch->status = 'depleted';
        } elseif ($batch->expiry_date && $batch->expiry_date->lt(today())) {
            $batch->status = 'expired';
        } else {
            $batch->status = 'active';
        }
        $batch->save();

        return null;
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}