<?php
/**
 * Permissions & access control.
 *
 * Two layers decide what a signed-in staff member can open:
 *
 *   1. role_permissions  — the default matrix for their role
 *                          (Admin → Settings → Roles & Permissions).
 *   2. user_permissions  — per-person overrides saved from the staff
 *                          edit screen. When a row exists for a module
 *                          it wins over the role default, so one farm
 *                          manager can be limited to Flocks while
 *                          another sees Finance too.
 *
 * Levels per module: view, edit, delete, manage.
 *   view   → may open the module
 *   edit   → may create / update records
 *   delete → may remove records
 *   manage → may configure the module (settings, permissions, imports)
 *
 * super_admin always has every level; customer has none.
 */
declare(strict_types=1);

require_once __DIR__ . '/migrations.php';

/**
 * Canonical module groups. Used by the staff permission editor, the
 * role matrix page and the sidebar, so all three can never drift.
 */
function busiaPermissionModules(): array
{
    return [
        'Dashboard' => ['dashboard' => 'Dashboard'],
        'Poultry Operations' => [
            'flocks' => 'Flocks & Batches',
            'production' => 'Daily Production',
            'vaccinations' => 'Vaccinations',
            'batches' => 'Batches & Houses',
            'health' => 'Health & Vet',
            'broiler' => 'Broiler Workflow',
            'hatchery' => 'Hatchery (DOC)',
            'feeding' => 'Feeding Program',
            'losses' => 'Losses & Quality',
        ],
        'Inventory & Stores' => [
            'products' => 'Products Catalog',
            'stores' => 'Stores & Stock',
            'feed_production' => 'Feed Production',
            'egg_grading' => 'Egg Grading',
        ],
        'Sales & Finance' => [
            'hub_finance' => 'Sales & Finance Hub',
            'profit' => 'Costs & Profit',
            'cashbook' => 'Cashbook',
            'credit' => 'Customer Credit',
            'lpo' => 'LPO & Invoicing',
            'purchase_orders' => 'Procurement (PO)',
            'daily_sales' => 'Daily Reconciliation',
            'bulk_sales' => 'Bulk Sales',
        ],
        'Reports & Tools' => [
            'analytics' => 'Analytics & Charts',
            'bulk_import_export' => 'Bulk Import / Export',
        ],
        'Team & Messages' => [
            'staff' => 'Staff Accounts',
            'users' => 'Customers / Accounts',
            'tasks' => 'Assigned Tasks',
            'messages' => 'Team Messages',
        ],
        'Website' => [
            'website_cms' => 'Website CMS',
        ],
        'Settings' => [
            'calendar' => 'Calendar',
            'dropdowns' => 'Lists & Options',
            'settings' => 'App Settings',
            'logs' => 'Activity Logs',
            'permissions' => 'Roles & Permissions',
        ],
    ];
}

/**
 * Flat list of module keys.
 */
function busiaPermissionModuleKeys(): array
{
    $keys = [];
    foreach (busiaPermissionModules() as $modules) {
        foreach ($modules as $key => $label) {
            $keys[] = $key;
        }
    }
    return $keys;
}

/**
 * Human label for one module key.
 */
function busiaModuleLabel(string $key): string
{
    foreach (busiaPermissionModules() as $modules) {
        if (isset($modules[$key])) {
            return $modules[$key];
        }
    }
    return ucwords(str_replace('_', ' ', $key));
}

/**
 * The four permission levels, in display order.
 */
function busiaPermissionLevels(): array
{
    return [
        'view'   => 'View',
        'edit'   => 'Add / Edit',
        'delete' => 'Delete',
        'manage' => 'Manage',
    ];
}

/**
 * Role defaults as a full matrix: [role][module] => [view,edit,delete,manage].
 */
function busiaRoleMatrix(?PDO $pdo = null): array
{
    $pdo = $pdo ?: busiaPermissionsPdo();
    $keys = busiaPermissionModuleKeys();

    $matrix = [];
    foreach (['super_admin', 'farm_manager', 'stock_manager', 'sales_staff', 'customer'] as $role) {
        foreach ($keys as $key) {
            $matrix[$role][$key] = [
                'view' => 0, 'edit' => 0, 'delete' => 0, 'manage' => 0,
            ];
        }
    }

    // super_admin always full; farm_manager defaults to full (as before).
    foreach (['super_admin', 'farm_manager'] as $role) {
        foreach ($keys as $key) {
            $matrix[$role][$key] = ['view' => 1, 'edit' => 1, 'delete' => 1, 'manage' => 1];
        }
    }

    // Sensible module defaults for the limited roles.
    $limited = [
        'stock_manager' => ['dashboard', 'products', 'stores', 'feed_production', 'egg_grading', 'batches', 'losses'],
        'sales_staff'   => ['dashboard', 'hub_finance', 'profit', 'cashbook', 'credit', 'daily_sales', 'bulk_sales', 'lpo', 'purchase_orders', 'users', 'tasks', 'messages'],
    ];
    foreach ($limited as $role => $allowed) {
        foreach ($allowed as $key) {
            $matrix[$role][$key] = ['view' => 1, 'edit' => 1, 'delete' => 0, 'manage' => 0];
        }
    }

    if (!$pdo) {
        return $matrix;
    }

    // Apply the stored role matrix (view/edit only, legacy shape).
    try {
        $rows = $pdo->query('SELECT role, module_key, can_view, can_edit FROM role_permissions')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $role = (string)$row['role'];
            $module = (string)$row['module_key'];
            if (!isset($matrix[$role][$module])) {
                continue;
            }
            if ($role === 'super_admin') {
                continue;
            }
            $matrix[$role][$module] = [
                'view'   => (int)$row['can_view'],
                'edit'   => (int)$row['can_edit'],
                'delete' => (int)$row['can_edit'],   // legacy: edit implied delete
                'manage' => (int)$row['can_edit'] && in_array($module, ['settings', 'permissions', 'dropdowns', 'website_cms'], true) ? 1 : 0,
            ];
        }
    } catch (Throwable $e) {
        // table missing — defaults stand
    }

    return $matrix;
}

/**
 * Stored per-user overrides: [user_id][module] => [view,edit,delete,manage].
 */
function busiaUserOverrides(PDO $pdo, int $userId): array
{
    $out = [];
    if ($userId <= 0) {
        return $out;
    }
    try {
        $stmt = $pdo->prepare('SELECT module_key, can_view, can_edit, can_delete, can_manage FROM user_permissions WHERE user_id = ?');
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(string)$row['module_key']] = [
                'view'   => (int)$row['can_view'],
                'edit'   => (int)$row['can_edit'],
                'delete' => (int)$row['can_delete'],
                'manage' => (int)$row['can_manage'],
            ];
        }
    } catch (Throwable $e) {
        // table missing — role defaults stand
    }
    return $out;
}

/**
 * Effective permissions for one user: role defaults + personal overrides.
 */
function busiaEffectivePermissions(PDO $pdo, int $userId, string $role): array
{
    $matrix = busiaRoleMatrix($pdo);
    $effective = $matrix[$role] ?? $matrix['customer'];
    $overrides = busiaUserOverrides($pdo, $userId);

    foreach ($overrides as $module => $levels) {
        if (!isset($effective[$module])) {
            continue;
        }
        $effective[$module] = $levels;
    }

    if ($role === 'super_admin') {
        foreach ($effective as $module => $levels) {
            $effective[$module] = ['view' => 1, 'edit' => 1, 'delete' => 1, 'manage' => 1];
        }
    }

    return $effective;
}

/**
 * Persist a user's permission matrix. Every module gets an explicit row
 * so the saved state is authoritative.
 *
 * @param array<string,array<string,int|bool|string>> $levels
 */
function busiaSaveUserPermissions(PDO $pdo, int $userId, array $levels): int
{
    if ($userId <= 0) {
        throw new RuntimeException('Unknown staff account.');
    }

    // The table ships with migration v8; create it defensively for hosts
    // whose migration has not run yet.
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module_key VARCHAR(50) NOT NULL,
            can_view TINYINT(1) NOT NULL DEFAULT 0,
            can_edit TINYINT(1) NOT NULL DEFAULT 0,
            can_delete TINYINT(1) NOT NULL DEFAULT 0,
            can_manage TINYINT(1) NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_module (user_id, module_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
        busiaDbLog('Could not create user_permissions: ' . $e->getMessage());
        throw new RuntimeException('The permissions table is missing. Run tools/migrate.php to repair the database.');
    }

    $stmt = $pdo->prepare('INSERT INTO user_permissions
        (user_id, module_key, can_view, can_edit, can_delete, can_manage)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_edit = VALUES(can_edit),
            can_delete = VALUES(can_delete), can_manage = VALUES(can_manage)');

    $saved = 0;
    foreach (busiaPermissionModuleKeys() as $module) {
        $row = $levels[$module] ?? [];
        $view = !empty($row['view']) ? 1 : 0;
        // Any stronger level implies view.
        if (!empty($row['edit']) || !empty($row['delete']) || !empty($row['manage'])) {
            $view = 1;
        }
        $stmt->execute([
            $userId,
            $module,
            $view,
            !empty($row['edit']) ? 1 : 0,
            !empty($row['delete']) ? 1 : 0,
            !empty($row['manage']) ? 1 : 0,
        ]);
        $saved++;
    }
    return $saved;
}

/**
 * PDO connection without assuming bootstrap order.
 */
function busiaPermissionsPdo(): ?PDO
{
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    if (function_exists('getDB')) {
        $pdo = getDB();
        return $pdo instanceof PDO ? $pdo : null;
    }
    if (function_exists('getDatabaseConnection')) {
        $pdo = getDatabaseConnection();
        return $pdo instanceof PDO ? $pdo : null;
    }
    return null;
}

/**
 * Load the current session's effective permissions once per request.
 */
function busiaCurrentPermissions(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    $role = (string)($_SESSION['role'] ?? '');
    if ($userId <= 0) {
        return $cache = [];
    }
    if ($role === 'super_admin') {
        $all = [];
        foreach (busiaPermissionModuleKeys() as $key) {
            $all[$key] = ['view' => 1, 'edit' => 1, 'delete' => 1, 'manage' => 1];
        }
        return $cache = $all;
    }

    $pdo = busiaPermissionsPdo();
    if (!$pdo) {
        return $cache = [];
    }
    return $cache = busiaEffectivePermissions($pdo, $userId, $role);
}

/**
 * Can the signed-in user perform $action on $module?
 */
function busiaCan(string $module, string $action = 'view'): bool
{
    if (($_SESSION['role'] ?? '') === 'super_admin') {
        return true;
    }
    $perms = busiaCurrentPermissions();
    $levels = $perms[$module] ?? null;
    if (!$levels) {
        return false;
    }
    return !empty($levels[$action] ?? $levels['view'] ?? 0);
}

/**
 * Shortcut used by the sidebar / header.
 */
function busiaCanView(string $module): bool
{
    return busiaCan($module, 'view');
}
