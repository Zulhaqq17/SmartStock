# Team conventions (Laravel)

New to Laravel? Read [LARAVEL_GUIDE.md](LARAVEL_GUIDE.md) first.

## Request flow (every page works like this)

```
Browser -> routes/web.php -> Controller -> Service (logic) -> Model (database) -> Blade view -> Browser
```

- **Route**: maps a URL to a controller method.
- **Controller**: reads the request, validates input, calls a service, returns a view or redirect. Keep it thin.
- **Service**: business rules and queries (`app/Services`). This is where most of your code goes.
- **Model**: one class per table (`app/Models`), with relationships.
- **View**: Blade template (`resources/views`). Display only, no queries.

## Rules

- No SQL in views or controllers. Use Eloquent in services.
- Validate every form with `$request->validate([...])`. Never trust input.
- Every `POST/PUT/DELETE` form has `@csrf` (and `@method('PUT')` / `@method('DELETE')` when needed).
- Print values with `{{ }}` (escaped). Do not use `{!! !!}` for user data.
- Writes that touch more than one table use `DB::transaction(function () { ... })`.
- Do not delete rows other tables depend on: deactivate (`is_active = false`).
- Use role constants: `Role::MANAGER`, `Role::WAREHOUSE`, `Role::PRODUCTION`. Restrict routes in `routes/web.php` (not by hiding buttons only).
- Classes: `PascalCase` (`AlertService`). Methods/variables: `camelCase`. Views and folders: `snake_case`. Route names: `module.action` (`inventory.index`).
- Show messages with `->with('success', '...')` / `->with('error', '...')`; the layout displays them.
- UI uses Bootstrap 5 classes. Don't add another CSS framework.

## Database changes

- Never edit the database by hand in phpMyAdmin for something the team needs. Use a migration:
  `docker compose exec web php artisan make:migration add_something_to_batches_table`
- Update `docs/smartstock_erd.dbml` and tell the group chat.
- Everyone then runs `docker compose exec web php artisan migrate` (or `migrate:fresh --seed` to reset local data).
