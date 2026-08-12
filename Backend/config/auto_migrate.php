<?php
/**
 * Auto-Migration — ensures all module tables exist on every connection.
 *
 * Safe to call repeatedly: CREATE TABLE IF NOT EXISTS makes re-runs no-ops,
 * and individual statement errors are skipped. The completeness guard below
 * re-runs a migration file whenever any of its tables is missing, so tables
 * added to the migration files later are created automatically.
 */
declare(strict_types=1);

/**
 * Split a migration .sql file into individual executable statements.
 *
 * Comment banner lines ("-- ...") are stripped BEFORE splitting. The old
 * approach split on ";" and then dropped chunks that STARTED with a comment —
 * which silently discarded every CREATE TABLE that followed a banner, leaving
 * tables like broiler_weighings / egg_losses permanently missing.
 */
function splitMigrationSql(string $sql): array
{
    // Remove full-line SQL comments (these files use "-- ..." banners)
    $lines = preg_split('/\R/', $sql);
    if ($lines !== false) {
        $lines = array_filter($lines, function (string $line): bool {
            $trimmed = ltrim($line);
            return $trimmed !== '' && !str_starts_with($trimmed, '--');
        });
        $sql = implode("\n", $lines);
    }

    // Strip any USE statement (we are already connected to the right DB)
    $sql = preg_replace('/USE\s+`?\w+`?\s*;/i', '', $sql);

    $statements = [];
    foreach (array_map('trim', explode(';', $sql)) as $stmt) {
        if ($stmt !== '') {
            $statements[] = $stmt;
        }
    }
    return $statements;
}

/**
 * Execute a migration file, skipping statements that fail (idempotent).
 */
function runMigrationFile(PDO $pdo, string $file): void
{
    $sql = @file_get_contents($file);
    if ($sql === false) {
        return;
    }
    foreach (splitMigrationSql($sql) as $stmt) {
        try {
            $pdo->exec($stmt);
        } catch (Exception $e) {
            // Ignore — already applied, or a transient FK ordering issue.
            // The completeness guard below re-runs on the next request.
        }
    }
}

/**
 * Table names created by a migration file, derived from the file itself.
 */
function migrationTableNames(string $file): array
{
    $sql = @file_get_contents($file);
    if ($sql === false) {
        return [];
    }
    preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?/i', $sql, $m);
    return array_values(array_unique($m[1] ?? []));
}

/**
 * Reconcile legacy column shapes with the schema the code expects.
 *
 * The raw_materials / suppliers tables in older databases use an older shape
 * (name, stock_tons, current_price_per_ton, feed_type) while the migration
 * files and every current module read (material_name, current_stock,
 * current_price_per_unit, unit, category) and (supplier_name). Adding columns
 * and back-filling from the legacy ones lets both old and new code read the
 * table, without touching or dropping any existing data. Idempotent.
 */
function reconcileLegacySchema(PDO $pdo): void
{
    // ── raw_materials ──
    if (tableExists($pdo, 'raw_materials')) {
        $add = [];
        if (!columnExists($pdo, 'raw_materials', 'material_name'))   $add[] = 'ADD COLUMN material_name VARCHAR(100) NULL AFTER id';
        if (!columnExists($pdo, 'raw_materials', 'material_code'))   $add[] = 'ADD COLUMN material_code VARCHAR(50) NULL';
        if (!columnExists($pdo, 'raw_materials', 'unit'))            $add[] = "ADD COLUMN unit VARCHAR(20) NOT NULL DEFAULT 'kg'";
        if (!columnExists($pdo, 'raw_materials', 'opening_balance')) $add[] = 'ADD COLUMN opening_balance DECIMAL(12,3) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'current_stock'))   $add[] = 'ADD COLUMN current_stock DECIMAL(12,3) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'current_price_per_unit')) $add[] = 'ADD COLUMN current_price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'category'))        $add[] = "ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'feed_ingredient'";
        if (!columnExists($pdo, 'raw_materials', 'supplier_id'))     $add[] = 'ADD COLUMN supplier_id INT NULL';
        if (!columnExists($pdo, 'raw_materials', 'notes'))           $add[] = 'ADD COLUMN notes TEXT NULL';
        if ($add) {
            $pdo->exec('ALTER TABLE raw_materials ' . implode(', ', $add));
        }

        // Back-fill from the legacy columns when they exist. The legacy stock
        // is stored in TONS (old code converts kg -> tons), the current schema
        // is in KG — so convert 1:1000 and price per ton -> per kg.
        if (columnExists($pdo, 'raw_materials', 'name')) {
            $pdo->exec("UPDATE raw_materials SET material_name = name WHERE material_name IS NULL OR material_name = ''");
            if (columnExists($pdo, 'raw_materials', 'stock_tons')) {
                $pdo->exec('UPDATE raw_materials SET current_stock = stock_tons * 1000, opening_balance = stock_tons * 1000 WHERE stock_tons IS NOT NULL AND (current_stock = 0 OR current_stock IS NULL)');
            }
            if (columnExists($pdo, 'raw_materials', 'current_price_per_ton')) {
                $pdo->exec('UPDATE raw_materials SET current_price_per_unit = current_price_per_ton / 1000 WHERE current_price_per_ton IS NOT NULL AND (current_price_per_unit = 0 OR current_price_per_unit IS NULL)');
            }
        }
    }

    // ── suppliers ──
    if (tableExists($pdo, 'suppliers')
        && !columnExists($pdo, 'suppliers', 'supplier_name')
        && columnExists($pdo, 'suppliers', 'name')) {
        $pdo->exec('ALTER TABLE suppliers ADD COLUMN supplier_name VARCHAR(150) NULL AFTER id');
        $pdo->exec("UPDATE suppliers SET supplier_name = name WHERE supplier_name IS NULL OR supplier_name = ''");
    }

    // ── financial_records (expenses) ──
    if (tableExists($pdo, 'financial_records')) {
        $add = [];
        if (!columnExists($pdo, 'financial_records', 'payment_method')) {
            $add[] = "ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash'";
        }
        if (!columnExists($pdo, 'financial_records', 'payment_status')) {
            $add[] = "ADD COLUMN payment_status ENUM('Pending','Approved','Failed','Completed') DEFAULT 'Completed'";
        }
        if ($add) {
            $pdo->exec('ALTER TABLE financial_records ' . implode(', ', $add));
        }
    }

    // ── egg_losses — add a "stage" column so broken eggs can be attributed
    //    to where they were damaged: during collection or on route (transport).
    if (tableExists($pdo, 'egg_losses') && !columnExists($pdo, 'egg_losses', 'stage')) {
        $pdo->exec("ALTER TABLE egg_losses ADD COLUMN stage ENUM('collection','transport','storage','other') NOT NULL DEFAULT 'collection' AFTER loss_type");
    }

    // ── users.role — older databases have an ENUM without 'sales_staff',
    //    so assigning that role silently stores an empty string. Widen it.
    if (tableExists($pdo, 'users')) {
        $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
        if ($col && str_contains($col['Type'] ?? '', 'enum') && !str_contains($col['Type'], 'sales_staff')) {
            $pdo->exec("ALTER TABLE users MODIFY role ENUM('super_admin','farm_manager','stock_manager','sales_staff','customer') NULL DEFAULT 'customer'");
        }
    }
}

/**
 * List of every admin module key (used by role permissions and the sidebar).
 * Keep in sync with admin_sidebar.php sub-module keys.
 */
function busiaModuleKeys(): array
{
    return [
        'dashboard',
        // Poultry Operations
        'flocks', 'production', 'vaccinations', 'batches', 'health', 'broiler', 'hatchery', 'feeding', 'losses',
        // Inventory & Stores
        'products', 'stores', 'feed_production', 'egg_grading',
        // Sales & Finance
        'hub_finance', 'profit', 'cashbook', 'credit', 'purchase_orders', 'daily_sales', 'bulk_sales', 'lpo',
        // Reports & Tools
        'analytics', 'bulk_import_export',
        // Team & Messages
        'staff', 'users', 'tasks', 'messages',
        // Settings
        'calendar', 'dropdowns', 'settings', 'logs', 'permissions',
    ];
}

/**
 * Map an admin script name to its module key ('' when unknown).
 */
function busiaModuleKeyForScript(string $script): string
{
    $map = [
        'dashboard.php' => 'dashboard',
        'hub_operations.php' => 'flocks', 'flocks.php' => 'flocks', 'flocks_tab.php' => 'flocks',
        'production.php' => 'production', 'vaccinations.php' => 'vaccinations',
        'batches.php' => 'batches', 'health.php' => 'health', 'broiler.php' => 'broiler',
        'hatchery.php' => 'hatchery', 'feeding.php' => 'feeding', 'extras.php' => 'losses',
        'hub_inventory.php' => 'products', 'products.php' => 'products',
        'stores.php' => 'stores', 'feed_production.php' => 'feed_production',
        'egg_grading.php' => 'egg_grading',
        'hub_finance.php' => 'hub_finance', 'profit.php' => 'profit', 'cashbook.php' => 'cashbook',
        'credit.php' => 'credit', 'purchase_orders.php' => 'purchase_orders',
        'daily_sales.php' => 'daily_sales', 'bulk_sales.php' => 'bulk_sales', 'lpo.php' => 'lpo',
        'analytics.php' => 'analytics', 'bulk_import_export.php' => 'bulk_import_export',
        'hub_people.php' => 'staff', 'staff.php' => 'staff', 'users.php' => 'users',
        'tasks.php' => 'tasks', 'messages.php' => 'messages',
        'hub_settings.php' => 'settings', 'calendar.php' => 'calendar', 'dropdowns.php' => 'dropdowns',
        'settings.php' => 'settings', 'logs.php' => 'logs', 'permissions.php' => 'permissions',
        'orders.php' => 'hub_finance', 'sales.php' => 'hub_finance', 'payments.php' => 'hub_finance',
        'expenses.php' => 'hub_finance', 'reports.php' => 'analytics', 'operations.php' => 'flocks',
    ];
    return $map[$script] ?? '';
}

/**
 * Default permission grants per role. super_admin and farm_manager get full
 * access; limited roles get their own module sets. 'customer' gets nothing.
 */
function busiaDefaultRolePermissions(): array
{
    $all = busiaModuleKeys();
    $perms = [];
    foreach ($all as $m) {
        $perms['super_admin'][$m] = ['view' => 1, 'edit' => 1];
        $perms['farm_manager'][$m] = ['view' => 1, 'edit' => 1];
    }

    $stock = ['products', 'stores', 'feed_production', 'egg_grading', 'batches', 'losses'];
    $sales = ['hub_finance', 'profit', 'cashbook', 'credit', 'daily_sales', 'bulk_sales', 'lpo', 'purchase_orders'];
    foreach ($all as $m) {
        $perms['stock_manager'][$m] = in_array($m, $stock, true) ? ['view' => 1, 'edit' => 1] : ['view' => 0, 'edit' => 0];
        $perms['sales_staff'][$m] = in_array($m, $sales, true) ? ['view' => 1, 'edit' => 1] : ['view' => 0, 'edit' => 0];
    }
    // Everyone can always open the dashboard.
    $perms['stock_manager']['dashboard'] = ['view' => 1, 'edit' => 1];
    $perms['sales_staff']['dashboard'] = ['view' => 1, 'edit' => 1];
    foreach ($all as $m) {
        $perms['customer'][$m] = ['view' => 0, 'edit' => 0];
    }
    return $perms;
}

/**
 * Load the role_permissions matrix for every role.
 * Returns ['role' => ['module' => ['view'=>bool,'edit'=>bool]]]
 */
function busiaRolePermissions(?PDO $pdo = null): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    if ($pdo === null) $pdo = getDatabaseConnection();
    $cache = busiaDefaultRolePermissions();
    if (!$pdo) return $cache;
    try {
        if (!tableExists($pdo, 'role_permissions')) return $cache;
        $rows = $pdo->query('SELECT role, module_key, can_view, can_edit FROM role_permissions')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            if (!isset($cache[$r['role']])) continue;
            if (!isset($cache[$r['role']][$r['module_key']])) continue;
            $cache[$r['role']][$r['module_key']] = ['view' => (int)$r['can_view'], 'edit' => (int)$r['can_edit']];
        }
    } catch (Exception $e) {
        // Table missing — fall back to defaults
    }
    return $cache;
}

/**
 * Idempotent master-data seeding: egg grades (incl. Small/Medium/Large),
 * chicken sizes, user-role dropdown entries and the role_permissions matrix.
 * Runs on every connection but is a handful of INSERT IGNOREs — cheap.
 */
function seedMasterData(PDO $pdo): void
{
    // ── Egg grades: add the standard market sizes alongside B14/B15/Cracked ──
    if (tableExists($pdo, 'egg_grades')) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO egg_grades (grade_code, grade_name, weight_min_grams, weight_max_grams, pieces_per_crate, description, is_active) VALUES (?,?,?,?,?,?,1)');
        $grades = [
            ['PW', 'Peewee', 1, 41, 30, 'Very small eggs (< 42g)'],
            ['S',  'Small', 42, 49, 30, 'Small sized eggs'],
            ['M',  'Medium', 50, 55, 30, 'Medium sized eggs'],
            ['L',  'Large', 56, 64, 30, 'Large sized eggs'],
            ['J',  'Jumbo', 71, 999, 30, 'Jumbo sized eggs (> 70g)'],
        ];
        foreach ($grades as $g) {
            $stmt->execute($g);
        }
    }

    // ── Chicken sizes dropdown group (for product / sale size options) ──
    if (tableExists($pdo, 'system_dropdowns')) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO system_dropdowns (group_key, group_label, option_value, option_label, sort_order, is_active, is_system) VALUES (?,?,?,?,?,1,1)');
        $sizes = [
            ['chicken_sizes', 'Chicken Sizes', 'peewee', 'Peewee', 1],
            ['chicken_sizes', 'Chicken Sizes', 'small', 'Small', 2],
            ['chicken_sizes', 'Chicken Sizes', 'medium', 'Medium', 3],
            ['chicken_sizes', 'Chicken Sizes', 'large', 'Large', 4],
            ['chicken_sizes', 'Chicken Sizes', 'extra_large', 'Extra Large', 5],
            ['chicken_sizes', 'Chicken Sizes', 'jumbo', 'Jumbo', 6],
            // User roles used by the code (older seed only had 'admin')
            ['user_roles', 'User Roles', 'super_admin', 'Super Admin', 0],
            ['user_roles', 'User Roles', 'farm_manager', 'Farm Manager', 1],
            ['user_roles', 'User Roles', 'sales_staff', 'Sales Staff', 4],
        ];
        foreach ($sizes as $s) {
            $stmt->execute($s);
        }
    }

    // ── Role permissions matrix (idempotent) ──
    if (tableExists($pdo, 'role_permissions')) {
        $defaults = busiaDefaultRolePermissions();
        $stmt = $pdo->prepare('INSERT IGNORE INTO role_permissions (role, module_key, can_view, can_edit) VALUES (?,?,?,?)');
        foreach ($defaults as $role => $mods) {
            foreach ($mods as $mod => $p) {
                $stmt->execute([$role, $mod, $p['view'], $p['edit']]);
            }
        }
    }
}

/**
 * Ensure all module tables exist. No-op when everything is present.
 */
function ensureBusiaSchema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        // Always reconcile legacy column shapes first (idempotent, cheap) so
        // existing databases get the columns the current modules read even
        // when every table already exists.
        reconcileLegacySchema($pdo);
        seedMasterData($pdo);

        $configDir = __DIR__;
        $poultryFile = $configDir . '/migration_poultry_complete.sql';
        $businessFile = $configDir . '/migration_v2_business.sql';

        // Loop until stable: a statement can fail mid-run when its foreign
        // key target is created later in the same pass (e.g. batches depends
        // on houses/flocks). Later passes create those, then the dependents.
        for ($pass = 0; $pass < 6; $pass++) {
            $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $missingPoultry = array_diff(migrationTableNames($poultryFile), $existing);
            $missingBusiness = array_diff(migrationTableNames($businessFile), $existing);

            if (!$missingPoultry && !$missingBusiness) {
                return; // everything present
            }

            $tableCountBefore = count($existing);

            // Order matters: the business tables FK-reference poultry tables
            // (batches, raw_materials, suppliers, walk_in_customers).
            if ($missingPoultry) {
                runMigrationFile($pdo, $poultryFile);
            }
            if ($missingBusiness) {
                runMigrationFile($pdo, $businessFile);
            }

            $after = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            if (count($after) <= $tableCountBefore) {
                return; // no progress — give up quietly, retried next request
            }
        }
    } catch (Exception $e) {
        // Silent — never break the page
    }
}
