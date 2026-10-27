# Module guide

Each module = one **logic folder** + one **pages folder**. Only edit your own unless it is a shared file.

| # | Module | Owner | Logic (`src/modules/`) | Pages (`src/public/`) |
|---|--------|-------|------------------------|------------------------|
| 1 | User Authentication and Access Management | TODO | `auth/AuthService.php` | `auth/login.php`, `auth/logout.php` |
| 2 | Inventory and Product Management | TODO | `inventory/InventoryService.php` | `inventory/` (index = working example) |
| 3 | Stock Entry and Batch Management | TODO | `stock_entry/StockEntryService.php` | `stock_entry/` |
| 4 | Stock Movement and Quantity Tracking | TODO | `stock_movement/StockMovementService.php` | `stock_movement/` |
| 5 | FEFO, Expiry and Low-Stock Alert Management | Fikri | `alerts/AlertService.php` | `alerts/` |
| 6 | Inventory Dashboard and Reporting | TODO | `dashboard/DashboardService.php` | `dashboard/` |

Shared files (`src/includes/`, `src/config/`, `src/public/assets/css/style.css`): ask the team leader before changing, or open a small separate PR.

## Dependencies between modules

- Module 4 calls `runAllChecks()` (Module 5) after recording a movement.
- Module 6 reads alerts and stock data from Modules 2, 4 and 5.
- Module 3 and 4 both write `stock_transactions`: always inside one DB transaction with the batch update.
- Everyone needs login from Module 1. Until it is merged, use `/dev_login.php`.

## Definition of done (per module)

- [ ] Every function in your service file is implemented (no `TODO` left)
- [ ] Pages enforce the right roles (`require_role` / `require_login`)
- [ ] All queries use PDO prepared statements; all output uses `e()`; all forms use CSRF
- [ ] Works on a fresh `docker compose down -v && docker compose up -d --build`
- [ ] Tested with the seed data and your own edge cases
- [ ] Module flowchart and ERD still match the code
