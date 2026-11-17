<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * MODULE 2: Inventory and Product Management
 */
class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['active', 'inactive'], true)
            ? $request->query('status')
            : 'all';

        return view('inventory.index', [
            'products' => $this->inventory->searchProducts(
                $request->string('search')->trim()->toString() ?: null,
                $request->integer('category_id') ?: null,
                $status,
            ),
            'categories' => $this->inventory->getCategories(),
            'status' => $status,
        ]);
    }

    public function show(Product $product): View
    {
        return view('inventory.show', ['product' => $this->inventory->getProductDetails($product)]);
    }

    public function create(): View
    {
        return view('inventory.create', ['categories' => $this->inventory->getCategories()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = true;

        $this->inventory->create($data);

        return redirect()->route('inventory.index')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('inventory.edit', [
            'product' => $product,
            'categories' => $this->inventory->getCategories(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules($product));
        $data['is_active'] = $request->boolean('is_active');

        $this->inventory->update($product, $data);

        return redirect()->route('inventory.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $result = $this->inventory->removeOrDeactivate($product);

        return redirect()->route('inventory.index')->with(
            'success',
            $result === 'deleted'
                ? 'Product deleted.'
                : 'This product has batches or alert history, it was changed to inactive.'
        );
    }

    private function rules(?Product $product = null): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:150'],
            'sku_code' => ['required', 'string', 'max:50', Rule::unique('products', 'sku_code')->ignore($product)],
            'unit_of_measure' => ['required', 'string', 'max:30'],
            'min_stock_level' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
