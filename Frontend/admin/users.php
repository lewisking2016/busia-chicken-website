<?php
/**
 * Admin — Customers & Accounts.
 *
 * A single, readable customer list: search, role filter, stats and
 * per-row actions (change role, reset password, delete). Your own
 * profile lives in a modal so the list is no longer squeezed next to it.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$page_title = 'Customers & Accounts - Admin';
include __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../../Backend/api/dropdowns.php';
require_once __DIR__ . '/../../Backend/config/permissions.php';

$pdo = getDB();
$error_message = '';
$success_message = '';
$current_admin_id = (int)$_SESSION['user_id'];
$isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
$canEditUsers = $isSuperAdmin || busiaCan('users', 'edit') || busiaCan('users', 'manage');
$canDeleteUsers = $isSuperAdmin || busiaCan('users', 'delete') || busiaCan('users', 'manage');

$roleLabels = [
    'super_admin'   => 'Super Admin',
    'farm_manager'  => 'Farm Manager',
    'stock_manager' => 'Stock Manager',
    'sales_staff'   => 'Sales Staff',
    'customer'      => 'Customer',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';

    /* Update my own profile */
    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        if ($username === '' || $email === '') {
            $error_message = 'Username and email are required.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?');
                $stmt->execute([$username, $email, $current_admin_id]);
                if ($stmt->fetch()) {
                    $error_message = 'That username or email belongs to another account.';
                } else {
                    if ($new_password !== '') {
                        $pdo->prepare('UPDATE users SET username=?, email=?, first_name=?, last_name=?, password_hash=? WHERE id=?')
                            ->execute([$username, $email, $first_name, $last_name, password_hash($new_password, PASSWORD_DEFAULT), $current_admin_id]);
                    } else {
                        $pdo->prepare('UPDATE users SET username=?, email=?, first_name=?, last_name=? WHERE id=?')
                            ->execute([$username, $email, $first_name, $last_name, $current_admin_id]);
                    }
                    $_SESSION['username'] = $username;
                    $_SESSION['first_name'] = $first_name;
                    $success_message = 'Your account details were updated.';
                }
            } catch (Throwable $e) {
                $error_message = 'Could not update your profile: ' . $e->getMessage();
            }
        }
    }

    /* Change another account's role */
    if ($action === 'update_role') {
        $target_user_id = (int)($_POST['target_user_id'] ?? 0);
        $new_role = (string)($_POST['role'] ?? 'customer');
        if (!$canEditUsers) {
            $error_message = 'You do not have permission to change roles.';
        } elseif ($target_user_id === $current_admin_id) {
            $error_message = 'You cannot change your own role here.';
        } elseif (!isset($roleLabels[$new_role])) {
            $error_message = 'Unknown role.';
        } elseif (!$isSuperAdmin && in_array($new_role, ['super_admin', 'farm_manager'], true)) {
            $error_message = 'Only a super admin can grant admin-level roles.';
        } else {
            try {
                $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$new_role, $target_user_id]);
                $success_message = 'Role updated to ' . $roleLabels[$new_role] . '.';
            } catch (Throwable $e) {
                $error_message = 'Could not update the role: ' . $e->getMessage();
            }
        }
    }

    /* Delete an account */
    if ($action === 'delete_user') {
        $target_user_id = (int)($_POST['target_user_id'] ?? 0);
        if (!$canDeleteUsers) {
            $error_message = 'You do not have permission to delete accounts.';
        } elseif ($target_user_id === $current_admin_id) {
            $error_message = 'You cannot delete your own account.';
        } else {
            try {
                $row = $pdo->prepare('SELECT role, username FROM users WHERE id = ?');
                $row->execute([$target_user_id]);
                $target = $row->fetch(PDO::FETCH_ASSOC);
                if (!$target) {
                    $error_message = 'That account no longer exists.';
                } elseif ($target['role'] === 'super_admin' && !$isSuperAdmin) {
                    $error_message = 'Only a super admin can delete a super admin.';
                } elseif (($target['role'] ?? '') === 'super_admin' && (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='super_admin'")->fetchColumn() <= 1) {
                    $error_message = 'This is the last super admin — it cannot be deleted.';
                } else {
                    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$target_user_id]);
                    $success_message = 'Account @' . $target['username'] . ' was deleted.';
                }
            } catch (Throwable $e) {
                $error_message = 'Could not delete the account: ' . $e->getMessage();
            }
        }
    }

    /* Reset someone's password */
    if ($action === 'reset_password') {
        $target_user_id = (int)($_POST['target_user_id'] ?? 0);
        $new_pass = (string)($_POST['new_password'] ?? '');
        if (!$canEditUsers) {
            $error_message = 'You do not have permission to reset passwords.';
        } elseif (mb_strlen($new_pass) < 6) {
            $error_message = 'The new password must be at least 6 characters.';
        } else {
            try {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($new_pass, PASSWORD_DEFAULT), $target_user_id]);
                $success_message = 'Password reset. Share the new password privately.';
            } catch (Throwable $e) {
                $error_message = 'Could not reset the password: ' . $e->getMessage();
            }
        }
    }

    /* Create an account */
    if ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role = (string)($_POST['role'] ?? 'customer');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');

        if (!$canEditUsers) {
            $error_message = 'You do not have permission to create accounts.';
        } elseif ($username === '' || $email === '' || $password === '') {
            $error_message = 'Username, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Enter a valid email address.';
        } elseif (mb_strlen($password) < 6) {
            $error_message = 'The password must be at least 6 characters.';
        } elseif (!isset($roleLabels[$role])) {
            $error_message = 'Unknown role.';
        } elseif (!$isSuperAdmin && in_array($role, ['super_admin', 'farm_manager'], true)) {
            $error_message = 'Only a super admin can create admin-level accounts.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error_message = 'That username or email is already registered.';
                } else {
                    $pdo->prepare('INSERT INTO users (username, email, password_hash, role, first_name, last_name) VALUES (?,?,?,?,?,?)')
                        ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $first_name, $last_name]);
                    $success_message = 'Account @' . $username . ' created.';
                }
            } catch (Throwable $e) {
                $error_message = 'Could not create the account: ' . $e->getMessage();
            }
        }
    }
}

/* Data */
$admin_profile = [];
$users_list = [];
$stats = ['customers' => 0, 'staff' => 0, 'new' => 0];

if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$current_admin_id]);
        $admin_profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
    }

    try {
        $stats['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
        $stats['staff'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role <> 'customer'")->fetchColumn();
        $stats['new'] = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    } catch (Throwable $e) {
    }

    try {
        $search = trim((string)($_GET['search'] ?? ''));
        $role_filter = (string)($_GET['role'] ?? '');

        $query = 'SELECT id, username, email, first_name, last_name, role, created_at FROM users WHERE id != ?';
        $params = [$current_admin_id];
        if ($search !== '') {
            $query .= ' AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($role_filter !== '') {
            $query .= ' AND role = ?';
            $params[] = $role_filter;
        }
        $query .= ' ORDER BY created_at DESC LIMIT 300';
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $error_message = 'Could not load accounts: ' . $e->getMessage();
    }
}

$roleColors = [
    'super_admin'   => ['#eef2ff', '#4338ca'],
    'farm_manager'  => ['#ecfdf5', '#15803d'],
    'stock_manager' => ['#fff7ed', '#c2410c'],
    'sales_staff'   => ['#eff6ff', '#1d4ed8'],
    'customer'      => ['#f8fafc', '#475569'],
];

function accountInitials(array $u): string
{
    $f = trim((string)($u['first_name'] ?? ''));
    $l = trim((string)($u['last_name'] ?? ''));
    if ($f !== '' || $l !== '') return strtoupper(substr($f, 0, 1) . substr($l, 0, 1));
    return strtoupper(substr((string)($u['username'] ?? 'U'), 0, 2));
}
?>

<style>
    .acc-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .acc-hero h2 { margin:0; font-family:'Outfit',sans-serif; font-size:1.55rem; color:var(--admin-text-heading); letter-spacing:-0.3px; }
    .acc-hero p { margin:5px 0 0; font-size:0.9rem; color:#64748b; }
    .acc-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
    .acc-stat { background:#fff; border:1px solid #e7edf3; border-radius:12px; padding:14px 16px; }
    .acc-stat strong { display:block; font-size:1.35rem; font-family:'Outfit',sans-serif; color:#0f172a; line-height:1.1; }
    .acc-stat span { font-size:0.74rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; }
    .acc-toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; padding:14px 16px; border-bottom:1px solid #eef2f6; }
    .acc-toolbar input[type=search] { flex:1 1 220px; min-width:0; padding:9px 13px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.88rem; }
    .acc-toolbar input[type=search]:focus { outline:none; border-color:var(--admin-primary); box-shadow:0 0 0 3px rgba(27,94,32,0.1); }
    .acc-toolbar select { padding:9px 11px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.84rem; background:#fff; }
    .acc-row { display:flex; align-items:center; gap:13px; padding:13px 16px; border-bottom:1px solid #f1f5f9; }
    .acc-row:hover { background:#fbfdfc; }
    .acc-row:last-child { border-bottom:none; }
    .acc-avatar { width:40px; height:40px; border-radius:50%; background:#e2e8f0; color:#334155; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.84rem; flex-shrink:0; }
    .acc-name { font-weight:700; color:#0f172a; font-size:0.92rem; }
    .acc-meta { font-size:0.78rem; color:#64748b; margin-top:2px; }
    .acc-role { padding:5px 9px; border-radius:999px; font-size:0.72rem; font-weight:700; border:1px solid transparent; font-family:inherit; cursor:pointer; }
    .acc-actions { display:flex; gap:4px; justify-content:flex-end; }
    .acc-icon-btn { background:none; border:1px solid transparent; border-radius:7px; width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; color:#64748b; transition:all .15s; }
    .acc-icon-btn:hover { background:#f1f5f9; color:var(--admin-primary); }
    .acc-icon-btn.danger:hover { background:#fef2f2; color:#dc2626; }
    .acc-empty { padding:34px; text-align:center; color:#64748b; }
    .acc-modal { position:fixed; inset:0; background:rgba(15,23,42,0.45); z-index:9999; display:none; align-items:center; justify-content:center; padding:16px; }
    .acc-modal.open { display:flex; }
    .acc-modal-card { background:#fff; border-radius:14px; width:100%; max-width:440px; padding:26px; box-shadow:0 24px 48px rgba(15,23,42,0.22); position:relative; max-height:92vh; overflow:auto; }
    .acc-modal-card h3 { margin:0 0 6px; font-family:'Outfit',sans-serif; font-size:1.2rem; color:#0f172a; }
    .acc-close { position:absolute; top:14px; right:14px; background:none; border:none; cursor:pointer; color:#64748b; width:30px; height:30px; border-radius:8px; }
    .acc-close:hover { background:#f1f5f9; }
    @media (max-width:720px) {
        .acc-row { flex-wrap:wrap; }
        .acc-row .acc-actions { width:100%; justify-content:flex-start; }
        .acc-hide-sm { display:none; }
    }
</style>

<div class="acc-hero">
    <div>
        <h2>Customers &amp; Accounts</h2>
        <p>Everyone who can sign in — customers, staff and admins. Search, change roles, reset passwords.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('profile-modal').classList.add('open')" style="border-radius:9px;display:inline-flex;align-items:center;gap:7px;padding:10px 14px;">
            <i data-lucide="user-cog" style="width:16px;height:16px;"></i> My account
        </button>
        <?php if ($canEditUsers): ?>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('add-user-modal').classList.add('open')" style="border-radius:9px;display:inline-flex;align-items:center;gap:7px;padding:10px 14px;">
            <i data-lucide="user-plus" style="width:16px;height:16px;"></i> Add account
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($success_message): ?>
<div style="padding:12px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:9px;color:#166534;font-size:0.88rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:16px;height:16px;"></i> <?= htmlspecialchars($success_message) ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:12px 16px;background:#fee2e2;border:1px solid #fecaca;border-radius:9px;color:#b91c1c;font-size:0.88rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <i data-lucide="alert-circle" style="width:16px;height:16px;"></i> <?= htmlspecialchars($error_message) ?>
</div>
<?php endif; ?>

<div class="acc-stats">
    <div class="acc-stat"><strong><?= (int)$stats['customers'] ?></strong><span>Customers</span></div>
    <div class="acc-stat"><strong><?= (int)$stats['staff'] ?></strong><span>Staff accounts</span></div>
    <div class="acc-stat"><strong><?= (int)$stats['new'] ?></strong><span>Joined last 30 days</span></div>
    <div class="acc-stat"><strong><?= count($users_list) ?></strong><span>Showing now</span></div>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <form method="GET" class="acc-toolbar">
        <input type="search" name="search" value="<?= htmlspecialchars((string)($_GET['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, username or email…">
        <select name="role">
            <?= renderDropdownOptions('user_roles', (string)($_GET['role'] ?? ''), 'All roles') ?>
        </select>
        <button type="submit" class="btn btn-outline" style="border-radius:8px;padding:8px 16px;">Filter</button>
        <?php if (($_GET['search'] ?? '') !== '' || ($_GET['role'] ?? '') !== ''): ?>
            <a href="users.php" class="btn btn-outline" style="border-radius:8px;padding:8px 14px;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (!$users_list): ?>
        <div class="acc-empty">
            <i data-lucide="users" style="width:32px;height:32px;color:#94a3b8;margin-bottom:8px;"></i>
            <p style="margin:0;font-weight:700;color:#334155;">No accounts matched</p>
            <p style="margin:4px 0 0;font-size:0.86rem;">Try a different search or clear the filters.</p>
        </div>
    <?php else: ?>
        <?php foreach ($users_list as $usr):
            $colors = $roleColors[$usr['role']] ?? $roleColors['customer'];
            $fullName = trim(($usr['first_name'] ?? '') . ' ' . ($usr['last_name'] ?? ''));
        ?>
        <div class="acc-row">
            <div class="acc-avatar" style="background:<?= $colors[0] ?>;color:<?= $colors[1] ?>;"><?= htmlspecialchars(accountInitials($usr)) ?></div>
            <div style="flex:1 1 200px;min-width:0;">
                <div class="acc-name"><?= htmlspecialchars($fullName !== '' ? $fullName : $usr['username']) ?></div>
                <div class="acc-meta">@<?= htmlspecialchars((string)$usr['username']) ?> · <?= htmlspecialchars((string)$usr['email']) ?></div>
            </div>
            <div class="acc-hide-sm" style="flex:0 0 auto;font-size:0.76rem;color:#94a3b8;">
                Joined <?= htmlspecialchars(date('j M Y', strtotime((string)$usr['created_at']))) ?>
            </div>
            <div style="flex:0 0 auto;">
                <?php if ($canEditUsers && (int)$usr['id'] !== $current_admin_id): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="target_user_id" value="<?= (int)$usr['id'] ?>">
                    <select name="role" onchange="this.form.submit()" class="acc-role" style="background:<?= $colors[0] ?>;color:<?= $colors[1] ?>;">
                        <?php foreach ($roleLabels as $rk => $rl): ?>
                            <option value="<?= $rk ?>" <?= $usr['role'] === $rk ? 'selected' : '' ?>><?= htmlspecialchars($rl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php else: ?>
                <span class="acc-role" style="background:<?= $colors[0] ?>;color:<?= $colors[1] ?>;"><?= htmlspecialchars($roleLabels[$usr['role']] ?? ucfirst((string)$usr['role'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="acc-actions">
                <?php if ($canEditUsers): ?>
                <button type="button" class="acc-icon-btn" title="Reset password"
                        onclick="openResetModal(<?= (int)$usr['id'] ?>, <?= htmlspecialchars(json_encode((string)$usr['username']), ENT_QUOTES, 'UTF-8') ?>)">
                    <i data-lucide="key-round" style="width:16px;height:16px;"></i>
                </button>
                <?php endif; ?>
                <?php if ($canDeleteUsers && $usr['role'] !== 'super_admin'): ?>
                <form method="POST" onsubmit="return confirm('Delete @<?= htmlspecialchars((string)$usr['username'], ENT_QUOTES, 'UTF-8') ?>? This cannot be undone.');" style="margin:0;">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="target_user_id" value="<?= (int)$usr['id'] ?>">
                    <button type="submit" class="acc-icon-btn danger" title="Delete account">
                        <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- My account -->
<div class="acc-modal" id="profile-modal">
    <div class="acc-modal-card">
        <button type="button" class="acc-close" onclick="document.getElementById('profile-modal').classList.remove('open')">✕</button>
        <h3>My account</h3>
        <p style="margin:0 0 18px;font-size:0.84rem;color:#64748b;">Update your sign-in details and password.</p>
        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="admin-form-group">
                <label class="admin-form-label">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars((string)($admin_profile['username'] ?? '')) ?>" required class="admin-form-control">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars((string)($admin_profile['email'] ?? '')) ?>" required class="admin-form-control">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">First name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars((string)($admin_profile['first_name'] ?? '')) ?>" class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Last name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars((string)($admin_profile['last_name'] ?? '')) ?>" class="admin-form-control">
                </div>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">New password</label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current" class="admin-form-control">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px;">
                <button type="button" class="btn btn-outline" style="border-radius:8px;" onclick="document.getElementById('profile-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="border-radius:8px;">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset password -->
<div class="acc-modal" id="reset-modal">
    <div class="acc-modal-card" style="max-width:400px;">
        <button type="button" class="acc-close" onclick="document.getElementById('reset-modal').classList.remove('open')">✕</button>
        <h3>Reset password</h3>
        <p style="margin:0 0 18px;font-size:0.84rem;color:#64748b;" id="reset-modal-subtitle">Set a new password.</p>
        <form method="POST">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="target_user_id" id="reset-target-id">
            <div class="admin-form-group">
                <label class="admin-form-label">New password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="At least 6 characters" class="admin-form-control">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px;">
                <button type="button" class="btn btn-outline" style="border-radius:8px;" onclick="document.getElementById('reset-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="border-radius:8px;">Reset password</button>
            </div>
        </form>
    </div>
</div>

<?php if ($canEditUsers): ?>
<!-- Add account -->
<div class="acc-modal" id="add-user-modal">
    <div class="acc-modal-card" style="max-width:470px;">
        <button type="button" class="acc-close" onclick="document.getElementById('add-user-modal').classList.remove('open')">✕</button>
        <h3>Add account</h3>
        <p style="margin:0 0 18px;font-size:0.84rem;color:#64748b;">Create a customer login, or a staff login you can fine-tune in Staff &amp; Permissions.</p>
        <form method="POST">
            <input type="hidden" name="action" value="add_user">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">First name</label>
                    <input type="text" name="first_name" class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Last name</label>
                    <input type="text" name="last_name" class="admin-form-control">
                </div>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Username</label>
                <input type="text" name="username" required placeholder="e.g. john_doe" class="admin-form-control">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Email address</label>
                <input type="email" name="email" required placeholder="name@example.com" class="admin-form-control">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Password</label>
                <input type="password" name="password" required minlength="6" class="admin-form-control">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Role</label>
                <select name="role" class="admin-form-control">
                    <option value="customer">Customer</option>
                    <option value="sales_staff">Sales Staff</option>
                    <option value="stock_manager">Stock Manager</option>
                    <?php if ($isSuperAdmin): ?>
                    <option value="farm_manager">Farm Manager</option>
                    <option value="super_admin">Super Admin</option>
                    <?php endif; ?>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px;">
                <button type="button" class="btn btn-outline" style="border-radius:8px;" onclick="document.getElementById('add-user-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="border-radius:8px;">Create account</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openResetModal(userId, username) {
    document.getElementById('reset-target-id').value = userId;
    document.getElementById('reset-modal-subtitle').textContent = 'Set a new password for @' + username;
    document.getElementById('reset-modal').classList.add('open');
}
document.querySelectorAll('.acc-modal').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
