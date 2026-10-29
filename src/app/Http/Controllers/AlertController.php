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
        return view('alerts.index', ['alerts' => $this->alerts->getOpenAlerts()]);
    }

    /** "Run check" button. */
    public function run(): RedirectResponse
    {
        $result = $this->alerts->runAllChecks();

        return back()->with('success', sprintf(
            'Check finished. New alerts: %d expired, %d near expiry, %d low stock.',
            $result['expired'], $result['near_expiry'], $result['low_stock']
        ));
    }

    /** "Mark resolved" button. */
    public function resolve(Alert $alert): RedirectResponse
    {
        $this->alerts->resolveAlert($alert);

        return back()->with('success', 'Alert resolved.');
    }
}
