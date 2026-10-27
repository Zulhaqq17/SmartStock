<?php
/**
 * MODULE 1: User Authentication and Access Management
 * Owner: TODO (name)
 * Logic only (no HTML). Pages call these functions from public/auth/.
 */

/** TODO: find user by email (prepared statement), join roles to get role_name. Return array|null. */
function getUserByEmail(string $email): ?array
{
    return null;
}

/**
 * TODO: check is_active, verify with password_verify(), then set
 * $_SESSION['user_id'], ['full_name'], ['role'] and call session_regenerate_id(true).
 * Return true on success.
 */
function attemptLogin(string $email, string $password): bool
{
    return false;
}

/** TODO: clear session data, destroy session. */
function logoutUser(): void
{
}

/** TODO (optional): create user with password_hash(). Manager-only. */
function createUser(string $fullName, string $email, string $password, int $roleId): bool
{
    return false;
}
