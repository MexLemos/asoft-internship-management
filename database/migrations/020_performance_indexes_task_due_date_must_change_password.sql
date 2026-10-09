-- Migration 020: Otimização de Performance, Prazos de Tarefas e Alteração Obrigatória de Senha

-- 1. Campo must_change_password em users (caso ainda não exista)
ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 
    AFTER password_hash;

-- 2. Campo due_date em tasks
ALTER TABLE tasks 
    ADD COLUMN IF NOT EXISTS due_date DATE NULL 
    AFTER estimated_hours;

-- 3. Índices de alta performance para concorrência
CREATE INDEX IF NOT EXISTS idx_users_status_email ON users (status, email);
CREATE INDEX IF NOT EXISTS idx_attendance_intern_date ON attendance (intern_id, date);
CREATE INDEX IF NOT EXISTS idx_task_assign_intern_status ON task_assignments (intern_id, status);
CREATE INDEX IF NOT EXISTS idx_task_assign_due_date ON task_assignments (due_date);
CREATE INDEX IF NOT EXISTS idx_dynamic_tokens_expires ON dynamic_attendance_tokens (expires_at);
