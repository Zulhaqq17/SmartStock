<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * SYNTHETIC usage history so the AI forecast has data to learn from (60 days of 'used' transactions).
 * Say clearly in the report that this data is generated, not real.
 *
 * Run AFTER the main seed:  docker compose exec web php artisan db:seed --class=UsageSeeder
 * (or add `$this->call(UsageSeeder::class);` at the end of DatabaseSeeder)
 * It does not change batch quantities: it only adds history.
 */
class UsageSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);   // same numbers every time, so results are reproducible

        $user = User::first();

        // average units used per day, by product SKU
        $base = ['DAI-001' => 6.0, 'DAI-002' => 1.2, 'DRY-001' => 8.0, 'DRY-002' => 4.0];
        // bakeries are busier on Saturday, quieter on Sunday (Carbon: 0 = Sunday ... 6 = Saturday)
        $weekday = [0 => 0.6, 1 => 1.0, 2 => 1.0, 3 => 1.0, 4 => 1.0, 5 => 1.2, 6 => 1.4];

        foreach (Product::all() as $product) {
            $batch = Batch::where('product_id', $product->id)->orderBy('id')->first();

            if (! $batch || ! isset($base[$product->sku_code])) {
                continue;
            }

            for ($daysAgo = 60; $daysAgo >= 1; $daysAgo--) {
                $day = now()->subDays($daysAgo)->setTime(10, 0);
                $noise = 0.85 + mt_rand(0, 30) / 100;   // 0.85 to 1.15
                $qty = round($base[$product->sku_code] * $weekday[$day->dayOfWeek] * $noise, 2);

                StockTransaction::create([
                    'batch_id' => $batch->id,
                    'user_id' => $user->id,
                    'transaction_type' => 'used',
                    'quantity_changed' => -$qty,
                    'remarks' => 'Synthetic usage data (UsageSeeder)',
                    'transaction_date' => $day,
                ]);
            }
        }
    }
}
