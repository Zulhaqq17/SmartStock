# SmartStock

Instructions/Cheatsheet  if u forgor 

Smart Inventory and Expiry Tracking System to Reduce Product Wastage in Food Manufacturing SMEs.
BITU 3923 Workshop 2, Group K27.

Stack: Laravel 13 (PHP 8.3, Apache), MySQL 8, Bootstrap 5, Docker, phpMyAdmin (dev only).

New to Laravel? Don't panic. Read [docs/LARAVEL_GUIDE.md](docs/LARAVEL_GUIDE.md) once (10 minutes), it explains everything we use.

## Quick start

Requirements: [Docker Desktop](https://www.docker.com/products/docker-desktop/) and [Git](https://git-scm.com/download/win).
Open Docker Desktop first and wait until it says running.

## Cloning the repo
Open terminal in the folder that you want the project to be save in and use the command
```bash
git clone https://github.com/Zulhaqq17/SmartStock.git smartstock 
cd smartstock
cp .env.example .env  # Copy example and create local .env configuration
docker compose up -d --build 
```

The first start is slow (2 to 3 minutes): the container downloads Laravel's packages by itself. Watch it with:
```bash
docker compose logs -f web      # wait until Apache is running, then Ctrl+C
```

Then create the tables and the sample data (one time):
```bash
docker compose exec web php artisan migrate:fresh --seed
```

| Service | URL |
|---|---|
| Web app | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL (from host) | `localhost:3307` |

Open http://localhost:8080 and log in with one of the dev accounts (password for all: `password`):

| Role | Email |
|---|---|
| Manager | manager@dev.local |
| Warehouse Staff | warehouse@dev.local |
| Production Worker | production@dev.local |

Check: Inventory page should list 4 products. Different roles see different menus.

Most of the stuff already been setup so its fine 

## Everyday commands

### 1. Docker Commands

```bash
docker compose up -d            # start (normal daily command)
docker compose logs -f web      # watch PHP/Apache/Laravel logs
docker compose down             # stop (keeps data)
docker compose down -v          # stop AND wipe your local database
docker compose exec db mysql -u root -p smartstock   # MySQL shell
```
PHP files in `src/` are mounted into the container, so edit and refresh. No rebuild needed.

After `git pull`: run `docker compose up -d` (it applies new migrations by itself). If `Dockerfile` or `docker-compose.yml` changed, add `--build`.
If someone changed the seed data, run `docker compose exec web php artisan migrate:fresh --seed`.

### 2. Laravel (artisan) Commands
Always put `docker compose exec web` in front:

```bash
docker compose exec web php artisan route:list            # all URLs in the app
docker compose exec web php artisan migrate               # apply new database changes
docker compose exec web php artisan migrate:fresh --seed  # reset local DB + sample data
docker compose exec web php artisan make:migration add_x_to_y_table
docker compose exec web php artisan optimize:clear        # fixes "my change doesn't show up"
docker compose exec web php artisan alerts:check          # run the Module 5 check
```

### 3. Daily Git Command (Aku pon selalu lupa)
#### Starting & Branching

```bash
git checkout main
git pull origin main  # Get the latest code from main
```
#### Create your branch on GitHub:
![Much easier on GitHub](./img/Branch1.png)

```bash
git fetch origin # Download new branch references from GitHub
git checkout feature/your-feature-name  # Switch to your new feature branch
git status # See what files you've changed or staged
```



#### Saving & Pushing Changes

```bash
git add . # Stage all changed files
git commit -m "feat: description of what you built" # Save your changes locally with a descriptive message
git push -u origin feature/your-feature-name # First push only (links local branch to remote)
git push # All future pushes on this branch
```

#### After PR Merged

```bash
git checkout main
git pull origin main # Switch back to main and update it
git branch -d feature/your-feature-name # Delete your old local branch (use -D if git says "not fully merged")
```

## Project structure

The Laravel app lives in `src/`.

```
src/routes/web.php                 All URLs (one block per module)
src/app/Http/Controllers/          Controllers (receive request, return page)
src/app/Services/                  Business logic and queries (most of your code)
src/app/Models/                    One class per table
src/resources/views/               Blade pages (HTML), one folder per module
src/database/migrations/           Database structure (create/change tables)
src/database/seeders/              Sample data
docker/, Dockerfile, docker-compose.yml   Docker setup (leader owns)
docs/                              LARAVEL_GUIDE.md, MODULES.md, CONVENTIONS.md, ERD, flowcharts
```

Request flow: **Route -> Controller -> Service -> Model -> View**.
Working example to copy: Inventory page (`InventoryController::index`, `InventoryService::getAllProducts`, `views/inventory/index.blade.php`).

## Where do I write my module?

See [docs/MODULES.md](docs/MODULES.md) for the owner and files of each module. In short, every module has:
a controller, a service, a views folder, and a block of routes in `routes/web.php`. Only edit your own, shared files (layout, models, migrations, Docker) need the leader's OK.

## Database changes

We use **migrations** now, not SQL files. To change a table:
```bash
docker compose exec web php artisan make:migration add_notes_to_batches_table
# edit the new file in src/database/migrations, then:
docker compose exec web php artisan migrate
```
Then add the column to the model's `$fillable`, update `docs/smartstock_erd.dbml`, and tell the group chat. Details in [docs/LARAVEL_GUIDE.md](docs/LARAVEL_GUIDE.md).

## Git workflow

- `main` is always working. Nobody pushes to it directly.
- One branch per task: `feature/module-5-expiry-alerts`, `fix/login-redirect`.
- Small commits with clear messages: `feat: add FEFO batch query`, `fix: correct low-stock comparison`.
- Open a Pull Request into `main`; at least one teammate reviews before merging.
- Pull `main` before starting new work: `git pull origin main`.


## Modules

1. User Authentication and Access Management
2. Inventory and Product Management
3. Stock Entry and Batch Management
4. Stock Movement and Quantity Tracking
5. FEFO, Expiry and Low-Stock Alert Management
6. Inventory Dashboard and Reporting

## Rules

- Never commit `.env` or any password. Use `.env.example` for new variables.
- Validate every form (`$request->validate`), put `@csrf` in every POST form, print with `{{ }}`.
- No SQL in views or controllers. Use Eloquent in services.
- Restrict pages by role in `routes/web.php`, not only by hiding buttons.
- Passwords are hashed automatically by the `User` model. Never store plain passwords.
- More rules in [docs/CONVENTIONS.md](docs/CONVENTIONS.md).
- AI-assisted code must be tested, understood, and recorded in the AI Use Log.

## Troubleshooting

- Docker commands fail or "cannot connect": open Docker Desktop and wait until it is running.
- Page not loading right after `up`: the first start is still installing packages. Check `docker compose logs -f web`.
- Port already in use: change `WEB_PORT`, `PMA_PORT` or `DB_HOST_PORT` in `.env`.
- Login says invalid / no tables / "Table doesn't exist": run `docker compose exec web php artisan migrate:fresh --seed`.
- Red error page or "500 Server Error": read the first line of the error, or check `src/storage/logs/laravel.log`.
- "Permission denied" on `storage`: run `docker compose exec web chmod -R a+rwX storage bootstrap/cache`.
- Changes not showing: `docker compose exec web php artisan optimize:clear`, then hard refresh (Ctrl+F5).
- "Class not found": missing `use ...;` at the top of the file, or `docker compose exec web composer dump-autoload`.
- Dev accounts hint not shown on the login page: set `APP_ENV=local` in `.env`, then `docker compose up -d`.
