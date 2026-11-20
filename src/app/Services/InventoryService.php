<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

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
        return new Collection();
    }

    /** TODO: used by the create/edit form dropdown. */
    public function getCategories(): Collection
    {
        return Category::orderBy('category_name')->get();
    }

    /** TODO: create a product from validated data. Return the new Product. */
    public function create(array $data): ?Product
    {
        return null;
    }

    /** TODO: update a product from validated data. */
    public function update(Product $product, array $data): bool
    {
        return false;
    }

    /** TODO: set is_active = false. Do NOT delete rows: batches and history depend on them. */
    public function deactivate(Product $product): bool
    {
        return false;
    }
}
