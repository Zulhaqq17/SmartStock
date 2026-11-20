<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureRole;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

/*
| All URLs of the app live here. One line = one URL:
|   Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
|   (HTTP method, URL, controller + method, a name you use in views: route('inventory.index'))
|
| Add your own routes inside YOUR module's block. Don't reorder other people's blocks.
*/

// Role groups (EnsureRole middleware). 'auth' = must be logged in.
$managerOnly = ['auth', EnsureRole::class.':'.Role::MANAGER];
$managerOrWarehouse = ['auth', EnsureRole::class.':'.Role::MANAGER.','.Role::WAREHOUSE];

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

// ---------------- Module 1: Authentication and access ----------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware($managerOnly)->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// ---------------- Module 6: Dashboard and reports ----------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
Route::middleware($managerOrWarehouse)->group(function () {
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
});

// ---------------- Module 2: Inventory and products ----------------
Route::middleware('auth')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
});
Route::middleware($managerOrWarehouse)->group(function () {
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
});

// ---------------- Module 3: Stock entry and batches ----------------
Route::middleware($managerOrWarehouse)->group(function () {
    Route::get('/stock-entry', [StockEntryController::class, 'index'])->name('stock_entry.index');
    Route::get('/stock-entry/create', [StockEntryController::class, 'create'])->name('stock_entry.create');
    Route::post('/stock-entry', [StockEntryController::class, 'store'])->name('stock_entry.store');
    Route::get('/stock-entry/import', [StockEntryController::class, 'importForm'])->name('stock_entry.import');
    Route::post('/stock-entry/import', [StockEntryController::class, 'import'])->name('stock_entry.import.store');
});

// ---------------- Module 4: Stock movement ----------------
Route::middleware('auth')->group(function () {
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock_movement.index');
    Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock_movement.create');
    Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock_movement.store');
});

// ---------------- Module 5: FEFO, expiry and low-stock alerts ----------------
Route::middleware('auth')->group(function () {
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/run', [AlertController::class, 'run'])->name('alerts.run');
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
});
