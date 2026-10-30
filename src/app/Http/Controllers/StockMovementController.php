<?php

namespace App\Http\Controllers;

use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MODULE 4: Stock Movement and Quantity Tracking
 * Owner: TODO (name)
 */
class StockMovementController extends Controller
{
    public function __construct(private StockMovementService $movements)
    {
    }

    public function index(Request $request): View
    {
        return view('stock_movement.index', ['transactions' => $this->movements->getHistory($request->all())]);
    }

    public function create(): View
    {
        return view('stock_movement.create');
    }

    /** TODO: validate, then $this->movements->recordMovement(...). */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }
}
