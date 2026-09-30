-- Migration: 018_attendance_precision_devices_and_qr.sql
-- Fase 2: Motor de Presença Reforçado, Filtro de Precisão GPS, Dynamic QR Code e Device Binding

-- 1. Tabela de dispositivos vinculados ao estagiário (Device Binding anti-partilha de credenciais)
CREATE TABLE IF NOT EXISTS intern_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    device_uuid VARCHAR(100) NOT NULL,
    device_name VARCHAR(150) NULL,
    user_agent TEXT NULL,
    is_trusted BOOLEAN DEFAULT TRUE,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_intern_devices_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    UNIQUE KEY uk_intern_device (intern_id, device_uuid),
    INDEX idx_device_uuid (device_uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de tokens dinâmicos para presença via QR Code rotativo (TOTP na Sede)
CREATE TABLE IF NOT EXISTS dynamic_attendance_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    token_seed VARCHAR(32) NOT NULL,
    generated_by BIGINT UNSIGNED NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_qr_token_lookup (token_hash, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de consumo de tokens dinâmicos (Garante uso único por estagiário)
CREATE TABLE IF NOT EXISTS dynamic_token_redemptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_redemption_token FOREIGN KEY (token_id) REFERENCES dynamic_attendance_tokens (id) ON DELETE CASCADE,
    CONSTRAINT fk_redemption_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    UNIQUE KEY uk_token_intern (token_id, intern_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Adicionar colunas de método de verificação, device e auditoria na tabela attendance
ALTER TABLE attendance
    ADD COLUMN verification_method ENUM('gps', 'dynamic_qr', 'hybrid_gps_qr', 'manual_supervisor') DEFAULT 'gps' AFTER status,
    ADD COLUMN device_uuid VARCHAR(100) NULL AFTER verification_method,
    ADD COLUMN flagged_for_review BOOLEAN DEFAULT FALSE AFTER device_uuid,
    ADD COLUMN flag_reason TEXT NULL AFTER flagged_for_review;

-- 5. Adicionar colunas de device e método na tabela attendance_attempts e flexibilizar status
ALTER TABLE attendance_attempts
    ADD COLUMN device_uuid VARCHAR(100) NULL AFTER user_agent,
    ADD COLUMN verification_method VARCHAR(50) DEFAULT 'gps' AFTER device_uuid,
    MODIFY COLUMN status VARCHAR(60) NOT NULL;
