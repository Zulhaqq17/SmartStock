<?php
/**
 * Access control helpers used by every page.
 *
 * Session keys (agreed by the team, do not rename):
 *   $_SESSION['user_id']   int
 *   $_SESSION['full_name'] string
 *   $_SESSION['role']      string  (Manager | Warehouse Staff | Production Worker)
 */

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

/** Page requires any logged-in user. */
function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/auth/login.php');
    }
}

/** Page requires one of the listed roles, e.g. require_role([ROLE_MANAGER, ROLE_WAREHOUSE]); */
function require_role(array $allowed): void
{
    require_login();
    if (!in_array(current_role(), $allowed, true)) {
        http_response_code(403);
        exit('403: you do not have permission to view this page.');
    }
}
