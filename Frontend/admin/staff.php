<?php
/**
 * Admin — Staff accounts & per-person permissions.
 *
 * Every staff member can be given their own access matrix instead of
 * inheriting the whole role. Toggle view / add-edit / delete / manage
 * for each module and the person only sees those modules when they
 * sign in with their own account.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Staff & Permissions - Admin';
include __DIR__ . '/includes/admin_header.php';

require_once __DIR__ . '/../../Backend/config/permissions.php';

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';
$currentAdminId = (int)($_SESSION['user_id'] ?? 0);
$isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
$canEditStaff = $isSuperAdmin || busiaCan('staff', 'edit') || busiaCan('staff', 'manage');

$staffRoles = [
    'super_admin'   => 'Super Admin',
    'farm_manager'  => 'Farm Manager',
    'stock_manager' => 'Stock Manager',
    'sales_staff'   => 'Sales Staff',
];

$moduleGroups = busiaPermissionModules();
$levels = busiaPermissionLevels();

/* ── Save ──────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    if (!$canEditStaff) {
        $error_message = 'You do not have permission to change staff accounts.';
    } else {
        $staffId    = (int)($_POST['staff_id'] ?? 0);
        $username   = trim((string)($_POST['username'] ?? ''));
        $email      = trim((string)($_POST['email'] ?? ''));
        $firstName  = trim((string)($_POST['first_name'] ?? ''));
        $lastName   = trim((string)($_POST['last_name'] ?? ''));
        $phone      = trim((string)($_POST['phone_number'] ?? ''));
        $role       = (string)($_POST['role'] ?? 'farm_manager');
        $password   = (string)($_POST['password'] ?? '');
        $permInput  = is_array($_POST['perm'] ?? null) ? $_POST['perm'] : [];

        if (!isset($staffRoles[$role])) $role = 'farm_manager';

        // Never let a non-super-admin edit a super admin account.
        if ($staffId > 0 && !$isSuperAdmin) {
            $chk = $pdo->prepare('SELECT role FROM users WHERE id = ?');
            $chk->execute([$staffId]);
            if ($chk->fetchColumn() === 'super_admin') {
                $error_message = 'Only a super admin can edit a super admin account.';
            }
        }
        if (!$error_message && $role === 'super_admin' && !$isSuperAdmin) {
            $error_message = 'Only a super admin can create another super admin.';
        }

        if (!$error_message && ($username === '' || $email === '')) {
            $error_message = 'Username and email are required.';
        }
        if (!$error_message && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Enter a valid email address.';
        }

        // Unique username / email.
        if (!$error_message) {
            $dupe = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
            $dupe->execute([$username, $email, $staffId]);
            if ($dupe->fetchColumn()) {
                $error_message = 'That username or email is already used by another account.';
            }
        }

        if (!$error_message) {
            try {
                $pdo->beginTransaction();
                if ($staffId > 0) {
                    $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, first_name=?, last_name=?, phone_number=?, role=? WHERE id=?');
                    $stmt->execute([$username, $email, $firstName, $lastName, $phone, $role, $staffId]);
                    if ($password !== '') {
                        if (strlen($password) < 8) {
                            throw new RuntimeException('The new password must be at least 8 characters.');
                        }
                        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
                            ->execute([password_hash($password, PASSWORD_DEFAULT), $staffId]);
                    }
                    $message = 'Staff account updated.';
                } else {
                    if ($password === '') $password = 'Staff@123';
                    if (strlen($password) < 8) {
                        throw new RuntimeException('The password must be at least 8 characters.');
                    }
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, first_name, last_name, phone_number) VALUES (?,?,?,?,?,?,?)');
                    $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $firstName, $lastName, $phone]);
                    $staffId = (int)$pdo->lastInsertId();
                    $message = 'Staff member added. They can sign in with the password you set.';
                }

                // Explicit matrix wins over the role defaults for this person.
                if ($permInput) {
                    $flat = [];
                    foreach ($moduleGroups as $mods) {
                        foreach ($mods as $moduleKey => $_) {
                            $flat[$moduleKey] = $permInput[$moduleKey] ?? [];
                        }
                    }
                    busiaSaveUserPermissions($pdo, $staffId, $flat);
                } elseif (isset($_POST['reset_perms'])) {
                    $pdo->prepare('DELETE FROM user_permissions WHERE user_id = ?')->execute([$staffId]);
                }

                $pdo->commit();
                if ($staffId === $currentAdminId && function_exists('busiaCurrentPermissions')) {
                    // Refresh this session's own permissions if they just changed them.
                    header('Location: staff.php?action=edit&id=' . $staffId . '&saved=1');
                    exit;
                }
                header('Location: staff.php?saved=1');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error_message = 'Unable to save staff member: ' . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['saved']) && $message === '') {
    $message = 'Staff account saved.';
}

/* ── Delete permission overrides (back to role defaults) ────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_permissions'])) {
    $target = (int)($_POST['staff_id'] ?? 0);
    if (!$canEditStaff || $target <= 0) {
        $error_message = 'You do not have permission to do that.';
    } else {
        try {
            $pdo->prepare('DELETE FROM user_permissions WHERE user_id = ?')->execute([$target]);
            $message = 'Permissions reset to this role’s defaults.';
        } catch (Throwable $e) {
            $error_message = 'Could not reset permissions: ' . $e->getMessage();
        }
    }
}

/* ── Data ──────────────────────────────────────────────────────── */
$search = trim((string)($_GET['q'] ?? ''));
$users = [];
$counts = ['total' => 0];
if ($pdo) {
    try {
        $sql = 'SELECT * FROM users WHERE role IN ("super_admin","farm_manager","stock_manager","sales_staff")';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (username LIKE ? OR email LIKE ? OR CONCAT(COALESCE(first_name,"")," ",COALESCE(last_name,"")) LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }
        $sql .= ' ORDER BY FIELD(role,"super_admin","farm_manager","stock_manager","sales_staff"), COALESCE(first_name, username)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts['total'] = count($users);
    } catch (Throwable $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

$selectedUser = null;
$selectedPerms = [];
$hasOverrides = false;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $userId = (int)($_GET['id'] ?? 0);
    if ($userId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($selectedUser) {
            $selectedPerms = busiaEffectivePermissions($pdo, (int)$selectedUser['id'], (string)$selectedUser['role']);
            $hasOverrides = !empty(busiaUserOverrides($pdo, (int)$selectedUser['id']));
        }
    }
}

// Module counts per staff member for the list summary.
$overrideCounts = [];
if ($pdo && $users) {
    try {
        $rows = $pdo->query('SELECT user_id, SUM(can_view) AS v, COUNT(*) AS t FROM user_permissions GROUP BY user_id')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $overrideCounts[(int)$row['user_id']] = ['view' => (int)$row['v'], 'total' => (int)$row['t']];
        }
    } catch (Throwable $e) {
    }
}

$roleMatrix = busiaRoleMatrix($pdo);
$totalModules = count(busiaPermissionModuleKeys());

function staffInitials(array $u): string
{
    $f = trim((string)($u['first_name'] ?? ''));
    $l = trim((string)($u['last_name'] ?? ''));
    if ($f !== '' || $l !== '') {
        return strtoupper(substr($f, 0, 1) . substr($l, 0, 1));
    }
    return strtoupper(substr((string)($u['username'] ?? 'S'), 0, 2));
}

$roleColors = [
    'super_admin'   => ['#eef2ff', '#4338ca'],
    'farm_manager'  => ['#ecfdf5', '#15803d'],
    'stock_manager' => ['#fff7ed', '#c2410c'],
    'sales_staff'   => ['#eff6ff', '#1d4ed8'],
];
?>

<style>
    .staff-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:22px; }
    .staff-hero h2 { margin:0; font-family:'Outfit',sans-serif; font-size:1.55rem; color:var(--admin-text-heading); letter-spacing:-0.3px; }
    .staff-hero p { margin:5px 0 0; font-size:0.9rem; color:#64748b; max-width:640px; }
    .staff-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:22px; }
    .staff-stat { background:#fff; border:1px solid #e7edf3; border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
    .staff-stat .ico { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#f0fdf4; color:#15803d; flex-shrink:0; }
    .staff-stat strong { display:block; font-size:1.3rem; line-height:1.1; color:#0f172a; font-family:'Outfit',sans-serif; }
    .staff-stat span { font-size:0.76rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; }
    .staff-search { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .staff-search input { flex:1 1 240px; padding:10px 14px; border:1px solid #e2e8f0; border-radius:9px; font-family:inherit; font-size:0.9rem; }
    .staff-search input:focus { outline:none; border-color:var(--admin-primary); box-shadow:0 0 0 3px rgba(27,94,32,0.10); }
    .staff-row { display:flex; align-items:center; gap:14px; padding:14px 4px; border-bottom:1px solid #eef2f6; }
    .staff-row:last-child { border-bottom:none; }
    .staff-avatar { width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#1B5E20,#43a047); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem; flex-shrink:0; }
    .staff-name { font-weight:700; color:#0f172a; font-size:0.94rem; }
    .staff-meta { font-size:0.78rem; color:#64748b; margin-top:2px; }
    .role-pill { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:0.72rem; font-weight:700; }
    .perm-pill { font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; background:#f1f5f9; color:#475569; }
    .perm-pill.full { background:#ecfdf5; color:#15803d; }
    .perm-matrix { border:1px solid #e7edf3; border-radius:12px; overflow:hidden; background:#fff; }
    .perm-group-head { background:#f8fafc; padding:9px 14px; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:#475569; border-top:1px solid #eef2f6; display:flex; justify-content:space-between; align-items:center; }
    .perm-group-head:first-child { border-top:none; }
    .perm-row { display:flex; align-items:center; gap:10px; padding:9px 14px; border-top:1px solid #f1f5f9; font-size:0.86rem; }
    .perm-row:hover { background:#fbfdfc; }
    .perm-row .label { flex:1 1 auto; color:#1e293b; font-weight:600; }
    .perm-cell { display:flex; flex-direction:column; align-items:center; gap:3px; width:64px; font-size:0.64rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.03em; }
    .perm-cell input { width:17px; height:17px; accent-color:var(--admin-primary); cursor:pointer; }
    .perm-legend { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
    .perm-toolbar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
    .perm-toolbar button { font-size:0.76rem; font-weight:700; padding:6px 11px; border-radius:8px; border:1px solid #dbe3ec; background:#fff; color:#334155; cursor:pointer; }
    .perm-toolbar button:hover { border-color:#bbf7d0; background:#f0fdf4; color:#15803d; }
    .staff-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.15fr); gap:20px; align-items:start; }
    @media (max-width: 1080px) { .staff-grid { grid-template-columns:1fr; } .perm-cell { width:52px; } }
</style>

<div class="staff-hero">
    <div>
        <h2>Staff &amp; Permissions</h2>
        <p>Create staff accounts and decide exactly which modules each person can see — view, add/edit, delete or fully manage. The same rules apply the moment they sign in.</p>
    </div>
    <?php if ($canEditStaff): ?>
    <a href="?action=add" class="btn btn-primary" style="border-radius:9px;display:inline-flex;align-items:center;gap:8px;padding:10px 16px;">
        <i data-lucide="user-plus" style="width:17px;height:17px;"></i> Add Staff Member
    </a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div style="padding:13px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:9px;color:#166534;margin-bottom:18px;font-size:0.9rem;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:13px 16px;background:#fee2e2;border:1px solid #fecaca;border-radius:9px;color:#b91c1c;margin-bottom:18px;font-size:0.9rem;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if (!$canEditStaff): ?>
<div style="padding:13px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;color:#92400e;margin-bottom:18px;font-size:0.9rem;">
    You can view the team, but only a staff manager can add people or change permissions.
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

<?php
$byRole = ['super_admin' => 0, 'farm_manager' => 0, 'stock_manager' => 0, 'sales_staff' => 0];
foreach ($users as $u) { $byRole[$u['role']] = ($byRole[$u['role']] ?? 0) + 1; }
?>
<div class="staff-stats">
    <div class="staff-stat"><div class="ico"><i data-lucide="users" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$counts['total'] ?></strong><span>Staff accounts</span></div></div>
    <div class="staff-stat"><div class="ico"><i data-lucide="shield-check" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$byRole['super_admin'] ?></strong><span>Super admins</span></div></div>
    <div class="staff-stat"><div class="ico"><i data-lucide="bird" style="width:19px;height:19px;"></i></div><div><strong><?= (int)($byRole['farm_manager'] + $byRole['stock_manager']) ?></strong><span>Farm / stock</span></div></div>
    <div class="staff-stat"><div class="ico"><i data-lucide="receipt" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$byRole['sales_staff'] ?></strong><span>Sales staff</span></div></div>
</div>

<form method="get" class="staff-search">
    <input type="search" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search staff by name, username or email…">
    <button class="btn btn-outline" type="submit" style="padding:0 16px;border-radius:9px;">Search</button>
    <?php if ($search !== ''): ?><a class="btn btn-outline" href="staff.php" style="padding:0 16px;border-radius:9px;">Clear</a><?php endif; ?>
</form>

<div class="admin-card">
    <?php if (!$users): ?>
        <div style="padding:26px;text-align:center;color:#64748b;">
            <i data-lucide="users-round" style="width:34px;height:34px;color:#94a3b8;margin-bottom:8px;"></i>
            <p style="margin:0 0 6px;font-weight:700;color:#334155;">No staff found</p>
            <p style="margin:0;font-size:0.86rem;"><?= $search !== '' ? 'Nothing matched that search.' : 'Add your first team member to get started.' ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($users as $u):
            $uid = (int)$u['id'];
            $roleKey = (string)$u['role'];
            $colors = $roleColors[$roleKey] ?? ['#f1f5f9', '#475569'];
            $ov = $overrideCounts[$uid] ?? null;
            if ($ov) {
                $summary = $ov['view'] . ' of ' . $totalModules . ' modules';
                $isFull = $ov['view'] >= $totalModules;
            } else {
                $roleView = 0;
                foreach (($roleMatrix[$roleKey] ?? []) as $lvl) { if (!empty($lvl['view'])) $roleView++; }
                $summary = 'Role defaults · ' . $roleView . ' modules';
                $isFull = $roleView >= $totalModules;
            }
            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        ?>
        <div class="staff-row">
            <div class="staff-avatar" style="<?= $uid === $currentAdminId ? 'box-shadow:0 0 0 3px #fde68a;' : '' ?>"><?= htmlspecialchars(staffInitials($u)) ?></div>
            <div style="flex:1 1 auto;min-width:0;">
                <div class="staff-name"><?= htmlspecialchars($fullName !== '' ? $fullName : $u['username']) ?>
                    <?php if ($uid === $currentAdminId): ?><span style="font-size:0.7rem;color:#a16207;font-weight:700;">(you)</span><?php endif; ?>
                </div>
                <div class="staff-meta">@<?= htmlspecialchars((string)$u['username']) ?> · <?= htmlspecialchars((string)$u['email']) ?><?= !empty($u['phone_number']) ? ' · ' . htmlspecialchars((string)$u['phone_number']) : '' ?></div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                <span class="role-pill" style="background:<?= $colors[0] ?>;color:<?= $colors[1] ?>;"><?= htmlspecialchars($staffRoles[$roleKey] ?? ucfirst($roleKey)) ?></span>
                <span class="perm-pill <?= $isFull ? 'full' : '' ?>" title="<?= $ov ? 'Custom permissions for this person' : 'Inherited from the role' ?>"><?= htmlspecialchars($summary) ?></span>
                <a href="?action=edit&id=<?= $uid ?>" class="btn btn-outline" style="padding:6px 12px;border-radius:8px;font-size:0.8rem;display:inline-flex;align-items:center;gap:6px;">
                    <i data-lucide="<?= $canEditStaff ? 'pencil' : 'eye' ?>" style="width:14px;height:14px;"></i> <?= $canEditStaff ? 'Edit' : 'View' ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<p style="margin-top:14px;font-size:0.82rem;color:#64748b;">
    Tip: editing a staff member lets you tick the exact modules they may open. Anything left unticked is hidden from their sign-in, including its sidebar link.
</p>

<?php else: ?>

<?php
$editing = (bool)$selectedUser;
$u = $selectedUser ?: [
    'id' => 0, 'username' => '', 'email' => '', 'first_name' => '', 'last_name' => '',
    'phone_number' => '', 'role' => 'farm_manager',
];
$effective = $editing ? $selectedPerms : $roleMatrix['farm_manager'];
$locked = $editing && $u['role'] === 'super_admin' && !$isSuperAdmin;
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
    <div>
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.25rem;color:#0f172a;"><?= $editing ? 'Edit staff member' : 'Add staff member' ?></h3>
        <p style="margin:4px 0 0;font-size:0.86rem;color:#64748b;">Account details on the left, module access on the right.</p>
    </div>
    <a href="staff.php" class="btn btn-outline" style="border-radius:8px;">← Back to staff list</a>
</div>

<?php if ($locked): ?>
<div style="padding:12px 15px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;color:#92400e;margin-bottom:16px;font-size:0.88rem;">This is a super admin account — only another super admin can change it.</div>
<?php endif; ?>

<form method="post" action="staff.php" id="staff-form" <?= $locked ? 'onsubmit="return false;"' : '' ?>>
    <input type="hidden" name="save_staff" value="1">
    <input type="hidden" name="staff_id" value="<?= (int)$u['id'] ?>">

    <div class="staff-grid">
        <!-- Account -->
        <div class="admin-card">
            <h4 style="margin:0 0 14px;font-family:'Outfit',sans-serif;font-size:1rem;color:#0f172a;">Account details</h4>

            <div class="admin-form-group">
                <label class="admin-form-label">Role</label>
                <select class="admin-form-control" name="role" id="staff-role">
                    <?php foreach ($staffRoles as $rk => $rl): ?>
                        <option value="<?= $rk ?>" <?= $u['role'] === $rk ? 'selected' : '' ?>><?= htmlspecialchars($rl) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#64748b;font-size:0.76rem;display:block;margin-top:5px;">The role sets the default module access. Your ticks below override it for this person.</small>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">First name</label>
                    <input class="admin-form-control" type="text" name="first_name" value="<?= htmlspecialchars((string)$u['first_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Last name</label>
                    <input class="admin-form-control" type="text" name="last_name" value="<?= htmlspecialchars((string)$u['last_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Username <span style="color:#dc2626;">*</span></label>
                <input class="admin-form-control" type="text" name="username" value="<?= htmlspecialchars((string)$u['username'], ENT_QUOTES, 'UTF-8') ?>" required autocomplete="off">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Email <span style="color:#dc2626;">*</span></label>
                <input class="admin-form-control" type="email" name="email" value="<?= htmlspecialchars((string)$u['email'], ENT_QUOTES, 'UTF-8') ?>" required autocomplete="off">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Phone</label>
                <input class="admin-form-control" type="text" name="phone_number" value="<?= htmlspecialchars((string)$u['phone_number'], ENT_QUOTES, 'UTF-8') ?>" placeholder="07xx xxx xxx">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label"><?= $editing ? 'New password' : 'Password' ?></label>
                <input class="admin-form-control" type="password" name="password" autocomplete="new-password" placeholder="<?= $editing ? 'Leave blank to keep the current password' : 'Defaults to Staff@123' ?>">
                <small style="color:#64748b;font-size:0.76rem;display:block;margin-top:5px;">At least 8 characters. Share it with the staff member privately.</small>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
                <button type="submit" class="btn btn-primary" style="border-radius:8px;display:inline-flex;align-items:center;gap:7px;">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> <?= $editing ? 'Save changes' : 'Create staff member' ?>
                </button>
                <a href="staff.php" class="btn btn-outline" style="border-radius:8px;">Cancel</a>
            </div>
        </div>

        <!-- Permissions -->
        <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <div>
                    <h4 style="margin:0;font-family:'Outfit',sans-serif;font-size:1rem;color:#0f172a;">Module access</h4>
                    <p style="margin:4px 0 0;font-size:0.8rem;color:#64748b;">Tick what this person may do. Unticked modules disappear from their sidebar.</p>
                </div>
                <span class="perm-pill <?= $hasOverrides ? '' : 'full' ?>" id="perm-source"><?= $editing ? ($hasOverrides ? 'Custom for this person' : 'Role defaults') : 'Preview from role' ?></span>
            </div>

            <div class="perm-toolbar">
                <button type="button" data-perm-preset="all">Grant everything</button>
                <button type="button" data-perm-preset="view">View only</button>
                <button type="button" data-perm-preset="none">Clear all</button>
                <?php if ($editing): ?>
                <button type="button" data-perm-preset="role">Use role defaults</button>
                <?php endif; ?>
            </div>

            <div class="perm-matrix" style="max-height:620px;overflow:auto;">
                <?php foreach ($moduleGroups as $groupLabel => $mods): ?>
                    <div class="perm-group-head">
                        <span><?= htmlspecialchars($groupLabel) ?></span>
                        <button type="button" data-perm-group="<?= htmlspecialchars(md5($groupLabel)) ?>" style="background:none;border:none;color:#15803d;font-size:0.72rem;font-weight:800;cursor:pointer;">toggle all</button>
                    </div>
                    <div data-perm-group-body="<?= htmlspecialchars(md5($groupLabel)) ?>">
                    <?php foreach ($mods as $moduleKey => $moduleLabel):
                        $lvl = $effective[$moduleKey] ?? ['view' => 0, 'edit' => 0, 'delete' => 0, 'manage' => 0];
                        $roleLvl = $roleMatrix[$u['role']][$moduleKey] ?? ['view' => 0, 'edit' => 0, 'delete' => 0, 'manage' => 0];
                    ?>
                        <div class="perm-row" data-module="<?= htmlspecialchars($moduleKey) ?>"
                             data-role-view="<?= (int)($roleLvl['view'] ?? 0) ?>"
                             data-role-edit="<?= (int)($roleLvl['edit'] ?? 0) ?>"
                             data-role-delete="<?= (int)($roleLvl['delete'] ?? 0) ?>"
                             data-role-manage="<?= (int)($roleLvl['manage'] ?? 0) ?>">
                            <span class="label"><?= htmlspecialchars($moduleLabel) ?></span>
                            <?php foreach ($levels as $lvlKey => $lvlLabel): ?>
                                <label class="perm-cell" title="<?= htmlspecialchars($lvlLabel . ' · ' . $moduleLabel) ?>">
                                    <input type="checkbox"
                                           name="perm[<?= htmlspecialchars($moduleKey) ?>][<?= $lvlKey ?>]"
                                           value="1"
                                           data-level="<?= $lvlKey ?>"
                                           <?= !empty($lvl[$lvlKey]) ? 'checked' : '' ?>
                                           <?= $locked ? 'disabled' : '' ?>>
                                    <span><?= htmlspecialchars($lvlLabel) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($editing): ?>
            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <small style="font-size:0.76rem;color:#64748b;">Saving stores this person’s own matrix.</small>
                <button type="submit" form="reset-perm-form" class="btn btn-outline" style="border-radius:8px;font-size:0.8rem;"
                        onclick="return confirm('Remove this person’s custom permissions and go back to their role defaults?');">
                    Reset to role defaults
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($editing): ?>
<form method="post" action="staff.php?action=edit&id=<?= (int)$u['id'] ?>" id="reset-perm-form" style="display:none;">
    <input type="hidden" name="reset_permissions" value="1">
    <input type="hidden" name="staff_id" value="<?= (int)$u['id'] ?>">
</form>
<?php endif; ?>

<script>
(function () {
    var form = document.getElementById('staff-form');
    if (!form) return;

    function rowBoxes(row) {
        return Array.prototype.slice.call(row.querySelectorAll('input[type=checkbox]'));
    }

    function syncRow(row, changed) {
        var boxes = rowBoxes(row);
        var view = boxes.find(function (b) { return b.dataset.level === 'view'; });
        var stronger = boxes.filter(function (b) { return b.dataset.level !== 'view' && b.checked; });
        if (stronger.length && view) view.checked = true;
        if (view && !view.checked) boxes.forEach(function (b) { if (b.dataset.level !== 'view') b.checked = false; });
    }

    form.addEventListener('change', function (e) {
        var box = e.target;
        if (!box.matches('input[type=checkbox][data-level]')) return;
        var row = box.closest('.perm-row');
        if (row) syncRow(row, box);
        updateSource();
    });

    document.querySelectorAll('[data-perm-group]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.dataset.permGroup;
            var body = document.querySelector('[data-perm-group-body="' + key + '"]');
            if (!body) return;
            var boxes = Array.prototype.slice.call(body.querySelectorAll('input[type=checkbox]'));
            var allOn = boxes.length && boxes.every(function (b) { return b.checked; });
            boxes.forEach(function (b) { b.checked = !allOn; });
            body.querySelectorAll('.perm-row').forEach(function (r) { syncRow(r); });
            updateSource();
        });
    });

    function setAll(fn) {
        form.querySelectorAll('.perm-row').forEach(function (row) {
            var boxes = rowBoxes(row);
            boxes.forEach(function (b) { b.checked = fn(b.dataset.level, row); });
        });
        updateSource();
    }

    document.querySelectorAll('[data-perm-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var preset = btn.dataset.permPreset;
            if (preset === 'all') setAll(function () { return true; });
            if (preset === 'view') setAll(function (lvl) { return lvl === 'view'; });
            if (preset === 'none') setAll(function () { return false; });
            if (preset === 'role') setAll(function (lvl, row) { return row.dataset['role' + lvl.charAt(0).toUpperCase() + lvl.slice(1)] === '1'; });
        });
    });

    function updateSource() {
        var pill = document.getElementById('perm-source');
        if (!pill) return;
        pill.textContent = 'Custom for this person';
        pill.classList.remove('full');
    }

    // Show rough module count next to the matrix heading.
    updateSource();
})();
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
