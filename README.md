# SmartStock

Smart Inventory and Expiry Tracking System to Reduce Product Wastage in Food Manufacturing SMEs.
BITU 3923 Workshop 2, Group K27, UTeM FTMK.

Stack: PHP 8.3 (Apache), MySQL 8, Docker, phpMyAdmin (dev only).

## Quick start

Requirements: [Docker Desktop](https://www.docker.com/products/docker-desktop/) and Git.

```bash
git clone <repo-url> smartstock
cd smartstock
cp .env.example .env        # Windows PowerShell: copy .env.example .env
docker compose up -d --build
```

| Service | URL |
|---|---|
| Web app | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL (from host) | `localhost:3307` |

Open http://localhost:8080. You should see "Database connection: OK", 8 tables and 5 sample batches ordered by expiry date.

## Everyday commands

```bash
docker compose up -d            # start
docker compose logs -f web      # watch PHP/Apache logs
docker compose down             # stop (keeps data)
docker compose down -v          # stop AND wipe the database (re-runs db/init/*.sql)
docker compose exec db mysql -u root -p smartstock   # MySQL shell
```

PHP files in `src/` are mounted into the container, so edit and refresh. No rebuild needed.

## Project structure

```
db/init/        SQL run automatically on first start (schema, then seed)
src/public/     Web root (index.php and page entry points)
src/config/     Shared config, e.g. database connection
docs/           Flowcharts, ERD (smartstock_erd.dbml), diagrams
.github/        Pull request template
```

## Database changes

`db/init/*.sql` only runs when the database volume is created. After editing a schema file, everyone runs:

```bash
docker compose down -v && docker compose up -d --build
```

Keep `docs/smartstock_erd.dbml` in sync with `01_schema.sql`.

## Git workflow

- `main` is always working. Nobody pushes to it directly.
- One branch per task: `feature/module-5-expiry-alerts`, `fix/login-redirect`.
- Small commits with clear messages: `feat: add FEFO batch query`, `fix: correct low-stock comparison`.
- Open a Pull Request into `main`; at least one teammate reviews before merging.
- Pull `main` before starting new work: `git pull origin main`.

```bash
git checkout main && git pull
git checkout -b feature/module-5-expiry-alerts
# ...work, then...
git add . && git commit -m "feat: add near-expiry check"
git push -u origin feature/module-5-expiry-alerts
```

## Modules

1. User Authentication and Access Management
2. Inventory and Product Management
3. Stock Entry and Batch Management
4. Stock Movement and Quantity Tracking
5. FEFO, Expiry and Low-Stock Alert Management
6. Inventory Dashboard and Reporting

## Rules

- Never commit `.env` or any password. Use `.env.example` for new variables.
- Use prepared statements (PDO) for every query that takes user input.
- Hash passwords with `password_hash()`.
- AI-assisted code must be tested, understood, and recorded in the AI Use Log.

## Troubleshooting

- Port already in use: change `WEB_PORT`, `PMA_PORT` or `DB_HOST_PORT` in `.env`.
- "Connection refused" right after start: MySQL needs a few seconds on first boot; refresh.
- Tables missing: the volume already existed. Run `docker compose down -v` and start again.
