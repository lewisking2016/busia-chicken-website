<?php
/**
 * Team messaging helpers.
 *
 * A message row has a recipient (one person) or recipient_id = NULL,
 * which means "every staff member". Broadcasts are read-tracked per
 * person in message_reads so one staff opening it does not clear the
 * notification for everyone else.
 */
declare(strict_types=1);

function busiaMessagesPdo(): ?PDO
{
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    if (function_exists('getDatabaseConnection')) {
        $pdo = getDatabaseConnection();
        return $pdo instanceof PDO ? $pdo : null;
    }
    if (function_exists('getDB')) {
        $pdo = getDB();
        return $pdo instanceof PDO ? $pdo : null;
    }
    return null;
}

/**
 * Ensure the v8 messaging tables exist on hosts whose migration has not
 * run yet (mirrors busiaSaveUserPermissions()).
 */
function busiaMessagesEnsureSchema(PDO $pdo): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM messages')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('priority', $cols, true)) {
            $pdo->exec("ALTER TABLE messages ADD COLUMN priority ENUM('normal','high') NOT NULL DEFAULT 'normal'");
        }
        if (!in_array('read_at', $cols, true)) {
            $pdo->exec('ALTER TABLE messages ADD COLUMN read_at DATETIME NULL');
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS message_reads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id INT NOT NULL,
            user_id INT NOT NULL,
            read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_message_user (message_id, user_id),
            INDEX idx_reads_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
        if (function_exists('busiaDbLog')) {
            busiaDbLog('Message schema repair failed: ' . $e->getMessage());
        }
    }
}

/**
 * Send one message. $recipientId = null broadcasts to all staff.
 */
function busiaMessageSend(PDO $pdo, int $senderId, ?int $recipientId, string $subject, string $body, string $priority = 'normal'): int
{
    busiaMessagesEnsureSchema($pdo);
    $subject = trim($subject) !== '' ? trim($subject) : 'Team message';
    $priority = $priority === 'high' ? 'high' : 'normal';

    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body, status, priority) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$senderId, $recipientId, $subject, trim($body), 'sent', $priority]);

    return (int)$pdo->lastInsertId();
}

/**
 * Unread messages for one user (direct + broadcasts), newest first.
 */
function busiaUnreadMessages(PDO $pdo, int $userId, int $limit = 5): array
{
    if ($userId <= 0) return [];
    busiaMessagesEnsureSchema($pdo);
    try {
        $sql = 'SELECT m.id, m.subject, m.body, m.priority, m.created_at,
                       m.recipient_id,
                       COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, ""))), ""), u.username, "System") AS sender_name,
                       u.role AS sender_role
                FROM messages m
                LEFT JOIN users u ON u.id = m.sender_id
                WHERE (m.recipient_id = ? OR m.recipient_id IS NULL)
                  AND m.sender_id <> ?
                  AND NOT EXISTS (SELECT 1 FROM message_reads r WHERE r.message_id = m.id AND r.user_id = ?)
                ORDER BY (m.priority = "high") DESC, m.created_at DESC
                LIMIT ' . (int)max(1, $limit);
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function busiaUnreadMessageCount(PDO $pdo, int $userId): int
{
    if ($userId <= 0) return 0;
    busiaMessagesEnsureSchema($pdo);
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM messages m
            WHERE (m.recipient_id = ? OR m.recipient_id IS NULL)
              AND m.sender_id <> ?
              AND NOT EXISTS (SELECT 1 FROM message_reads r WHERE r.message_id = m.id AND r.user_id = ?)');
        $stmt->execute([$userId, $userId, $userId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Mark messages read for this user only.
 */
function busiaMarkMessagesRead(PDO $pdo, int $userId, array $ids): int
{
    busiaMessagesEnsureSchema($pdo);
    $ids = array_values(array_filter(array_map('intval', $ids), static fn($i) => $i > 0));
    if (!$ids || $userId <= 0) return 0;

    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("INSERT IGNORE INTO message_reads (message_id, user_id)
        SELECT id, ? FROM messages WHERE id IN ($in)
          AND (recipient_id = ? OR recipient_id IS NULL)");
    $stmt->execute(array_merge([$userId], $ids, [$userId]));
    $count = $stmt->rowCount();

    // Keep the legacy status column meaningful for direct messages.
    try {
        $upd = $pdo->prepare("UPDATE messages SET status = 'read', read_at = NOW()
            WHERE id IN ($in) AND recipient_id = ? AND status <> 'read'");
        $upd->execute(array_merge($ids, [$userId]));
    } catch (Throwable $e) {
    }

    return $count;
}

/**
 * Who sent it, in the words the recipient should see.
 */
function busiaMessageSenderLabel(array $message): string
{
    $role = (string)($message['sender_role'] ?? '');
    $name = (string)($message['sender_name'] ?? 'A team member');
    if ($role === 'super_admin') {
        return 'Admin (' . $name . ')';
    }
    $roleLabel = ucwords(str_replace('_', ' ', $role));
    return $roleLabel !== '' ? $roleLabel . ' · ' . $name : $name;
}
