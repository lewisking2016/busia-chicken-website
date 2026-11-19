<?php
/**
 * Admin — Team & People hub.
 *
 * A single overview that links into the real modules. The old version
 * embedded other admin pages in an iframe, which rendered the sidebar
 * and top bar twice; each module is now a first-class page instead.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Team & People - Admin';
include __DIR__ . '/includes/admin_header.php';

require_once __DIR__ . '/../../Backend/config/permissions.php';
require_once __DIR__ . '/../../Backend/config/messages.php';

$pdo = getDB();

$stats = ['staff' => 0, 'customers' => 0, 'open_tasks' => 0, 'overdue' => 0, 'unread' => 0];
$recentTasks = [];
$recentMessages = [];

if ($pdo) {
    try {
        $stats['staff'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('super_admin','farm_manager','stock_manager','sales_staff')")->fetchColumn();
        $stats['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    } catch (Throwable $e) {
    }
    try {
        $stats['open_tasks'] = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('Pending','In Progress')")->fetchColumn();
        $stats['overdue'] = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('Pending','In Progress') AND due_date IS NOT NULL AND due_date < CURDATE()")->fetchColumn();
    } catch (Throwable $e) {
    }
    try {
        $stmt = $pdo->query("SELECT t.id, t.title, t.status, t.due_date, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))),''), u.username, 'Unassigned') AS assignee
            FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to
            WHERE t.status IN ('Pending','In Progress')
            ORDER BY COALESCE(t.due_date, t.created_at) ASC LIMIT 5");
        $recentTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
    }
    try {
        $stmt = $pdo->query("SELECT m.id, m.subject, m.created_at, m.priority,
                COALESCE(NULLIF(TRIM(CONCAT(COALESCE(su.first_name,''),' ',COALESCE(su.last_name,''))),''), su.username, 'System') AS sender_name,
                su.role AS sender_role, m.recipient_id
            FROM messages m LEFT JOIN users su ON su.id = m.sender_id
            ORDER BY m.created_at DESC LIMIT 5");
        $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
    }
    $stats['unread'] = busiaUnreadMessageCount($pdo, (int)($_SESSION['user_id'] ?? 0));
}

$cards = [
    [
        'href' => '/Frontend/admin/staff.php',
        'module' => 'staff',
        'icon' => 'users',
        'title' => 'Staff & permissions',
        'desc' => 'Add staff logins and choose exactly which modules each person may open.',
        'stat' => $stats['staff'] . ' staff accounts',
        'action' => 'Manage staff',
    ],
    [
        'href' => '/Frontend/admin/users.php',
        'module' => 'users',
        'icon' => 'user-round',
        'title' => 'Customers',
        'desc' => 'Everyone with a store account: search, change roles, reset passwords.',
        'stat' => $stats['customers'] . ' customers',
        'action' => 'Open customers',
    ],
    [
        'href' => '/Frontend/admin/tasks.php',
        'module' => 'tasks',
        'icon' => 'list-checks',
        'title' => 'Assigned tasks',
        'desc' => 'Give work to a specific person with a due date and track it to done.',
        'stat' => $stats['open_tasks'] . ' open' . ($stats['overdue'] ? ' · ' . $stats['overdue'] . ' overdue' : ''),
        'action' => 'Open tasks',
    ],
    [
        'href' => '/Frontend/admin/messages.php',
        'module' => 'messages',
        'icon' => 'message-circle',
        'title' => 'Team messages',
        'desc' => 'Message one person or the whole team — it pops up on their screen.',
        'stat' => $stats['unread'] . ' unread',
        'action' => 'Open messages',
    ],
];
?>

<style>
    .hp-hero { background:linear-gradient(135deg,#14532d,#1B5E20 55%,#2E7D32); border-radius:16px; padding:24px 26px; color:#fff; margin-bottom:20px; position:relative; overflow:hidden; }
    .hp-hero h2 { margin:0 0 6px; font-family:'Outfit',sans-serif; font-size:1.5rem; letter-spacing:-0.3px; }
    .hp-hero p { margin:0; color:rgba(255,255,255,0.82); font-size:0.9rem; max-width:620px; line-height:1.55; }
    .hp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; margin-bottom:22px; }
    .hp-card { background:#fff; border:1px solid #e7edf3; border-radius:14px; padding:18px; display:flex; flex-direction:column; gap:10px; text-decoration:none; color:inherit; transition:border-color .15s, transform .15s, box-shadow .15s; }
    .hp-card:hover { border-color:#bbf7d0; box-shadow:0 12px 26px rgba(15,23,42,0.07); transform:translateY(-2px); }
    .hp-ico { width:40px; height:40px; border-radius:11px; background:#f0fdf4; color:#15803d; display:flex; align-items:center; justify-content:center; }
    .hp-card h3 { margin:0; font-size:1rem; color:#0f172a; font-family:'Outfit',sans-serif; }
    .hp-card p { margin:0; font-size:0.84rem; color:#64748b; line-height:1.5; flex:1 1 auto; }
    .hp-foot { display:flex; justify-content:space-between; align-items:center; font-size:0.78rem; font-weight:700; color:#15803d; }
    .hp-stat { background:#f1f5f9; color:#475569; border-radius:999px; padding:3px 10px; font-size:0.72rem; }
    .hp-panels { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:14px; }
    .hp-panel { background:#fff; border:1px solid #e7edf3; border-radius:14px; padding:16px 18px; }
    .hp-panel h4 { margin:0 0 10px; font-size:0.92rem; color:#0f172a; font-family:'Outfit',sans-serif; display:flex; justify-content:space-between; align-items:center; }
    .hp-panel h4 a { font-size:0.74rem; font-weight:700; color:var(--admin-primary); text-decoration:none; }
    .hp-item { display:flex; justify-content:space-between; gap:10px; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:0.83rem; color:#334155; text-decoration:none; }
    .hp-item:last-child { border-bottom:none; }
    .hp-item:hover { color:var(--admin-primary); }
    .hp-muted { color:#94a3b8; font-size:0.74rem; flex-shrink:0; }
</style>

<div class="hp-hero">
    <h2>Team &amp; People</h2>
    <p>Your staff, their access, the work assigned to them and the messages you share — all in one place.</p>
</div>

<div class="hp-grid">
    <?php foreach ($cards as $card):
        if (function_exists('busiaCanView') && !busiaCanView($card['module'])) continue;
    ?>
    <a class="hp-card" href="<?= htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="hp-ico"><i data-lucide="<?= $card['icon'] ?>" style="width:20px;height:20px;"></i></div>
        <h3><?= htmlspecialchars($card['title']) ?></h3>
        <p><?= htmlspecialchars($card['desc']) ?></p>
        <div class="hp-foot">
            <span style="display:inline-flex;align-items:center;gap:6px;"><?= htmlspecialchars($card['action']) ?> <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></span>
            <span class="hp-stat"><?= htmlspecialchars($card['stat']) ?></span>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<div class="hp-panels">
    <div class="hp-panel">
        <h4>Open tasks <a href="/Frontend/admin/tasks.php">View all</a></h4>
        <?php if (!$recentTasks): ?>
            <p style="margin:0;font-size:0.84rem;color:#64748b;">Nothing pending — the team is caught up.</p>
        <?php else: ?>
            <?php foreach ($recentTasks as $t): ?>
            <a class="hp-item" href="/Frontend/admin/tasks.php?action=edit&id=<?= (int)$t['id'] ?>">
                <span><?= htmlspecialchars((string)$t['title']) ?><br><span class="hp-muted"><?= htmlspecialchars((string)$t['assignee']) ?></span></span>
                <span class="hp-muted"><?= htmlspecialchars($t['due_date'] ? date('j M', strtotime((string)$t['due_date'])) : $t['status']) ?></span>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="hp-panel">
        <h4>Latest messages <a href="/Frontend/admin/messages.php">Open inbox</a></h4>
        <?php if (!$recentMessages): ?>
            <p style="margin:0;font-size:0.84rem;color:#64748b;">No messages yet — send one to your team.</p>
        <?php else: ?>
            <?php foreach ($recentMessages as $m): ?>
            <a class="hp-item" href="/Frontend/admin/messages.php?view=<?= (int)$m['id'] ?>">
                <span><?= htmlspecialchars((string)$m['subject']) ?><br><span class="hp-muted"><?= htmlspecialchars(busiaMessageSenderLabel($m)) ?></span></span>
                <span class="hp-muted"><?= htmlspecialchars(date('j M', strtotime((string)$m['created_at']))) ?></span>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
