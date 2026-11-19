-- ══════════════════════════════════════════════════════════════
-- Migration V8: per-user permissions & team messaging
--
-- Roles were the only way to control access, so two farm managers
-- could never have different access. This adds a per-account
-- permission matrix (view / edit / delete / manage per module) that
-- overrides the role defaults for that one person.
--
-- It also upgrades team messages: broadcast to every staff member,
-- priorities, and read receipts so the recipient can be notified.
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_key VARCHAR(50) NOT NULL,
    can_view TINYINT(1) NOT NULL DEFAULT 0,
    can_edit TINYINT(1) NOT NULL DEFAULT 0,
    can_delete TINYINT(1) NOT NULL DEFAULT 0,
    can_manage TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_module (user_id, module_key),
    INDEX idx_user_perm_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message priority + read receipt (recipient_id NULL = all staff)
ALTER TABLE messages
    ADD COLUMN IF NOT EXISTS priority ENUM('normal','high') NOT NULL DEFAULT 'normal';

ALTER TABLE messages
    ADD COLUMN IF NOT EXISTS read_at DATETIME NULL;

-- Recipient can be empty for a broadcast to the whole team.
ALTER TABLE messages
    MODIFY COLUMN recipient_id INT NULL;

-- Per-user read receipts. A broadcast has one row in messages but every
-- staff member marks it read for themselves here.
CREATE TABLE IF NOT EXISTS message_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_message_user (message_id, user_id),
    INDEX idx_reads_user (user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
