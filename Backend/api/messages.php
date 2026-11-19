<?php
/**
 * Team message notification endpoint used by the admin header.
 *
 *   GET  ?action=pending      → unread messages for the signed-in staff
 *   POST ?action=read         → body: ids[] (marks only the caller's copies)
 *   POST ?action=send         → subject, body, recipient_id (0 = everyone)
 *
 * Always scoped to $_SESSION['user_id']; never trusts a client user id.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/messages.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function busiaJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$role = (string)($_SESSION['role'] ?? '');
if ($userId <= 0 || !in_array($role, ['super_admin', 'farm_manager', 'stock_manager', 'sales_staff'], true)) {
    busiaJson(['ok' => false, 'error' => 'Unauthorized'], 401);
}

$pdo = getDatabaseConnection();
if (!$pdo) {
    busiaJson(['ok' => false, 'error' => 'Database unavailable'], 500);
}

$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'pending');

if ($action === 'pending') {
    $rows = busiaUnreadMessages($pdo, $userId, 5);
    $messages = [];
    foreach ($rows as $row) {
        $messages[] = [
            'id' => (int)$row['id'],
            'subject' => (string)$row['subject'],
            'body' => (string)$row['body'],
            'priority' => (string)($row['priority'] ?? 'normal'),
            'created_at' => (string)$row['created_at'],
            'is_broadcast' => $row['recipient_id'] === null,
            'sender' => busiaMessageSenderLabel($row),
        ];
    }
    busiaJson([
        'ok' => true,
        'count' => busiaUnreadMessageCount($pdo, $userId),
        'messages' => $messages,
    ]);
}

if ($action === 'read') {
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    $marked = busiaMarkMessagesRead($pdo, $userId, $ids);
    busiaJson(['ok' => true, 'marked' => $marked, 'count' => busiaUnreadMessageCount($pdo, $userId)]);
}

if ($action === 'send') {
    require_once __DIR__ . '/../config/permissions.php';
    if (!busiaCan('messages', 'edit')) {
        busiaJson(['ok' => false, 'error' => 'You do not have permission to send team messages.'], 403);
    }

    $subject = trim((string)($_POST['subject'] ?? ''));
    $body = trim((string)($_POST['body'] ?? ''));
    $recipient = (int)($_POST['recipient_id'] ?? 0);
    $priority = (string)($_POST['priority'] ?? 'normal');

    if ($body === '') {
        busiaJson(['ok' => false, 'error' => 'Write a message first.'], 422);
    }
    if ($recipient > 0) {
        $check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $check->execute([$recipient]);
        if (!$check->fetchColumn()) {
            busiaJson(['ok' => false, 'error' => 'That staff account no longer exists.'], 422);
        }
    }

    $id = busiaMessageSend($pdo, $userId, $recipient > 0 ? $recipient : null, $subject, $body, $priority);
    busiaJson(['ok' => true, 'id' => $id]);
}

busiaJson(['ok' => false, 'error' => 'Unknown action'], 400);
