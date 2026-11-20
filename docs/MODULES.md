# Module guide

Each module has its own **controller, service, views and routes block**. Only edit your own module's files.
Controllers/services/views are under `src/app/...` and `src/resources/views/...`.

| # | Module | Owner | Controller (`src/app/Http/Controllers/`) | Service (`src/app/Services/`) | Views (`src/resources/views/`) |
|---|--------|-------|---|---|---|
| 1 | User Authentication and Access Management | TODO | `AuthController`, `UserController` | (Laravel handles most of it) | `auth/`, `users/` |
| 2 | Inventory and Product Management | TODO | `InventoryController` | `InventoryService` | `inventory/` (index = working example) |
| 3 | Stock Entry and Batch Management | TODO | `StockEntryController` | `StockEntryService` | `stock_entry/` |
| 4 | Stock Movement and Quantity Tracking | TODO | `StockMovementController` | `StockMovementService` | `stock_movement/` |
| 5 | FEFO, Expiry and Low-Stock Alert Management | Fikri | `AlertController` (+ `Console/Commands/RunAlertChecks`) | `AlertService` | `alerts/` |
| 6 | Inventory Dashboard and Reporting | TODO | `DashboardController` | `DashboardService` | `dashboard/` |

Your URLs live in your module's block in `src/routes/web.php`. Models are in `src/app/Models/` (shared: change only with the team's OK).

Shared files (ask the team leader first, or open a small separate PR):
`resources/views/layouts/app.blade.php`, `app/Http/Middleware/EnsureRole.php`, `app/Models/*`, `database/migrations/*`, `database/seeders/DatabaseSeeder.php`, `docker-compose.yml`, `Dockerfile`.

## Dependencies between modules

- Module 4 calls `AlertService::runAllChecks()` (Module 5) after recording a movement.
- Module 6 reads alerts and stock data produced by Modules 2, 4 and 5.
- Modules 3 and 4 both write `stock_transactions`: always inside one `DB::transaction()` together with the batch update.
- Everyone needs login from Module 1. Basic login already works with the dev accounts, so nobody has to wait.

## Definition of done (per module)

- [ ] Every method in your service has no `TODO` left
- [ ] Routes use the right access group (`auth`, manager/warehouse, manager only)
- [ ] Forms have `@csrf` and server-side validation; output uses `{{ }}`
- [ ] Works after `docker compose exec web php artisan migrate:fresh --seed`
- [ ] Tested with the seed data and your own edge cases
- [ ] Module flowchart and ERD still match the code
