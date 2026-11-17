<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample data for development. Run: php artisan migrate:fresh --seed
 * Dates are relative to today so the FEFO tests always work.
 * Module owners: add your own test data in a separate seeder and call it from here.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- roles ----
        $roles = [];
        foreach ([Role::MANAGER, Role::WAREHOUSE, Role::PRODUCTION] as $name) {
            $roles[$name] = Role::create(['role_name' => $name]);
        }

        // ---- dev users (DEV ONLY: password is "password") ----
        $devUsers = [
            ['Dev Manager', 'manager@dev.local', Role::MANAGER],
            ['Dev Warehouse', 'warehouse@dev.local', Role::WAREHOUSE],
            ['Dev Production', 'production@dev.local', Role::PRODUCTION],
        ];
        foreach ($devUsers as [$name, $email, $role]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => 'password',   // hashed automatically by the User model
                'role_id' => $roles[$role]->id,
            ]);
        }

        // ---- categories (expiry_alert_days = near-expiry threshold) ----
        $dairy = Category::create(['category_name' => 'Dairy', 'description' => 'Milk, butter, cream', 'expiry_alert_days' => 5]);
        $dry = Category::create(['category_name' => 'Dry Goods', 'description' => 'Flour, sugar, yeast', 'expiry_alert_days' => 30]);
        Category::create(['category_name' => 'Packaging', 'description' => 'Boxes and wrappers', 'expiry_alert_days' => 60]);

        // ---- products ----
        $milk = Product::create(['category_id' => $dairy->id, 'product_name' => 'Fresh Milk', 'sku_code' => 'DAI-001', 'unit_of_measure' => 'litre', 'min_stock_level' => 20]);
        $butter = Product::create(['category_id' => $dairy->id, 'product_name' => 'Butter', 'sku_code' => 'DAI-002', 'unit_of_measure' => 'kg', 'min_stock_level' => 10]);
        $flour = Product::create(['category_id' => $dry->id, 'product_name' => 'Wheat Flour', 'sku_code' => 'DRY-001', 'unit_of_measure' => 'kg', 'min_stock_level' => 50]);
        $sugar = Product::create(['category_id' => $dry->id, 'product_name' => 'Sugar', 'sku_code' => 'DRY-002', 'unit_of_measure' => 'kg', 'min_stock_level' => 30]);

        // ---- batches ----
        $day = fn (int $offset) => now()->addDays($offset)->toDateString();

        Batch::create(['product_id' => $milk->id, 'batch_number' => 'MILK-A1', 'initial_quantity' => 40, 'current_quantity' => 15, 'manufacture_date' => $day(-10), 'expiry_date' => $day(-3)]);  // expired
        Batch::create(['product_id' => $milk->id, 'batch_number' => 'MILK-A2', 'initial_quantity' => 40, 'current_quantity' => 40, 'manufacture_date' => $day(-2), 'expiry_date' => $day(3)]);    // near expiry (5-day rule)
        Batch::create(['product_id' => $butter->id, 'batch_number' => 'BUT-B1', 'initial_quantity' => 20, 'current_quantity' => 8, 'manufacture_date' => $day(-20), 'expiry_date' => $day(40)]);  // low stock (8 < 10)
        Batch::create(['product_id' => $flour->id, 'batch_number' => 'FLR-C1', 'initial_quantity' => 100, 'current_quantity' => 100, 'manufacture_date' => $day(-30), 'expiry_date' => $day(120)]); // healthy
        Batch::create(['product_id' => $sugar->id, 'batch_number' => 'SUG-D1', 'initial_quantity' => 60, 'current_quantity' => 60, 'manufacture_date' => $day(-30), 'expiry_date' => $day(20)]);   // near expiry (30-day rule)
    }
}
