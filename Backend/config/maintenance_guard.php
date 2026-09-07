<?php
/**
 * Shared access guard for root-level maintenance and debug scripts.
 *
 * These scripts (setup_*.php, import_data.php, update_admin_password.php,
 * status.php, check_*.php, fix_git_pull.php, ...) perform powerful actions
 * such as running schema migrations, resetting admin passwords or pulling
 * new code from git. They must never be reachable by anonymous visitors on
 * the live site, so they require either:
 *
 *   1. Command-line execution (SSH / cPanel Terminal / cron), or
 *   2. An active farm administrator session (super_admin / farm_manager).
 *
 * Scripts that are extra-sensitive (e.g. update_admin_password.php) can set
 * $requiredRoles = ['super_admin']; BEFORE requiring this file.
 *
 * Usage from a root script:
 *   require __DIR__ . '/Backend/config/maintenance_guard.php';
 */
declare(strict_types=1);

// Command-line execution is always allowed.
if (PHP_SAPI === 'cli') {
    return;
}

if (session_status() === PHP_SESSION_NONE) {
    $temp_dir = sys_get_temp_dir();
    if (is_writable($temp_dir)) {
        session_save_path($temp_dir);
    }
    session_start();
}

$requiredRoles = $requiredRoles ?? ['super_admin', 'farm_manager'];
$isAdmin = !empty($_SESSION['user_id'])
    && in_array($_SESSION['role'] ?? '', $requiredRoles, true);

if (!$isAdmin) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Access denied. This maintenance script can only be run from the command line or by a logged-in farm administrator.');
}

return;
