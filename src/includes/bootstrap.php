<?php
/**
 * Include this FIRST in every page:
 *   require __DIR__ . '/../../includes/bootstrap.php';
 * (from src/public/<module>/page.php)
 *
 * Please ask before changing; every module depends on it.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth_check.php';

// Role names must match the `roles` table. This is just dummy roles change when needed
const ROLE_MANAGER   = 'Manager';
const ROLE_WAREHOUSE = 'Warehouse Staff';
const ROLE_PRODUCTION = 'Production Worker';
