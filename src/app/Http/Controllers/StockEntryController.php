<?php

namespace App\Http\Controllers;

use App\Services\StockEntryService;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

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
    $products = Product::where('is_active', true)->orderBy('product_name')->get();
    return view('stock_entry.create', compact('products'));
}

    /** TODO: validate, then $this->stockEntry->addBatch($validated, $request->user()->id). */
    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'product_id'       => ['required', 'exists:products,id'],
        'batch_number'     => ['required', 'string', 'max:100',
            Rule::unique('batches')->where('product_id', $request->product_id)],
        'initial_quantity' => ['required', 'numeric', 'gt:0'],
        'manufacture_date' => ['nullable', 'date'],
        'expiry_date'      => ['nullable', 'date', 'after_or_equal:manufacture_date'],
    ]);

    $this->stockEntry->addBatch($validated, $request->user()->id);

    return redirect()->route('stock_entry.index')->with('success', 'Batch added successfully.');
}

    public function importForm(): View
    {
        return view('stock_entry.import');
    }

    /** TODO: validate the uploaded CSV, then $this->stockEntry->importFromCsv(...). */
    public function import(Request $request): RedirectResponse
{
    $request->validate([
        'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
    ]);

    $result = $this->stockEntry->importFromCsv(
        $request->file('csv_file')->getRealPath(),
        $request->user()->id
    );

    if (empty($result['errors'])) {
        return redirect()->route('stock_entry.index')
            ->with('success', "{$result['imported']} batch(es) imported successfully.");
    }

    $redirect = redirect()->route('stock_entry.import')->with('import_errors', $result['errors']);

    return $result['imported'] > 0
        ? $redirect->with('success', "{$result['imported']} batch(es) imported. Some rows were skipped, see below.")
        : $redirect->with('error', 'No batches were imported.');
}
}
