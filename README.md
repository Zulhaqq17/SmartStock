# SmartStock

Instructions/Cheatsheet  if u forgor 

Smart Inventory and Expiry Tracking System to Reduce Product Wastage in Food Manufacturing SMEs.
BITU 3923 Workshop 2, Group K27.

Stack: PHP 8.3 (Apache), MySQL 8, Docker, phpMyAdmin (dev only).

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


| Service | URL |
|---|---|
| Web app | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL (from host) | `localhost:3307` |

Wait about 20 seconds on the first start (MySQL is importing the database), then open http://localhost:8080/health.php.
You should see "Database connection: OK", 8 tables and 5 sample batches ordered by expiry date.

Most of the stuff already been setup so its fine 

## Testing without login (dev only)

Login (Module 1) is not done yet, so to test your pages as any role go to http://localhost:8080/dev_login.php
and pick Manager / Warehouse Staff / Production Worker.
It only works if your `.env` has `APP_ENV=development` (already in `.env.example`).
We delete this file before final submission.

## Everyday commands

### 1. Docker Commands

```bash
docker compose up -d            # start (normal daily command)
docker compose logs -f web      # watch PHP/Apache logs
docker compose down             # stop (keeps data)
docker compose down -v          # stop AND wipe your local database (re-runs db/init/*.sql)
docker compose exec db mysql -u root -p smartstock   # MySQL shell
```
PHP files in `src/` are mounted into the container, so edit and refresh. No rebuild needed.

After `git pull`: run `docker compose up -d --build` only if `Dockerfile` or `docker-compose.yml` changed,
and `docker compose down -v && docker compose up -d --build` only if something in `db/init/` changed.

### 2. Daily Git Command (Aku pon selalu lupa)
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

```
db/init/              SQL run automatically on first start (schema, then seed)
src/config/           Database connection
src/includes/         Shared: bootstrap, auth_check, header, footer, helpers (leader owns)
src/modules/<name>/   Logic and SQL per module (Service.php files)
src/public/<name>/    Pages per module (web root)
src/public/health.php Database health check
docs/                 MODULES.md, CONVENTIONS.md, ERD (smartstock_erd.dbml), flowcharts
.github/              Pull request template
```

Start here: [docs/MODULES.md](docs/MODULES.md) (who owns what) and [docs/CONVENTIONS.md](docs/CONVENTIONS.md) (how to write a page).

## Database changes

`db/init/*.sql` only runs when the database volume is created. After editing a schema file, everyone runs:

```bash
docker compose down -v && docker compose up -d --build
```

This wipes your local data (only sample data, so its fine). Keep `docs/smartstock_erd.dbml` in sync with `01_schema.sql`
and tell the group chat.

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

Owners and files for each module are in [docs/MODULES.md](docs/MODULES.md).
Only edit your own module's folders (`src/modules/<name>/` and `src/public/<name>/`).
Shared files in `src/includes/` need the leader's OK.

## Rules

- Never commit `.env` or any password. Use `.env.example` for new variables.
- Use prepared statements (PDO) for every query that takes user input.
- Print every value with `e()`. Every POST form uses `csrf_field()` and `csrf_verify()`.
- Hash passwords with `password_hash()`.
- AI-assisted code must be tested, understood, and recorded in the AI Use Log.

## Troubleshooting

- Docker commands fail or "cannot connect": open Docker Desktop and wait until it is running.
- Port already in use: change `WEB_PORT`, `PMA_PORT` or `DB_HOST_PORT` in `.env`.
- "Connection refused" right after start: MySQL needs a few seconds on first boot; refresh.
- Tables missing or "No dev user for that role": the volume already existed. Run `docker compose down -v` and start again.
- `dev_login.php` shows "Not found": check `.env` has `APP_ENV=development`, then `docker compose up -d --build`.
