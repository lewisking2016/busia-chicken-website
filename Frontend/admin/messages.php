<?php
/**
 * Admin — Team messages.
 *
 * Send a note to one staff member or to the whole team. Whatever is
 * sent appears as a pop-up on the recipient's screen the next time
 * their admin session polls, and in their inbox here.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Team Messages - Admin';
include __DIR__ . '/includes/admin_header.php';

require_once __DIR__ . '/../../Backend/config/permissions.php';
require_once __DIR__ . '/../../Backend/config/messages.php';

$pdo = getDB();
$message = '';
$error_message = '';
$currentAdminId = (int)($_SESSION['user_id'] ?? 0);
$canSend = ($_SESSION['role'] ?? '') === 'super_admin' || busiaCan('messages', 'edit') || busiaCan('messages', 'manage');

/* ── Send ──────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && $pdo) {
    if (!$canSend) {
        $error_message = 'You do not have permission to send team messages.';
    } else {
        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body = trim((string)($_POST['body'] ?? ''));
        $priority = (string)($_POST['priority'] ?? 'normal');

        if ($body === '') {
            $error_message = 'Write the message before sending.';
        } else {
            try {
                busiaMessageSend($pdo, $currentAdminId, $recipientId > 0 ? $recipientId : null, $subject, $body, $priority);
                $message = $recipientId > 0 ? 'Message sent — it pops up on their screen.' : 'Message sent to all staff.';
            } catch (Throwable $e) {
                $error_message = 'Could not send the message: ' . $e->getMessage();
            }
        }
    }
}

/* ── Mark read ─────────────────────────────────────────────────── */
$openId = (int)($_GET['view'] ?? $_GET['open'] ?? 0);
if ($openId > 0 && $pdo) {
    busiaMarkMessagesRead($pdo, $currentAdminId, [$openId]);
}

/* ── Data ──────────────────────────────────────────────────────── */
$tab = (string)($_GET['tab'] ?? 'inbox');
if (!in_array($tab, ['inbox', 'sent', 'all'], true)) $tab = 'inbox';

$staffList = [];
$rows = [];
$open = null;

if ($pdo) {
    busiaMessagesEnsureSchema($pdo);
    try {
        $stmt = $pdo->prepare('SELECT id, username, first_name, last_name, role FROM users WHERE id <> ? AND role <> "customer" ORDER BY first_name, last_name');
        $stmt->execute([$currentAdminId]);
        $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
    }

    try {
        $where = match ($tab) {
            'inbox' => '(m.recipient_id = ? OR m.recipient_id IS NULL)',
            'sent'  => 'm.sender_id = ?',
            default => '(m.recipient_id = ? OR m.recipient_id IS NULL OR m.sender_id = ?)',
        };
        $params = $tab === 'all' ? [$currentAdminId, $currentAdminId] : [$currentAdminId];

        $sql = "SELECT m.*,
                    COALESCE(NULLIF(TRIM(CONCAT(COALESCE(su.first_name,''),' ',COALESCE(su.last_name,''))),''), su.username, 'System') AS sender_name,
                    su.role AS sender_role, su.username AS sender_username,
                    COALESCE(NULLIF(TRIM(CONCAT(COALESCE(ru.first_name,''),' ',COALESCE(ru.last_name,''))),''), ru.username, 'All staff') AS recipient_name,
                    (SELECT r.read_at FROM message_reads r WHERE r.message_id = m.id AND r.user_id = ? LIMIT 1) AS read_at
                FROM messages m
                LEFT JOIN users su ON su.id = m.sender_id
                LEFT JOIN users ru ON ru.id = m.recipient_id
                WHERE $where
                ORDER BY m.created_at DESC
                LIMIT 200";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$currentAdminId], $params));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $error_message = 'Could not load messages: ' . $e->getMessage();
    }

    if ($openId > 0) {
        foreach ($rows as $r) {
            if ((int)$r['id'] === $openId) { $open = $r; break; }
        }
        if (!$open) {
            $stmt = $pdo->prepare('SELECT m.*, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(su.first_name,\'\'),\' \',COALESCE(su.last_name,\'\'))),\'\'), su.username, \'System\') AS sender_name, su.role AS sender_role, su.username AS sender_username, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(ru.first_name,\'\'),\' \',COALESCE(ru.last_name,\'\'))),\'\'), ru.username, \'All staff\') AS recipient_name FROM messages m LEFT JOIN users su ON su.id = m.sender_id LEFT JOIN users ru ON ru.id = m.recipient_id WHERE m.id = ?');
            $stmt->execute([$openId]);
            $open = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
}

$unreadCount = busiaUnreadMessageCount($pdo, $currentAdminId);
$sentCount = 0;
foreach ($rows as $r) { if ((int)$r['sender_id'] === $currentAdminId) $sentCount++; }

function msgInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) $out .= strtoupper(substr($p, 0, 1));
    return $out !== '' ? $out : '?';
}

function msgTime(string $created): string
{
    $ts = strtotime($created);
    if (!$ts) return $created;
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('j M Y', $ts);
}
?>

<style>
    .msg-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .msg-hero h2 { margin:0; font-family:'Outfit',sans-serif; font-size:1.55rem; color:var(--admin-text-heading); letter-spacing:-0.3px; }
    .msg-hero p { margin:5px 0 0; font-size:0.9rem; color:#64748b; }
    .msg-shell { display:grid; grid-template-columns:minmax(280px,360px) minmax(0,1fr); gap:16px; align-items:start; }
    .msg-list { background:#fff; border:1px solid #e7edf3; border-radius:14px; overflow:hidden; }
    .msg-tabs { display:flex; border-bottom:1px solid #eef2f6; }
    .msg-tabs a { flex:1; text-align:center; padding:11px 6px; font-size:0.82rem; font-weight:700; color:#64748b; text-decoration:none; border-bottom:2px solid transparent; }
    .msg-tabs a.active { color:var(--admin-primary); border-bottom-color:var(--admin-primary); background:#f8fdf9; }
    .msg-item { display:flex; gap:11px; padding:12px 14px; border-bottom:1px solid #f1f5f9; text-decoration:none; color:inherit; }
    .msg-item:hover { background:#fbfdfc; }
    .msg-item.active { background:#f0fdf4; }
    .msg-item.unread { background:#f8fbff; }
    .msg-item.unread .subject { font-weight:800; }
    .msg-av { width:34px; height:34px; border-radius:50%; background:#e2e8f0; color:#334155; font-size:0.72rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .msg-item.unread .msg-av { background:linear-gradient(135deg,#1B5E20,#43a047); color:#fff; }
    .subject { font-size:0.86rem; color:#0f172a; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .snippet { font-size:0.76rem; color:#64748b; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .msg-detail { background:#fff; border:1px solid #e7edf3; border-radius:14px; padding:20px; min-height:340px; }
    .msg-detail h3 { margin:0 0 6px; font-size:1.1rem; color:#0f172a; font-family:'Outfit',sans-serif; }
    .msg-body { margin-top:16px; padding:16px; background:#f8fafc; border:1px solid #eef2f6; border-radius:10px; font-size:0.9rem; line-height:1.65; color:#334155; white-space:pre-wrap; }
    .msg-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; font-size:0.7rem; font-weight:800; }
    .msg-compose { background:#fff; border:1px solid #e7edf3; border-radius:14px; padding:18px; }
    .msg-empty { padding:40px 20px; text-align:center; color:#64748b; }
    @media (max-width: 900px) { .msg-shell { grid-template-columns:1fr; } }
</style>

<div class="msg-hero">
    <div>
        <h2>Team messages</h2>
        <p>Message one person or the whole team. Recipients see it as a pop-up when they sign in.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('compose-card').scrollIntoView({behavior:'smooth',block:'center'}); document.getElementById('compose-body').focus();"
            style="border-radius:9px;display:inline-flex;align-items:center;gap:8px;padding:10px 16px;">
        <i data-lucide="send" style="width:16px;height:16px;"></i> Compose message
    </button>
</div>

<?php if ($message): ?>
<div style="padding:12px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:9px;color:#166534;font-size:0.88rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:16px;height:16px;"></i> <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:12px 16px;background:#fee2e2;border:1px solid #fecaca;border-radius:9px;color:#b91c1c;font-size:0.88rem;margin-bottom:16px;"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="msg-shell">
    <div class="msg-list">
        <div class="msg-tabs">
            <a href="messages.php?tab=inbox" class="<?= $tab === 'inbox' ? 'active' : '' ?>">Inbox<?= $unreadCount > 0 ? ' · ' . $unreadCount : '' ?></a>
            <a href="messages.php?tab=sent" class="<?= $tab === 'sent' ? 'active' : '' ?>">Sent</a>
            <a href="messages.php?tab=all" class="<?= $tab === 'all' ? 'active' : '' ?>">All</a>
        </div>
        <?php if (!$rows): ?>
            <div class="msg-empty">
                <i data-lucide="mail-open" style="width:30px;height:30px;color:#94a3b8;margin-bottom:8px;"></i>
                <p style="margin:0;font-weight:700;color:#334155;">Nothing here yet</p>
                <p style="margin:4px 0 0;font-size:0.82rem;">Send the first message to your team.</p>
            </div>
        <?php else: ?>
            <?php foreach ($rows as $r):
                $isMine = (int)$r['sender_id'] === $currentAdminId;
                $isUnread = !$isMine && empty($r['read_at']);
                $who = $isMine ? ('To ' . $r['recipient_name']) : busiaMessageSenderLabel($r);
            ?>
            <a class="msg-item <?= $isUnread ? 'unread' : '' ?> <?= $open && (int)$open['id'] === (int)$r['id'] ? 'active' : '' ?>"
               href="messages.php?tab=<?= $tab ?>&view=<?= (int)$r['id'] ?>">
                <div class="msg-av"><?= htmlspecialchars(msgInitials((string)($isMine ? $r['recipient_name'] : $r['sender_name']))) ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">
                        <span style="font-size:0.74rem;font-weight:700;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($who) ?></span>
                        <span style="font-size:0.68rem;color:#94a3b8;flex-shrink:0;"><?= htmlspecialchars(msgTime((string)$r['created_at'])) ?></span>
                    </div>
                    <div class="subject"><?= htmlspecialchars((string)$r['subject']) ?><?= ($r['priority'] ?? 'normal') === 'high' ? ' 🔴' : '' ?></div>
                    <div class="snippet"><?= htmlspecialchars(mb_substr(trim((string)$r['body']), 0, 70)) ?><?= mb_strlen((string)$r['body']) > 70 ? '…' : '' ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div>
        <?php if ($open): ?>
            <?php $fromMe = (int)$open['sender_id'] === $currentAdminId; ?>
            <div class="msg-detail">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                    <div>
                        <h3><?= htmlspecialchars((string)$open['subject']) ?></h3>
                        <p style="margin:0;font-size:0.8rem;color:#64748b;">
                            <?= htmlspecialchars($fromMe ? 'You' : busiaMessageSenderLabel($open)) ?>
                            → <?= htmlspecialchars($fromMe ? (string)$open['recipient_name'] : 'you') ?>
                            · <?= htmlspecialchars(date('j M Y, H:i', strtotime((string)$open['created_at']))) ?>
                        </p>
                    </div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <?php if (($open['priority'] ?? 'normal') === 'high'): ?>
                            <span class="msg-badge" style="background:#fef2f2;color:#dc2626;">High priority</span>
                        <?php endif; ?>
                        <span class="msg-badge" style="background:#f1f5f9;color:#475569;"><?= $open['recipient_id'] === null ? 'All staff' : 'Direct' ?></span>
                    </div>
                </div>
                <div class="msg-body" id="msg-body"><?= htmlspecialchars((string)$open['body']) ?></div>
                <?php if (!$fromMe && $canSend): ?>
                    <div style="margin-top:14px;">
                        <button type="button" class="btn btn-outline" style="border-radius:8px;" onclick="prefillReply(<?= (int)$open['sender_id'] ?>, <?= htmlspecialchars(json_encode((string)$open['sender_name']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode('Re: ' . $open['subject']), ENT_QUOTES, 'UTF-8') ?>)">Reply</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="msg-detail" style="display:flex;align-items:center;justify-content:center;">
                <div class="msg-empty">
                    <i data-lucide="mouse-pointer-click" style="width:30px;height:30px;color:#94a3b8;margin-bottom:8px;"></i>
                    <p style="margin:0;font-weight:700;color:#334155;">Select a message</p>
                    <p style="margin:4px 0 0;font-size:0.84rem;">Pick a conversation on the left to read it, or compose a new one below.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canSend): ?>
        <div class="msg-compose" id="compose-card" style="margin-top:16px;">
            <h3 style="margin:0 0 4px;font-family:'Outfit',sans-serif;font-size:1.05rem;color:#0f172a;">New message</h3>
            <p style="margin:0 0 14px;font-size:0.82rem;color:#64748b;">Choose one person, or send to everyone. They get a pop-up notification.</p>
            <form method="post">
                <input type="hidden" name="send_message" value="1">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Send to</label>
                        <select class="admin-form-control" name="recipient_id" id="compose-recipient">
                            <option value="0">📢 All staff</option>
                            <?php foreach ($staffList as $staff): ?>
                                <?php $nm = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?: $staff['username']; ?>
                                <option value="<?= (int)$staff['id'] ?>"><?= htmlspecialchars($nm) ?> · <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$staff['role']))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Priority</label>
                        <select class="admin-form-control" name="priority">
                            <option value="normal">Normal</option>
                            <option value="high">High — keep pop-up until read</option>
                        </select>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Subject</label>
                    <input class="admin-form-control" type="text" name="subject" id="compose-subject" placeholder="e.g. Eggs collection on Saturday">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Message</label>
                    <textarea class="admin-form-control" name="body" id="compose-body" rows="4" required placeholder="Write your message…"></textarea>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;display:inline-flex;align-items:center;gap:7px;">
                        <i data-lucide="send" style="width:16px;height:16px;"></i> Send message
                    </button>
                    <small style="font-size:0.76rem;color:#64748b;">Broadcasts reach everyone except you.</small>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function prefillReply(userId, name, subject) {
    var rec = document.getElementById('compose-recipient');
    if (rec) {
        var opt = Array.prototype.find.call(rec.options, function (o) { return o.value === String(userId); });
        if (opt) rec.value = String(userId);
    }
    var subj = document.getElementById('compose-subject');
    if (subj) subj.value = subject;
    document.getElementById('compose-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
    var body = document.getElementById('compose-body');
    if (body) { body.value = ''; body.focus(); }
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
