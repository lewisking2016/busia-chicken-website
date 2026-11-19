<?php
/**
 * Admin — Assigned tasks.
 *
 * Card-based board with status filters, assignee filters and inline
 * status updates, so "who is doing what by when" is readable at a
 * glance instead of a dense table row.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Tasks - Admin';
include __DIR__ . '/includes/admin_header.php';

require_once __DIR__ . '/../../Backend/config/permissions.php';

$pdo = getDB();
$action = (string)($_GET['action'] ?? 'list');
$message = '';
$error_message = '';
$current_admin_id = (int)($_SESSION['user_id'] ?? 0);
$statuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
$canEditTasks = ($_SESSION['role'] ?? '') === 'super_admin' || busiaCan('tasks', 'edit') || busiaCan('tasks', 'manage');
$canDeleteTasks = ($_SESSION['role'] ?? '') === 'super_admin' || busiaCan('tasks', 'delete') || busiaCan('tasks', 'manage');

/* ── Actions ───────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    /* Create / update */
    if (isset($_POST['save_task'])) {
        if (!$canEditTasks) {
            $error_message = 'You do not have permission to change tasks.';
        } else {
            $taskId = (int)($_POST['task_id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $assignedTo = (int)($_POST['assigned_to'] ?? 0) ?: null;
            $dueDate = trim((string)($_POST['due_date'] ?? ''));
            $status = (string)($_POST['status'] ?? 'Pending');

            if ($title === '') {
                $error_message = 'Give the task a title.';
            } elseif (!in_array($status, $statuses, true)) {
                $error_message = 'Unknown task status.';
            } else {
                try {
                    if ($taskId > 0) {
                        $pdo->prepare('UPDATE tasks SET title=?, description=?, assigned_to=?, due_date=?, status=? WHERE id=?')
                            ->execute([$title, $description, $assignedTo, $dueDate ?: null, $status, $taskId]);
                        $message = 'Task updated.';
                    } else {
                        $pdo->prepare('INSERT INTO tasks (title, description, assigned_to, due_date, status, created_by) VALUES (?,?,?,?,?,?)')
                            ->execute([$title, $description, $assignedTo, $dueDate ?: null, $status, $current_admin_id]);
                        $message = 'Task created and assigned.';
                    }
                } catch (Throwable $e) {
                    $error_message = 'Could not save the task: ' . $e->getMessage();
                }
            }
        }
    }

    /* Quick status change from the board */
    if (isset($_POST['quick_status'])) {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if (!$canEditTasks) {
            $error_message = 'You do not have permission to change tasks.';
        } elseif (in_array($status, $statuses, true)) {
            try {
                $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?')->execute([$status, $taskId]);
                $message = 'Task moved to ' . $status . '.';
            } catch (Throwable $e) {
                $error_message = 'Could not update the status.';
            }
        }
    }

    /* Delete */
    if (isset($_POST['delete_task'])) {
        $taskId = (int)($_POST['task_id'] ?? 0);
        if (!$canDeleteTasks) {
            $error_message = 'You do not have permission to delete tasks.';
        } else {
            try {
                $pdo->prepare('DELETE FROM tasks WHERE id = ?')->execute([$taskId]);
                $message = 'Task deleted.';
            } catch (Throwable $e) {
                $error_message = 'Could not delete the task.';
            }
        }
    }
}

/* ── Data ──────────────────────────────────────────────────────── */
$staffUsers = [];
$tasks = [];
$selectedTask = null;

if ($pdo) {
    try {
        $staffUsers = $pdo->query("SELECT id, username, first_name, last_name FROM users WHERE role IN ('super_admin','farm_manager','stock_manager','sales_staff') ORDER BY first_name, last_name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
    }

    try {
        $sql = 'SELECT t.*, CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) AS assigned_name,
                       u.username AS assigned_username,
                       CONCAT(COALESCE(c.first_name, ""), " ", COALESCE(c.last_name, "")) AS creator_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN users c ON t.created_by = c.id';
        $stmt = $pdo->query($sql . ' ORDER BY COALESCE(t.due_date, t.created_at) ASC, t.created_at DESC LIMIT 300');
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $error_message = 'Could not load tasks: ' . $e->getMessage();
    }
}

if ($action === 'edit' && $pdo) {
    $taskId = (int)($_GET['id'] ?? 0);
    if ($taskId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$taskId]);
        $selectedTask = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

/* Board filters */
$statusFilter = (string)($_GET['status'] ?? '');
$assigneeFilter = (int)($_GET['assignee'] ?? 0);
$mineOnly = !empty($_GET['mine']);
$q = trim((string)($_GET['q'] ?? ''));

$filtered = array_values(array_filter($tasks, static function ($t) use ($statusFilter, $assigneeFilter, $mineOnly, $q, $current_admin_id) {
    if ($statusFilter !== '' && $t['status'] !== $statusFilter) return false;
    if ($assigneeFilter > 0 && (int)$t['assigned_to'] !== $assigneeFilter) return false;
    if ($mineOnly && (int)$t['assigned_to'] !== $current_admin_id) return false;
    if ($q !== '' && stripos((string)$t['title'] . ' ' . (string)$t['description'], $q) === false) return false;
    return true;
}));

$today = date('Y-m-d');
$counts = ['open' => 0, 'today' => 0, 'overdue' => 0, 'done' => 0];
foreach ($tasks as $t) {
    if (in_array($t['status'], ['Completed', 'Cancelled'], true)) {
        if ($t['status'] === 'Completed') $counts['done']++;
        continue;
    }
    $counts['open']++;
    if (!empty($t['due_date'])) {
        if ($t['due_date'] < $today) $counts['overdue']++;
        elseif ($t['due_date'] === $today) $counts['today']++;
    }
}

$statusStyles = [
    'Pending'     => ['#fff7ed', '#c2410c', 'clock'],
    'In Progress' => ['#eff6ff', '#1d4ed8', 'loader'],
    'Completed'   => ['#ecfdf5', '#15803d', 'check-circle-2'],
    'Cancelled'   => ['#f8fafc', '#64748b', 'x-circle'],
];

function taskName(array $row, string $nameKey, string $userKey): string
{
    $name = trim((string)($row[$nameKey] ?? ''));
    if ($name !== '') return $name;
    return $row[$userKey] ?? '';
}
?>

<style>
    .tk-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .tk-hero h2 { margin:0; font-family:'Outfit',sans-serif; font-size:1.55rem; color:var(--admin-text-heading); letter-spacing:-0.3px; }
    .tk-hero p { margin:5px 0 0; font-size:0.9rem; color:#64748b; }
    .tk-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
    .tk-stat { background:#fff; border:1px solid #e7edf3; border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
    .tk-stat .ico { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .tk-stat strong { display:block; font-size:1.35rem; font-family:'Outfit',sans-serif; line-height:1.1; color:#0f172a; }
    .tk-stat span { font-size:0.73rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; }
    .tk-toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
    .tk-chip { padding:8px 13px; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#475569; font-size:0.82rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .tk-chip:hover { border-color:#bbf7d0; color:#15803d; }
    .tk-chip.active { background:linear-gradient(135deg,#1B5E20,#2E7D32); color:#fff; border-color:transparent; }
    .tk-filter { padding:8px 11px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.84rem; background:#fff; }
    .tk-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:14px; }
    .tk-card { background:#fff; border:1px solid #e7edf3; border-radius:14px; padding:15px 16px; display:flex; flex-direction:column; gap:10px; border-top:3px solid #e2e8f0; }
    .tk-card.is-overdue { border-top-color:#dc2626; }
    .tk-card.is-today { border-top-color:#d97706; }
    .tk-card.is-done { border-top-color:#15803d; opacity:0.9; }
    .tk-card h4 { margin:0; font-size:0.97rem; color:#0f172a; line-height:1.3; }
    .tk-card .desc { margin:0; font-size:0.83rem; color:#64748b; line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
    .tk-card .meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:0.75rem; color:#64748b; }
    .tk-pill { padding:3px 9px; border-radius:999px; font-size:0.7rem; font-weight:800; display:inline-flex; align-items:center; gap:4px; }
    .tk-avatars { display:flex; align-items:center; gap:-6px; }
    .tk-av { width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#1B5E20,#43a047); color:#fff; font-size:0.66rem; font-weight:800; display:flex; align-items:center; justify-content:center; border:2px solid #fff; }
    .tk-av.unassigned { background:#e2e8f0; color:#64748b; }
    .tk-actions { display:flex; align-items:center; gap:6px; margin-top:auto; padding-top:6px; border-top:1px solid #f1f5f9; }
    .tk-actions form { margin:0; }
    .tk-status-select { padding:5px 8px; border-radius:7px; border:1px solid #e2e8f0; font-family:inherit; font-size:0.76rem; font-weight:700; background:#fff; cursor:pointer; }
    .tk-icon { background:none; border:1px solid transparent; border-radius:7px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; color:#64748b; cursor:pointer; }
    .tk-icon:hover { background:#f1f5f9; color:var(--admin-primary); }
    .tk-icon.danger:hover { background:#fef2f2; color:#dc2626; }
    .tk-empty { padding:40px; text-align:center; color:#64748b; background:#fff; border:1px dashed #dbe3ec; border-radius:14px; }
</style>

<div class="tk-hero">
    <div>
        <h2>Assigned tasks</h2>
        <p>Give work to a specific person, set a due date, and track it from pending to done.</p>
    </div>
    <?php if ($canEditTasks): ?>
    <a href="?action=add" class="btn btn-primary" style="border-radius:9px;display:inline-flex;align-items:center;gap:8px;padding:10px 16px;">
        <i data-lucide="plus-circle" style="width:17px;height:17px;"></i> New task
    </a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div style="padding:12px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:9px;color:#166534;font-size:0.88rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:16px;height:16px;"></i> <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:12px 16px;background:#fee2e2;border:1px solid #fecaca;border-radius:9px;color:#b91c1c;font-size:0.88rem;margin-bottom:16px;"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

<div class="tk-stats">
    <div class="tk-stat"><div class="ico" style="background:#eff6ff;color:#1d4ed8;"><i data-lucide="list-checks" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$counts['open'] ?></strong><span>Open tasks</span></div></div>
    <div class="tk-stat"><div class="ico" style="background:#fffbeb;color:#b45309;"><i data-lucide="calendar-clock" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$counts['today'] ?></strong><span>Due today</span></div></div>
    <div class="tk-stat"><div class="ico" style="background:#fef2f2;color:#dc2626;"><i data-lucide="alert-triangle" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$counts['overdue'] ?></strong><span>Overdue</span></div></div>
    <div class="tk-stat"><div class="ico" style="background:#ecfdf5;color:#15803d;"><i data-lucide="check-circle-2" style="width:19px;height:19px;"></i></div><div><strong><?= (int)$counts['done'] ?></strong><span>Completed</span></div></div>
</div>

<form method="get" class="tk-toolbar">
    <a href="tasks.php" class="tk-chip <?= $statusFilter === '' && !$mineOnly ? 'active' : '' ?>">All</a>
    <?php foreach ($statuses as $st): ?>
        <a href="tasks.php?status=<?= urlencode($st) ?>" class="tk-chip <?= $statusFilter === $st ? 'active' : '' ?>"><?= htmlspecialchars($st) ?></a>
    <?php endforeach; ?>
    <a href="tasks.php?mine=1" class="tk-chip <?= $mineOnly ? 'active' : '' ?>">Assigned to me</a>

    <span style="flex:1 1 auto;"></span>
    <input type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search tasks…" class="tk-filter" style="min-width:150px;">
    <select name="assignee" class="tk-filter">
        <option value="">Everyone</option>
        <?php foreach ($staffUsers as $staff): ?>
            <?php $nm = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?: $staff['username']; ?>
            <option value="<?= (int)$staff['id'] ?>" <?= $assigneeFilter === (int)$staff['id'] ? 'selected' : '' ?>><?= htmlspecialchars($nm) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline" style="border-radius:8px;padding:8px 14px;">Apply</button>
</form>

<?php if (!$filtered): ?>
    <div class="tk-empty">
        <i data-lucide="clipboard-check" style="width:34px;height:34px;color:#94a3b8;margin-bottom:10px;"></i>
        <p style="margin:0 0 4px;font-weight:800;color:#334155;">No tasks here</p>
        <p style="margin:0;font-size:0.86rem;"><?= ($statusFilter || $assigneeFilter || $mineOnly || $q !== '') ? 'Nothing matches these filters.' : 'Create the first task to get the team moving.' ?></p>
    </div>
<?php else: ?>
<div class="tk-grid">
    <?php foreach ($filtered as $task):
        $isDone = in_array($task['status'], ['Completed', 'Cancelled'], true);
        $cardClass = '';
        $dueLabel = '';
        if (!$isDone && !empty($task['due_date'])) {
            if ($task['due_date'] < $today) { $cardClass = 'is-overdue'; $dueLabel = 'Overdue · ' . date('j M', strtotime($task['due_date'])); }
            elseif ($task['due_date'] === $today) { $cardClass = 'is-today'; $dueLabel = 'Due today'; }
            else { $dueLabel = 'Due ' . date('j M Y', strtotime($task['due_date'])); }
        } elseif (!empty($task['due_date'])) {
            $cardClass = 'is-done';
            $dueLabel = date('j M Y', strtotime($task['due_date']));
        }
        if ($isDone && $task['status'] === 'Completed') $cardClass = 'is-done';
        $st = $statusStyles[$task['status']] ?? $statusStyles['Pending'];
        $assignee = taskName($task, 'assigned_name', 'assigned_username');
        $initials = $assignee !== '' ? strtoupper(substr(trim($assignee), 0, 1)) : '?';
    ?>
    <div class="tk-card <?= $cardClass ?>">
        <div style="display:flex;align-items:flex-start;gap:8px;">
            <h4 style="flex:1 1 auto;"><?= htmlspecialchars((string)$task['title']) ?></h4>
            <span class="tk-pill" style="background:<?= $st[0] ?>;color:<?= $st[1] ?>;">
                <i data-lucide="<?= $st[2] ?>" style="width:11px;height:11px;"></i> <?= htmlspecialchars($task['status']) ?>
            </span>
        </div>
        <?php if (trim((string)$task['description']) !== ''): ?>
            <p class="desc"><?= htmlspecialchars((string)$task['description']) ?></p>
        <?php endif; ?>
        <div class="meta">
            <span class="tk-avatars"><span class="tk-av <?= $assignee === '' ? 'unassigned' : '' ?>" title="<?= htmlspecialchars($assignee ?: 'Unassigned') ?>"><?= htmlspecialchars($initials) ?></span></span>
            <span><?= htmlspecialchars($assignee ?: 'Unassigned') ?></span>
            <?php if ($dueLabel !== ''): ?>
                <span style="color:<?= $cardClass === 'is-overdue' ? '#dc2626' : ($cardClass === 'is-today' ? '#b45309' : '#64748b') ?>;font-weight:700;">· <?= htmlspecialchars($dueLabel) ?></span>
            <?php endif; ?>
        </div>
        <div class="tk-actions">
            <?php if ($canEditTasks): ?>
            <form method="post" style="flex:1 1 auto;">
                <input type="hidden" name="quick_status" value="1">
                <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                <select name="status" class="tk-status-select" onchange="this.form.submit()" title="Change status">
                    <?php foreach ($statuses as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>" <?= $task['status'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a class="tk-icon" href="?action=edit&id=<?= (int)$task['id'] ?>" title="Edit task"><i data-lucide="pencil" style="width:14px;height:14px;"></i></a>
            <?php endif; ?>
            <?php if ($canDeleteTasks): ?>
            <form method="post" onsubmit="return confirm('Delete this task?');" style="margin:0;">
                <input type="hidden" name="delete_task" value="1">
                <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                <button type="submit" class="tk-icon danger" title="Delete task"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
$form = [
    'title' => '', 'description' => '', 'assigned_to' => '', 'due_date' => '', 'status' => 'Pending',
];
if ($selectedTask) {
    $form = [
        'title' => (string)$selectedTask['title'],
        'description' => (string)$selectedTask['description'],
        'assigned_to' => (string)($selectedTask['assigned_to'] ?? ''),
        'due_date' => (string)($selectedTask['due_date'] ?? ''),
        'status' => (string)$selectedTask['status'],
    ];
}
?>
<div class="admin-card" style="max-width:760px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);"><?= $selectedTask ? 'Edit task' : 'New task' ?></h3>
            <p style="margin:4px 0 0;font-size:0.84rem;color:#64748b;">The person you pick sees it in their own task list.</p>
        </div>
        <a href="tasks.php" class="btn btn-outline" style="border-radius:8px;">← Back</a>
    </div>

    <form method="post">
        <input type="hidden" name="save_task" value="1">
        <?php if ($selectedTask): ?><input type="hidden" name="task_id" value="<?= (int)$selectedTask['id'] ?>"><?php endif; ?>

        <div class="admin-form-group">
            <label class="admin-form-label">Task title <span style="color:#dc2626;">*</span></label>
            <input class="admin-form-control" type="text" name="title" value="<?= htmlspecialchars($form['title'], ENT_QUOTES, 'UTF-8') ?>" required placeholder="e.g. Count eggs in House 2">
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">Details</label>
            <textarea class="admin-form-control" name="description" rows="4" placeholder="What needs to be done, and anything the person should know."><?= htmlspecialchars($form['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
            <div class="admin-form-group">
                <label class="admin-form-label">Assign to</label>
                <select class="admin-form-control" name="assigned_to">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($staffUsers as $staff): ?>
                        <?php $nm = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?: $staff['username']; ?>
                        <option value="<?= (int)$staff['id'] ?>" <?= (string)$staff['id'] === (string)$form['assigned_to'] ? 'selected' : '' ?>><?= htmlspecialchars($nm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Due date</label>
                <input class="admin-form-control" type="date" name="due_date" value="<?= htmlspecialchars($form['due_date'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach ($statuses as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>" <?= $form['status'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:8px;display:inline-flex;align-items:center;gap:7px;">
                <i data-lucide="save" style="width:16px;height:16px;"></i> <?= $selectedTask ? 'Save changes' : 'Create task' ?>
            </button>
            <a href="tasks.php" class="btn btn-outline" style="border-radius:8px;">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
