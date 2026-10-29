<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class RunAlertChecks extends Command
{
    // Run it with: docker compose exec web php artisan alerts:check
    protected $signature = 'alerts:check';

    protected $description = 'Run the Module 5 expiry and low-stock checks';

    public function handle(AlertService $alerts): int
    {
        $result = $alerts->runAllChecks();

        $this->info(sprintf(
            'Done. expired: %d, near expiry: %d, low stock: %d',
            $result['expired'], $result['near_expiry'], $result['low_stock']
        ));

        return self::SUCCESS;
    }
}
