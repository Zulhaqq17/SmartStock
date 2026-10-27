<?php
/**
 * MODULE 2: Inventory and Product Management
 * Owner: TODO (name)
 *
 * getAllProducts() is a WORKING EXAMPLE of the team pattern:
 * PDO via db(), prepared statements, return plain arrays. Copy this style.
 */

/** EXAMPLE (implemented): all active products with category name and total stock. */
function getAllProducts(): array
{
    $sql = 'SELECT p.product_id, p.product_name, p.sku_code, c.category_name,
                   p.unit_of_measure, p.min_stock_level,
                   COALESCE(SUM(b.current_quantity), 0) AS total_stock
            FROM products p
            JOIN categories c ON c.category_id = p.category_id
            LEFT JOIN batches b ON b.product_id = p.product_id
            WHERE p.is_active = 1
            GROUP BY p.product_id
            ORDER BY p.product_name';
    return db()->query($sql)->fetchAll();
}

/** TODO */ function getProductById(int $id): ?array { return null; }
/** TODO: search by name / SKU / category */ function searchProducts(string $keyword, ?int $categoryId = null): array { return []; }
/** TODO: validate, check SKU is unique, INSERT. Return new product_id or null. */
function createProduct(array $data): ?int { return null; }
/** TODO */ function updateProduct(int $id, array $data): bool { return false; }
/** TODO: set is_active = 0 (do NOT delete rows, history depends on them) */
function deactivateProduct(int $id): bool { return false; }
/** TODO: used by forms (dropdown) */ function getCategories(): array { return []; }
