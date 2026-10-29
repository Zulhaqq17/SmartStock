# One-time Laravel setup (team leader only)

Do this once on its own branch, merge it, and only then tell the team to pull. Nobody should write code on the old structure while this is in progress.

Commands are for PowerShell on Windows, run in the repo root. Docker Desktop must be running.

## 1. Branch and remove the old plain-PHP app
```powershell
git checkout main
git pull origin main
git checkout -b chore/laravel-setup
git rm -r src db
Remove-Item -Recurse -Force src, db -ErrorAction SilentlyContinue
```
(`src/` and `db/` must not exist before the next step. The old `db/init` SQL is replaced by migrations.)

## 2. Create a fresh Laravel app inside `src/`
```powershell
docker run --rm -v "${PWD}:/app" -w /app composer:2 create-project laravel/laravel src
```
This downloads Laravel (a few minutes). On Linux/macOS use `$(pwd)` instead of `${PWD}`, then `sudo chown -R $USER src`.

## 3. Add the SmartStock files over it
Extract `smartstock-laravel.zip` into the repo root and **overwrite everything it asks about**. It replaces `Dockerfile`, `docker-compose.yml`, `.env.example`, `README.md`, `docs/*`, and adds our migrations, models, services, controllers, views, routes and seeder inside `src/`.

Overwritten Laravel defaults: `src/app/Models/User.php`, `src/routes/web.php`, `src/database/seeders/DatabaseSeeder.php`.

## 4. Update your own `.env`
Your existing `.env` still works. Change `APP_ENV=development` to `APP_ENV=local` (or add `APP_ENV=local` and `APP_DEBUG=true` if missing). Compare with `.env.example`.

## 5. Build and start
```powershell
docker compose down -v
docker compose up -d --build
docker compose logs -f web
```
The first start runs `composer install` inside the container (1 to 3 minutes). Wait until the log shows Apache is running, then press Ctrl+C.

## 6. Create the tables and sample data
```powershell
docker compose exec web php artisan migrate:fresh --seed
```
Open http://localhost:8080 and log in with `manager@dev.local` / `password`. Check Dashboard, Inventory (should list 4 products) and Alerts.

## 7. Commit and open the PR
```powershell
git add .
git status          # make sure src/vendor and any .env are NOT listed
git commit -m "chore: switch project to Laravel"
git push -u origin chore/laravel-setup
```
Merge on GitHub, then tell the team:
```
git checkout main
git pull origin main
docker compose down -v
docker compose up -d --build
docker compose exec web php artisan migrate:fresh --seed
```

## Notes
- `src/.env` is created by Laravel's installer and is ignored by Git. The container uses the variables from the root `.env` through `docker-compose.yml`, so nobody needs to edit it.
- `APP_KEY` in `docker-compose.yml` is a shared **development** key. Use a different one when deploying (`docker compose exec web php artisan key:generate --show`).
- If `composer create-project` fails with a network error, retry; if it keeps failing, check Docker Desktop's internet access.
- Fill in the `Owner: TODO` names in `docs/MODULES.md` and in the controllers/services.
