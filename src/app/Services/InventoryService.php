<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * MODULE 2: Inventory and Product Management
 * Owner: TODO (name)
 *
 * Services hold the business logic. Controllers call them; views only display.
 * getAllProducts() is a WORKING EXAMPLE of the team pattern (Eloquent, no raw SQL).
 */
class InventoryService
{
    /** EXAMPLE (implemented): active products with their category and total stock across batches. */
    public function getAllProducts(): Collection
    {
        return Product::with('category')
            ->withSum('batches as total_stock', 'current_quantity')   // adds $product->total_stock
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();
    }

    /** TODO: search by name / SKU, optional category filter. Return a Collection of Product. */
    public function search(string $keyword, ?int $categoryId = null): Collection
    {
        return Product::with('category')
            ->withSum('batches as total_stock', 'current_quantity')
            ->when($keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('product_name', 'like', "%{$keyword}%")
                ->orWhere('sku_code', 'like', "%{$keyword}%")))
            ->when($categoryId, fn ($q, $c) => $q->where('category_id', $c))
            ->orderBy('product_name')
            ->get();
    }

    /** Product list with total stock, search and filters (paginated). */
    public function searchProducts(?string $search, ?int $categoryId, string $status = 'all'): LengthAwarePaginator
    {
        return Product::with('category')
            ->withSum('batches as total_stock', 'current_quantity')
            ->when($search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('product_name', 'like', "%{$s}%")
                ->orWhere('sku_code', 'like', "%{$s}%")))
            ->when($categoryId, fn ($q, $c) => $q->where('category_id', $c))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('product_name')
            ->paginate(10)
            ->withQueryString();
    }

    /** Product with category, total stock and its batches (read-only info from Module 3). */
    public function getProductDetails(Product $product): Product
    {
        return $product
            ->load(['category', 'batches' => fn ($q) => $q->orderBy('expiry_date')])
            ->loadSum('batches as total_stock', 'current_quantity');
    }

    /** TODO: used by the create/edit form dropdown. */
    public function getCategories(): Collection
    {
        return Category::orderBy('category_name')->get();
    }

    /** TODO: create a product from validated data. Return the new Product. */
    public function create(array $data): ?Product
    {
        return Product::create($data);
    }

    /** TODO: update a product from validated data. */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function deactivate(Product $product): bool
    {
        return $product->update(['is_active' => false]);
    }

    /**
     * Deletes the product if nothing references it; otherwise deactivates it.
     * Returns 'deleted' or 'deactivated'.
     */
    public function removeOrDeactivate(Product $product): string
    {
        $inUse = $product->batches()->exists()
            || $product->alerts()->exists()
            || $product->forecasts()->exists();

        if ($inUse) {
            $this->deactivate($product);

            return 'deactivated';
        }

        $product->delete();

        return 'deleted';
    }
}
