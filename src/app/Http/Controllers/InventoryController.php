<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MODULE 2: Inventory and Product Management
 * Owner: TODO (name)
 * index() is a WORKING EXAMPLE of: route -> controller -> service -> view.
 */
class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(): View
    {
        return view('inventory.index', ['products' => $this->inventory->getAllProducts()]);
    }

    public function create(): View
    {
        return view('inventory.create', ['categories' => $this->inventory->getCategories()]);
    }

    /** TODO: $request->validate([...]); $this->inventory->create($validated); redirect with success message. */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }

    public function edit(Product $product): View
    {
        return view('inventory.edit', [
            'product' => $product,
            'categories' => $this->inventory->getCategories(),
        ]);
    }

    /** TODO: validate and call $this->inventory->update($product, $validated). */
    public function update(Request $request, Product $product): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }

    /** TODO: $this->inventory->deactivate($product). */
    public function destroy(Product $product): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }
}
