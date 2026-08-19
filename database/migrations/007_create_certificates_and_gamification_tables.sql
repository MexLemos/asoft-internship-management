-- Migration: 007_create_certificates_and_gamification_tables.sql

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL UNIQUE,
    certificate_code VARCHAR(50) NOT NULL UNIQUE,
    validation_hash VARCHAR(64) NOT NULL UNIQUE,
    total_hours_completed DECIMAL(6,2) NOT NULL DEFAULT 300.00,
    final_score DECIMAL(5,2) NOT NULL DEFAULT 85.00,
    issue_date DATE NOT NULL,
    completion_date DATE NOT NULL,
    signatory_name VARCHAR(150) NOT NULL DEFAULT 'Direcção Geral Asoftmedia',
    signatory_role VARCHAR(100) NOT NULL DEFAULT 'Director Geral',
    pdf_file_path VARCHAR(255) NULL,
    status ENUM('valid', 'revoked', 'expired') DEFAULT 'valid',
    revocation_reason TEXT NULL,
    revoked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cert_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    INDEX idx_cert_hash (validation_hash),
    INDEX idx_cert_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificate_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    validated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cl_certificate FOREIGN KEY (certificate_id) REFERENCES certificates (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL DEFAULT 'bi-award',
    points_reward INT UNSIGNED DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_intern_badge (intern_id, badge_id),
    CONSTRAINT fk_ib_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_ib_badge FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_gamification_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    points INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    source_type ENUM('task', 'attendance', 'test', 'course', 'bonus') NOT NULL,
    source_id BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_igp_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
