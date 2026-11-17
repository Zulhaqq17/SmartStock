<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class RunAlertChecks extends Command
{
    // docker compose exec web php artisan alerts:check
    // docker compose exec web php artisan alerts:check --no-ai     (rule-based checks only)
    protected $signature = 'alerts:check {--no-ai : Skip the AI forecast}';

    protected $description = 'Run the Module 5 expiry, low-stock and AI forecast checks';

    public function handle(AlertService $alerts): int
    {
        $r = $alerts->runAllChecks(! $this->option('no-ai'));

        $this->table(['Alert type', 'New'], [
            ['expired', $r['expired']],
            ['near_expiry', $r['near_expiry']],
            ['low_stock', $r['low_stock']],
            ['predicted_spoilage (AI)', $r['predicted_spoilage']],
            ['reorder_suggested (AI)', $r['reorder_suggested']],
        ]);

        return self::SUCCESS;
    }
}
