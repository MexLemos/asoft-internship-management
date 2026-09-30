-- Migration: 017_intern_lifecycle_and_mentorship.sql
-- Fase 1: Ciclo de Vida do Estagiário, Máquina de Estados e Supervisão Independente

-- 1. Atualizar ENUM de status e adicionar campos de ciclo de vida e regime na tabela interns
ALTER TABLE interns 
    MODIFY COLUMN status ENUM(
        'pending',
        'active',
        'suspended',
        'awaiting_completion',
        'completed',
        'dropped_out',
        'terminated_anomalous',
        'cancelled'
    ) DEFAULT 'active';

ALTER TABLE interns
    ADD COLUMN completion_date DATE NULL AFTER end_date,
    ADD COLUMN exit_reason TEXT NULL AFTER status_reason,
    ADD COLUMN work_mode ENUM('presential', 'hybrid', 'remote') DEFAULT 'presential' AFTER exit_reason,
    ADD COLUMN remote_authorized_until DATE NULL AFTER work_mode;

-- 2. Tabela de histórico de transições de estado do estagiário (Auditoria e Trilha de Ciclo de Vida)
CREATE TABLE IF NOT EXISTS intern_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(50) NOT NULL,
    to_status VARCHAR(50) NOT NULL,
    changed_by BIGINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_status_history_user FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE RESTRICT,
    INDEX idx_history_intern (intern_id),
    INDEX idx_history_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de registos de mentoria e acompanhamento contínuo (Supervisão independente de tarefas)
CREATE TABLE IF NOT EXISTS intern_mentorship_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    supervisor_id BIGINT UNSIGNED NOT NULL,
    session_date DATETIME NOT NULL,
    session_type ENUM('1_on_1', 'periodic_review', 'technical_orientation', 'feedback', 'disciplinary', 'other') DEFAULT '1_on_1',
    title VARCHAR(200) NOT NULL,
    summary TEXT NOT NULL,
    topics_discussed TEXT NULL,
    action_items TEXT NULL,
    rating TINYINT UNSIGNED NULL COMMENT 'Avaliação de desempenho da sessão de 1 a 5',
    is_private BOOLEAN DEFAULT FALSE COMMENT 'Se verdadeiro, visível apenas a supervisores e administradores',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_mentorship_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_mentorship_supervisor FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE RESTRICT,
    INDEX idx_mentorship_intern (intern_id),
    INDEX idx_mentorship_supervisor (supervisor_id),
    INDEX idx_mentorship_session_date (session_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
