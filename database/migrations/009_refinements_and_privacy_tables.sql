-- Migration: 009_refinements_and_privacy_tables.sql

-- 1. Task History & Audit Trail
CREATE TABLE IF NOT EXISTS task_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'assigned, started, submitted, approved, rejected, reopened',
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    score DECIMAL(5,2) NULL,
    comments TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_th_assignment FOREIGN KEY (assignment_id) REFERENCES task_assignments (id) ON DELETE CASCADE,
    CONSTRAINT fk_th_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Content Doubts / Q&A in Study Zone
CREATE TABLE IF NOT EXISTS content_doubts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NULL,
    answered_by BIGINT UNSIGNED NULL,
    answered_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cd_content FOREIGN KEY (content_id) REFERENCES learning_contents (id) ON DELETE CASCADE,
    CONSTRAINT fk_cd_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_cd_answerer FOREIGN KEY (answered_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Institution Conversations & Direct Messages
CREATE TABLE IF NOT EXISTS institution_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_icv_institution FOREIGN KEY (institution_id) REFERENCES institutions (id) ON DELETE CASCADE,
    CONSTRAINT fk_icv_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institution_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_im_conversation FOREIGN KEY (conversation_id) REFERENCES institution_conversations (id) ON DELETE CASCADE,
    CONSTRAINT fk_im_sender FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Privacy & Data Protection Compliance (Angola Lei 22/11)
CREATE TABLE IF NOT EXISTS privacy_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    policy_version VARCHAR(20) NOT NULL DEFAULT '1.0',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    consent_type VARCHAR(50) DEFAULT 'general_policy',
    accepted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pc_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS privacy_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('access', 'rectification', 'deletion', 'opposition') NOT NULL,
    details TEXT NOT NULL,
    status ENUM('pending', 'under_review', 'fulfilled', 'rejected') DEFAULT 'pending',
    response_notes TEXT NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_pr_resolver FOREIGN KEY (resolved_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Password Resets (Secure Tokens)
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pwd_reset_token (token_hash),
    INDEX idx_pwd_reset_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
