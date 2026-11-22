<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Role;
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
    /** Used when a non-Manager creates a product (they cannot set the minimum level). */
    private const DEFAULT_MIN_STOCK = 1;

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
        $this->normalizeInput($request);
        $data = $request->validate($this->rules($request), $this->messages());
        $data['is_active'] = true;
        $data['min_stock_level'] ??= self::DEFAULT_MIN_STOCK; // absent for non-Managers

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
        $this->normalizeInput($request);
        $data = $request->validate($this->rules($request, $product), $this->messages());
        $data['is_active'] = $request->boolean('is_active');
        // for non-Managers min_stock_level is not in $data, so the stored value stays unchanged

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
                : 'This product has batches or alert history, so it was deactivated instead of deleted.'
        );
    }

    /** Trim and uppercase the SKU before validation so the database stays consistent. */
    private function normalizeInput(Request $request): void
    {
        if ($request->has('sku_code')) {
            $request->merge(['sku_code' => mb_strtoupper(trim((string) $request->input('sku_code')))]);
        }
    }

    private function rules(Request $request, ?Product $product = null): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:150'],
            'sku_code' => ['required', 'string', 'max:50', Rule::unique('products', 'sku_code')->ignore($product)],
            'unit_of_measure' => ['required', 'string', 'max:30'],
        ];

        // Only the Manager may set the minimum stock level
        if ($request->user()->hasRole(Role::MANAGER)) {
            $rules['min_stock_level'] = ['required', 'numeric', 'min:1', 'max:99999999.99'];
        }

        return $rules;
    }

    private function messages(): array
    {
        return ['min_stock_level.min' => 'The minimum stock level must be at least 1.'];
    }
}
