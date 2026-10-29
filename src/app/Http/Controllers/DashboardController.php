<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

/**
 * MODULE 6: Inventory Dashboard and Reporting
 * Owner: TODO (name)
 */
class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard)
    {
    }

    /** TODO: pass summary counts, low-stock items, expiring batches and recent movements to the view. */
    public function index(): View
    {
        return view('dashboard.index', ['summary' => $this->dashboard->getSummaryCounts()]);
    }

    public function reports(): View
    {
        return view('dashboard.reports', ['rows' => $this->dashboard->getInventoryReportData()]);
    }
}
