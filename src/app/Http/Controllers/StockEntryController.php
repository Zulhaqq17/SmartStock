<?php

namespace App\Http\Controllers;

use App\Services\StockEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MODULE 3: Stock Entry and Batch Management
 * Owner: TODO (name)
 */
class StockEntryController extends Controller
{
    public function __construct(private StockEntryService $stockEntry)
    {
    }

    public function index(): View
    {
        return view('stock_entry.index', ['batches' => $this->stockEntry->getRecentBatches()]);
    }

    public function create(): View
    {
        return view('stock_entry.create');
    }

    /** TODO: validate, then $this->stockEntry->addBatch($validated, $request->user()->id). */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }

    public function importForm(): View
    {
        return view('stock_entry.import');
    }

    /** TODO: validate the uploaded CSV, then $this->stockEntry->importFromCsv(...). */
    public function import(Request $request): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }
}
