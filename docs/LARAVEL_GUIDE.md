# Laravel guide for beginners

Read this once, then keep it open while you work. All commands run from the project root (the folder with `docker-compose.yml`).

## 1. The big idea

Laravel is a PHP framework. It already solves login, sessions, security, database access and routing, so we write less code. Our code goes in a few known places.

```
Browser -> routes/web.php -> Controller -> Service -> Model -> View -> Browser
```

| Part | What it does | Where | Example in our project |
|---|---|---|---|
| Route | URL -> controller method | `src/routes/web.php` | `Route::get('/inventory', [InventoryController::class, 'index'])` |
| Controller | Receives the request, validates, calls a service, returns a view | `src/app/Http/Controllers` | `InventoryController` |
| Service | Business logic and queries | `src/app/Services` | `InventoryService::getAllProducts()` |
| Model | One class per database table | `src/app/Models` | `Product`, `Batch` |
| View | The HTML page (Blade) | `src/resources/views` | `inventory/index.blade.php` |
| Migration | Creates/changes tables | `src/database/migrations` | `..._create_batches_table.php` |
| Seeder | Sample data | `src/database/seeders` | `DatabaseSeeder.php` |

**Study the working example first:** open `InventoryController::index`, `InventoryService::getAllProducts` and `inventory/index.blade.php`, and follow the flow from the route.

## 2. Running Laravel commands (artisan)

Laravel's tool is `php artisan`. Because PHP runs inside Docker, put `docker compose exec web` in front:

```bash
docker compose exec web php artisan route:list          # list every URL in the app
docker compose exec web php artisan migrate             # apply new migrations
docker compose exec web php artisan migrate:fresh --seed  # wipe local DB, rebuild, add sample data
docker compose exec web php artisan make:migration add_x_to_y_table   # new migration
docker compose exec web php artisan make:model Something
docker compose exec web php artisan tinker              # try Eloquent code interactively
docker compose exec web php artisan optimize:clear      # clears cached config/routes/views (fixes weird stale errors)
docker compose exec web php artisan alerts:check        # Module 5 check
```

## 3. How to add a page (step by step)

Example: a Module 2 "Add product" page.

1. **Route** in your module's block in `routes/web.php` (already there for our modules):
   ```php
   Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
   Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
   ```
2. **Controller method**:
   ```php
   public function store(Request $request): RedirectResponse
   {
       $validated = $request->validate([
           'product_name' => ['required', 'string', 'max:150'],
           'sku_code' => ['required', 'max:50', 'unique:products,sku_code'],
           'category_id' => ['required', 'exists:categories,id'],
           'unit_of_measure' => ['required', 'max:30'],
           'min_stock_level' => ['required', 'numeric', 'min:0'],
       ]);

       $this->inventory->create($validated);

       return redirect()->route('inventory.index')->with('success', 'Product added.');
   }
   ```
3. **Service method** (logic):
   ```php
   public function create(array $data): Product
   {
       return Product::create($data);   // only fields listed in $fillable in the model are accepted
   }
   ```
4. **View** `resources/views/inventory/create.blade.php`:
   ```blade
   @extends('layouts.app')
   @section('title', 'Add Product')
   @section('content')
   <form method="POST" action="{{ route('inventory.store') }}">
       @csrf
       <input name="product_name" value="{{ old('product_name') }}" class="form-control mb-2">
       {{-- ...other fields... --}}
       <button class="btn btn-primary">Save</button>
   </form>
   @endsection
   ```
   Validation errors and the success message are shown automatically by the layout.

## 4. Blade cheat sheet (views)

```blade
{{ $product->product_name }}          prints a value (safely escaped)
@if ($x) ... @elseif ... @else ... @endif
@foreach ($products as $p) ... @endforeach
@forelse ($products as $p) ... @empty No data @endforelse
@extends('layouts.app')               use the shared layout
@section('content') ... @endsection   fill the layout's content area
@csrf                                 REQUIRED inside every POST form
@method('PUT')                        for update forms (HTML forms only do GET/POST)
{{ route('inventory.index') }}        URL from a route name
{{-- comment --}}
```

## 5. Eloquent cheat sheet (database)

```php
Product::all();                                    // every row
Product::find(5);                                  // by id (null if missing)
Product::where('is_active', true)->orderBy('product_name')->get();
Product::with('category')->get();                  // load the relationship too (avoids slow N+1 queries)
Batch::active()->orderBy('expiry_date')->get();    // our own scope: current_quantity > 0
Product::create([...]);                            // insert (fields must be in $fillable)
$product->update(['min_stock_level' => 20]);
$batch->product->category->expiry_alert_days;      // follow relationships
$batch->expiry_date->isPast();                     // dates are Carbon objects
now(); now()->addDays(7); now()->toDateString();   // current date/time helpers
```

Several tables changed at once (batch + transaction)? Wrap them so both succeed or neither does:

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($batch, $qty) {
    $batch->decrement('current_quantity', $qty);
    StockTransaction::create([...]);
});
```

## 6. Who can open a page

Done in `routes/web.php`, not in the view (hiding a button is not security):

- logged-in users: `Route::middleware('auth')`
- Manager or Warehouse Staff: the `$managerOrWarehouse` group
- Manager only: the `$managerOnly` group

In a view, show or hide parts by role:
```blade
@if (auth()->user()->hasRole(\App\Models\Role::MANAGER))
    ...
@endif
```
In a controller or service, the current user is `auth()->user()` (or `$request->user()`).

## 7. Changing the database

1. `docker compose exec web php artisan make:migration add_notes_to_batches_table`
2. Edit the new file in `database/migrations` (`$table->text('notes')->nullable();` in `up()`, drop it in `down()`).
3. `docker compose exec web php artisan migrate`
4. Add the column to the model's `$fillable`, update `docs/smartstock_erd.dbml`, commit, tell the group chat.

Teammates get it by pulling and running `php artisan migrate` (it also runs automatically when the container starts).

## 8. Reading errors

- **Red error page with a stack trace** (while `APP_DEBUG=true`): read the first line. It names the file and line.
- **"500 | Server Error" only**: look at `src/storage/logs/laravel.log` or run `docker compose logs web`.
- **"Class ... not found"**: missing `use App\Models\Batch;` at the top of the file, or run `docker compose exec web composer dump-autoload`.
- **"Route [x] not defined"**: the route name in `route('x')` doesn't exist. Check `php artisan route:list`.
- **"419 Page Expired"**: the form is missing `@csrf`.
- **"Add [x] to fillable property"**: add the column to `$fillable` in the model.
- **Changes not showing**: `php artisan optimize:clear`, then hard refresh the browser.
- **"Table/column not found"**: run `php artisan migrate`; if still broken, `php artisan migrate:fresh --seed`.

## 9. Glossary

- **Eloquent**: Laravel's way to talk to the database using PHP classes instead of SQL.
- **Middleware**: code that runs before a route (login check, role check).
- **Blade**: Laravel's template language for views.
- **Migration**: version-controlled change to the database structure.
- **Seeder**: script that inserts sample data.
- **Facade**: short static-looking helpers such as `Auth::`, `DB::`, `Route::`.

Official docs (searchable, very readable): https://laravel.com/docs
