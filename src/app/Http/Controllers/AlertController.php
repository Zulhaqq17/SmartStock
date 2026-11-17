<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * MODULE 5: FEFO, Expiry and Low-Stock Alert Management
 * Owner: Fikri
 */
class AlertController extends Controller
{
    public function __construct(private AlertService $alerts)
    {
    }

    public function index(): View
    {
        return view('alerts.index', [
            'alerts' => $this->alerts->getOpenAlerts(),
            'forecasts' => $this->alerts->getLatestForecasts(),
        ]);
    }

    /** "Run check" button: rule-based checks + AI forecast. */
    public function run(): RedirectResponse
    {
        $r = $this->alerts->runAllChecks();

        return back()->with('success', sprintf(
            'Check finished. New alerts: %d expired, %d near expiry, %d low stock, %d predicted spoilage, %d reorder suggestions.',
            $r['expired'], $r['near_expiry'], $r['low_stock'], $r['predicted_spoilage'], $r['reorder_suggested']
        ));
    }

    /** "Mark resolved" button. */
    public function resolve(Alert $alert): RedirectResponse
    {
        $this->alerts->resolveAlert($alert);

        return back()->with('success', 'Alert resolved.');
    }
}
