<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * SHARED SYNTHETIC DATA for the whole team (all 3 transaction types: received, used, adjustment).
 * Called at the end of DatabaseSeeder, so `php artisan migrate:fresh --seed` gives everyone the same data.
 *
 * What it creates (see docs/SYNTHETIC_DATA.md):
 *  - 'received'   : a delivery for every batch (the 5 seed batches + weekly deliveries over the last 60 days)
 *  - 'used'       : daily usage in production, with a busy Friday/Saturday and a quiet Sunday
 *  - 'adjustment' : stock-count corrections (+) and write-offs of damaged/spoiled stock (-)
 *
 * The numbers add up: for every batch, the sum of its transactions equals its current_quantity.
 * Old weekly batches are fully used up (status 'depleted'). The 5 seed batches keep their quantities,
 * so the alert scenarios (expired, near expiry, low stock) are unchanged.
 * All remarks end with "[synthetic]" so the fake rows are easy to find.
 * The random numbers use a fixed seed, so everyone gets the same pattern. Dates are relative to today.
 */
class SyntheticDataSeeder extends Seeder
{
    private const MARK = ' [synthetic]';
    private const HISTORY_DAYS = 60;
    private const WINDOW_DAYS = 7;   // one delivery per week

    /** sku => average units used per day, and shelf life (days) of a delivery */
    private const PRODUCTS = [
        'DAI-001' => ['daily' => 6.0, 'shelf' => 10],   // Fresh Milk
        'DAI-002' => ['daily' => 1.2, 'shelf' => 60],   // Butter
        'DRY-001' => ['daily' => 8.0, 'shelf' => 180],  // Wheat Flour
        'DRY-002' => ['daily' => 4.0, 'shelf' => 365],  // Sugar
    ];

    /** Carbon dayOfWeek (0 = Sunday ... 6 = Saturday) => how busy the day is (1.0 = normal) */
    private const WEEKDAY = [0 => 0.6, 1 => 1.0, 2 => 1.0, 3 => 1.0, 4 => 1.0, 5 => 1.2, 6 => 1.4];

    /** who records what (matches the dev accounts) */
    private array $actors = [];

    /** Seed batches that were partly used: batch_number => day offsets (negative = days ago) when it was used. */
    private function baseBatchUsageDays(): array
    {
        return [
            'MILK-A1' => range(-9, -5),    // 40 -> 15
            'BUT-B1' => range(-19, -8),    // 20 -> 8
        ];
    }

    public function run(): void
    {
        if (StockTransaction::where('remarks', 'like', '%[synthetic]')->exists()) {
            $this->command->warn('Synthetic data is already there. Run "php artisan migrate:fresh --seed" to rebuild it.');

            return;
        }

        mt_srand(42);

        $fallback = User::first();
        $this->actors = [
            'received' => User::where('email', 'warehouse@dev.local')->first() ?? $fallback,    // Warehouse Staff
            'used' => User::where('email', 'production@dev.local')->first() ?? $fallback,       // Production Worker
            'adjustment' => User::where('email', 'manager@dev.local')->first() ?? $fallback,    // Manager
        ];

        foreach (self::PRODUCTS as $sku => $cfg) {
            $product = Product::where('sku_code', $sku)->first();
            if (! $product) {
                continue;
            }

            // 1) How much was needed on each of the last 60 days
            $demand = [];
            for ($off = -self::HISTORY_DAYS; $off <= -1; $off++) {
                $dow = now()->addDays($off)->dayOfWeek;
                $noise = 0.85 + mt_rand(0, 30) / 100;   // 0.85 to 1.15
                $demand[$off] = round($cfg['daily'] * self::WEEKDAY[$dow] * $noise, 2);
            }

            // 2) The seed batches: record their delivery, and the usage that explains why some are not full
            $baseUse = [];
            foreach (Batch::where('product_id', $product->id)->get() as $batch) {
                $this->record($batch, 'received', (float) $batch->initial_quantity, $this->offsetOf($batch->manufacture_date), '08:00', 'Delivery received');

                $days = $this->baseBatchUsageDays()[$batch->batch_number] ?? [];
                $consumed = round((float) $batch->initial_quantity - (float) $batch->current_quantity, 2);
                if (! $days || $consumed <= 0) {
                    continue;
                }

                $per = round($consumed / count($days), 2);
                $sum = 0.0;
                foreach ($days as $i => $off) {
                    $qty = ($i === count($days) - 1) ? round($consumed - $sum, 2) : $per;   // last day takes the rounding rest
                    $sum += $qty;
                    $this->record($batch, 'used', -$qty, $off, '10:00', 'Used in production');
                    $baseUse[$off] = ($baseUse[$off] ?? 0.0) + $qty;
                }
            }

            // 3) Older weekly deliveries that were completely used up
            $k = 0;
            for ($start = -self::HISTORY_DAYS; $start <= -1; $start += self::WINDOW_DAYS) {
                $end = min($start + self::WINDOW_DAYS - 1, -1);

                $uses = [];
                for ($off = $start; $off <= $end; $off++) {
                    $q = round(max(0.0, $demand[$off] - ($baseUse[$off] ?? 0.0)), 2);
                    if ($q > 0) {
                        $uses[$off] = $q;
                    }
                }
                $usedTotal = round(array_sum($uses), 2);
                if ($usedTotal <= 0) {
                    $k++;
                    continue;
                }

                // some weeks have a write-off (damage/spoilage), some have a recount that found extra stock
                $writeOff = ($k % 3 === 0) ? round($usedTotal * 0.04, 2) : 0.0;
                $gain = ($k % 4 === 1) ? round($usedTotal * 0.02, 2) : 0.0;
                $received = round($usedTotal + $writeOff - $gain, 2);   // so that the batch ends at exactly 0

                $batch = Batch::create([
                    'product_id' => $product->id,
                    'batch_number' => "{$sku}-H".($k + 1),
                    'initial_quantity' => $received,
                    'current_quantity' => 0,
                    'manufacture_date' => now()->addDays($start - 1)->toDateString(),
                    'expiry_date' => now()->addDays($start + $cfg['shelf'])->toDateString(),
                    'status' => 'depleted',
                ]);

                $this->record($batch, 'received', $received, $start, '08:00', 'Delivery received');
                foreach ($uses as $off => $q) {
                    $this->record($batch, 'used', -$q, $off, '10:00', 'Used in production');
                }
                if ($gain > 0) {
                    $this->record($batch, 'adjustment', $gain, min($start + 3, $end), '16:00', 'Stock count correction (extra found)');
                }
                if ($writeOff > 0) {
                    $this->record($batch, 'adjustment', -$writeOff, $end, '17:00', 'Write-off (damaged or spoiled in storage)');
                }

                $k++;
            }
        }

        $this->command->info(sprintf(
            'Synthetic data added: %d received, %d used, %d adjustment transactions.',
            StockTransaction::where('transaction_type', 'received')->count(),
            StockTransaction::where('transaction_type', 'used')->count(),
            StockTransaction::where('transaction_type', 'adjustment')->count(),
        ));
    }

    private function record(Batch $batch, string $type, float $qty, int $dayOffset, string $time, string $remarks): void
    {
        StockTransaction::create([
            'batch_id' => $batch->id,
            'user_id' => $this->actors[$type]->id,
            'transaction_type' => $type,
            'quantity_changed' => $qty,
            'remarks' => $remarks.self::MARK,
            'transaction_date' => now()->addDays($dayOffset)->setTimeFromTimeString($time),
        ]);
    }

    /** How many days ago a date was (negative number), e.g. -10. */
    private function offsetOf($date): int
    {
        return $date === null ? -1 : (int) round(today()->diffInDays($date, false));
    }
}
