# Team conventions

## How a page is built
```php
<?php
require __DIR__ . '/../../includes/bootstrap.php';            // session, db(), helpers, auth
require __DIR__ . '/../../modules/<module>/<Name>Service.php'; // your logic
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);                  // or require_login()

// 1. handle POST (csrf_verify(), validate, call service, flash_set + redirect)
// 2. load data with service functions
$pageTitle = 'My Page';
require __DIR__ . '/../../includes/header.php';
?>
... HTML ...
<?php require __DIR__ . '/../../includes/footer.php'; ?>
```

## Rules
- Logic and SQL go in `src/modules/`. Pages only call functions and display results.
- Prepared statements for anything from the user. Never concatenate input into SQL.
- Print every value with `e()`. Forms include `<?= csrf_field() ?>` and call `csrf_verify()` on POST.
- Multi-table writes (batch + transaction row) use `beginTransaction()`, `commit()`, `rollBack()`.
- Do not delete rows that history depends on: deactivate (`is_active = 0`) instead.
- Session keys: `user_id`, `full_name`, `role`. Roles: `ROLE_MANAGER`, `ROLE_WAREHOUSE`, `ROLE_PRODUCTION`.
- Functions: `camelCase`. Files/folders: `lowercase_with_underscores`. Service files: `<Name>Service.php`.
- Module CSS/JS: `assets/css/<module>.css`, `assets/js/<module>.js`. Don't edit another module's files.
- Database change? Edit `db/init/01_schema.sql`, update `docs/smartstock_erd.dbml`, tell the team.

## Dev login
`/dev_login.php` (only when `APP_ENV=development`) logs you in as a role for testing. Delete it before final submission.
