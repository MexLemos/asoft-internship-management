<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use Throwable;

class DatabaseAutoMigrator
{
    public const SCHEMA_VERSION = 'v20';
    private static bool $checked = false;

    /**
     * Garante que todas as tabelas e colunas necessárias para as fases mais recentes
     * do sistema estejam devidamente criadas no MySQL (Self-healing idempotente).
     * Utiliza trava em ficheiro local para evitar sobrecarga no MySQL em produção.
     */
    public static function ensureSchemaUpToDate(PDO $pdo, bool $force = false): array
    {
        if (self::$checked && !$force) {
            return ['status' => 'already_checked', 'applied' => []];
        }

        $lockFile = dirname(__DIR__, 2) . '/storage/cache/schema_' . self::SCHEMA_VERSION . '.lock';
        if (!$force && file_exists($lockFile)) {
            self::$checked = true;
            return ['status' => 'already_checked', 'applied' => []];
        }

        $applied = [];

        try {
            // 0. Garantir tabela de migrações
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    batch INT UNSIGNED NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // 1. Verificações e Atualizações da Fase 1 (Migration 017: Ciclo de Vida e Mentoria)
            if (!self::tableExists($pdo, 'intern_status_history')) {
                // Atualizar ENUM de status e campos na tabela interns
                try {
                    $pdo->exec("
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
                        ) DEFAULT 'active'
                    ");
                } catch (Throwable $e) {
                    error_log("AutoMigrator: Não foi possível atualizar ENUM status em interns: " . $e->getMessage());
                }

                if (!self::columnExists($pdo, 'interns', 'completion_date')) {
                    $pdo->exec("ALTER TABLE interns ADD COLUMN completion_date DATE NULL AFTER end_date");
                }
                if (!self::columnExists($pdo, 'interns', 'exit_reason')) {
                    $pdo->exec("ALTER TABLE interns ADD COLUMN exit_reason TEXT NULL AFTER status_reason");
                }
                if (!self::columnExists($pdo, 'interns', 'work_mode')) {
                    $pdo->exec("ALTER TABLE interns ADD COLUMN work_mode ENUM('presential', 'hybrid', 'remote') DEFAULT 'presential' AFTER exit_reason");
                }
                if (!self::columnExists($pdo, 'interns', 'remote_authorized_until')) {
                    $pdo->exec("ALTER TABLE interns ADD COLUMN remote_authorized_until DATE NULL AFTER work_mode");
                }

                // Criar tabela intern_status_history
                $pdo->exec("
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
                ");

                // Criar tabela intern_mentorship_logs
                $pdo->exec("
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
                        rating TINYINT UNSIGNED NULL,
                        is_private BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        deleted_at TIMESTAMP NULL,
                        CONSTRAINT fk_mentorship_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
                        CONSTRAINT fk_mentorship_supervisor FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE RESTRICT,
                        INDEX idx_mentorship_intern (intern_id),
                        INDEX idx_mentorship_supervisor (supervisor_id),
                        INDEX idx_mentorship_session_date (session_date)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                self::recordMigration($pdo, '017_intern_lifecycle_and_mentorship.sql');
                $applied[] = '017_intern_lifecycle_and_mentorship.sql';
            }

            // 2. Verificações e Atualizações da Fase 2 (Migration 018: Precisão, Dispositivos e Dynamic QR)
            if (!self::tableExists($pdo, 'intern_devices') || !self::tableExists($pdo, 'dynamic_attendance_tokens')) {
                // Criar tabela intern_devices
                $pdo->exec("
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
                ");

                // Criar tabela dynamic_attendance_tokens
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS dynamic_attendance_tokens (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        token_hash VARCHAR(64) NOT NULL UNIQUE,
                        token_seed VARCHAR(32) NOT NULL,
                        generated_by BIGINT UNSIGNED NULL,
                        expires_at DATETIME NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_qr_token_lookup (token_hash, expires_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                // Criar tabela dynamic_token_redemptions
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS dynamic_token_redemptions (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        token_id BIGINT UNSIGNED NOT NULL,
                        intern_id BIGINT UNSIGNED NOT NULL,
                        redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT fk_redemption_token FOREIGN KEY (token_id) REFERENCES dynamic_attendance_tokens (id) ON DELETE CASCADE,
                        CONSTRAINT fk_redemption_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
                        UNIQUE KEY uk_token_intern (token_id, intern_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                // Atualizar colunas em attendance
                if (!self::columnExists($pdo, 'attendance', 'verification_method')) {
                    $pdo->exec("ALTER TABLE attendance ADD COLUMN verification_method ENUM('gps', 'dynamic_qr', 'hybrid_gps_qr', 'manual_supervisor') DEFAULT 'gps' AFTER status");
                }
                if (!self::columnExists($pdo, 'attendance', 'device_uuid')) {
                    $pdo->exec("ALTER TABLE attendance ADD COLUMN device_uuid VARCHAR(100) NULL AFTER verification_method");
                }
                if (!self::columnExists($pdo, 'attendance', 'flagged_for_review')) {
                    $pdo->exec("ALTER TABLE attendance ADD COLUMN flagged_for_review BOOLEAN DEFAULT FALSE AFTER device_uuid");
                }
                if (!self::columnExists($pdo, 'attendance', 'flag_reason')) {
                    $pdo->exec("ALTER TABLE attendance ADD COLUMN flag_reason TEXT NULL AFTER flagged_for_review");
                }

                // Atualizar colunas em attendance_attempts
                if (!self::columnExists($pdo, 'attendance_attempts', 'device_uuid')) {
                    $pdo->exec("ALTER TABLE attendance_attempts ADD COLUMN device_uuid VARCHAR(100) NULL AFTER user_agent");
                }
                if (!self::columnExists($pdo, 'attendance_attempts', 'verification_method')) {
                    $pdo->exec("ALTER TABLE attendance_attempts ADD COLUMN verification_method VARCHAR(50) DEFAULT 'gps' AFTER device_uuid");
                }
                try {
                    $pdo->exec("ALTER TABLE attendance_attempts MODIFY COLUMN status VARCHAR(60) NOT NULL");
                } catch (Throwable $e) {
                    // Mantém se já estiver modificado
                }

                self::recordMigration($pdo, '018_attendance_precision_devices_and_qr.sql');
                $applied[] = '018_attendance_precision_devices_and_qr.sql';
            }

            // 3. Verificações de Suporte a Candidaturas Singulares (Migration 019)
            try {
                $checkNullable = $pdo->query("
                    SELECT IS_NULLABLE 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'interns' 
                      AND COLUMN_NAME = 'institution_id'
                ")->fetchColumn();

                if ($checkNullable === 'NO') {
                    $pdo->exec("ALTER TABLE interns MODIFY COLUMN institution_id BIGINT UNSIGNED NULL");
                    self::recordMigration($pdo, '019_make_interns_institution_id_nullable.sql');
                    $applied[] = '019_make_interns_institution_id_nullable.sql';
                }
            } catch (Throwable $e) {
                error_log("AutoMigrator: Falha ao tornar institution_id nullable: " . $e->getMessage());
            }

            // Garantir que a instituição 'Singular (Candidatura Particular)' existe caso seja referenciada
            try {
                $hasSingular = (int)$pdo->query("
                    SELECT COUNT(*) FROM institutions 
                    WHERE name LIKE 'Singular%' OR nif = 'SINGULAR'
                ")->fetchColumn();

                if ($hasSingular === 0) {
                    $pdo->exec("
                        INSERT INTO institutions (name, nif, email, phone, address, city, type, status) 
                        VALUES ('Singular (Candidatura Particular)', 'SINGULAR', 'singular@asoftmedia-ao.com', 'N/D', 'Luanda', 'Luanda', 'other', 'active')
                    ");
                    $applied[] = 'institution_singular_created';
                }
            } catch (Throwable $e) {
                error_log("AutoMigrator: Falha ao criar instituição Singular: " . $e->getMessage());
            }

            // 6. Atualizações da Fase de Performance, Prazos de Tarefas e Alteração Obrigatória de Senha (Migration 020)
            try {
                if (!self::columnExists($pdo, 'users', 'must_change_password')) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER password_hash");
                    $applied[] = 'users_must_change_password_column_added';
                }

                if (!self::columnExists($pdo, 'tasks', 'due_date')) {
                    $pdo->exec("ALTER TABLE tasks ADD COLUMN due_date DATE NULL AFTER estimated_hours");
                    $applied[] = 'tasks_due_date_column_added';
                }

                // Índices de alta performance
                if (!self::indexExists($pdo, 'users', 'idx_users_status_email')) {
                    $pdo->exec("CREATE INDEX idx_users_status_email ON users (status, email)");
                    $applied[] = 'idx_users_status_email_created';
                }

                if (!self::indexExists($pdo, 'attendance', 'idx_attendance_intern_date')) {
                    $pdo->exec("CREATE INDEX idx_attendance_intern_date ON attendance (intern_id, date)");
                    $applied[] = 'idx_attendance_intern_date_created';
                }

                if (!self::indexExists($pdo, 'task_assignments', 'idx_task_assign_intern_status')) {
                    $pdo->exec("CREATE INDEX idx_task_assign_intern_status ON task_assignments (intern_id, status)");
                    $applied[] = 'idx_task_assign_intern_status_created';
                }

                if (!self::indexExists($pdo, 'task_assignments', 'idx_task_assign_due_date')) {
                    $pdo->exec("CREATE INDEX idx_task_assign_due_date ON task_assignments (due_date)");
                    $applied[] = 'idx_task_assign_due_date_created';
                }

                if (!self::indexExists($pdo, 'dynamic_attendance_tokens', 'idx_dynamic_tokens_expires')) {
                    $pdo->exec("CREATE INDEX idx_dynamic_tokens_expires ON dynamic_attendance_tokens (expires_at)");
                    $applied[] = 'idx_dynamic_tokens_expires_created';
                }

                self::recordMigration($pdo, '020_performance_indexes_task_due_date_must_change_password.sql');
            } catch (Throwable $e) {
                error_log("AutoMigrator: Falha ao aplicar migration 020: " . $e->getMessage());
            }

            self::$checked = true;

            // Gravar ficheiro de trava para evitar reexecução no MySQL em requisições subsequentes
            try {
                @mkdir(dirname($lockFile), 0755, true);
                @file_put_contents($lockFile, date('c'));
            } catch (Throwable $e) {
                error_log("AutoMigrator: Não foi possível gravar ficheiro de trava: " . $e->getMessage());
            }

            return [
                'status' => 'success',
                'applied' => $applied,
                'message' => empty($applied) ? 'Base de dados já se encontra atualizada.' : 'Migrações aplicadas com sucesso: ' . implode(', ', $applied)
            ];
        } catch (Throwable $e) {
            error_log("DatabaseAutoMigrator Exception: " . $e->getMessage());
            return [
                'status' => 'error',
                'applied' => $applied,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function isSchemaSynchronized(PDO $pdo): bool
    {
        $requiredTables = [
            'intern_status_history',
            'intern_mentorship_logs',
            'intern_devices',
            'dynamic_attendance_tokens',
            'dynamic_token_redemptions'
        ];

        foreach ($requiredTables as $tbl) {
            if (!self::tableExists($pdo, $tbl)) {
                return false;
            }
        }

        $requiredColumns = [
            ['interns', 'work_mode'],
            ['attendance', 'verification_method'],
            ['attendance_attempts', 'device_uuid']
        ];

        foreach ($requiredColumns as [$tbl, $col]) {
            if (!self::columnExists($pdo, $tbl, $col)) {
                return false;
            }
        }

        return true;
    }

    public static function tableExists(PDO $pdo, string $tableName): bool
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
            ");
            $stmt->execute([$tableName]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function columnExists(PDO $pdo, string $tableName, string $columnName): bool
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
            ");
            $stmt->execute([$tableName, $columnName]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function indexExists(PDO $pdo, string $tableName, string $indexName): bool
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
            ");
            $stmt->execute([$tableName, $indexName]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function recordMigration(PDO $pdo, string $migrationName): void
    {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationName, 2]);
        } catch (Throwable $e) {
            error_log("AutoMigrator: Falha ao registar migration {$migrationName}: " . $e->getMessage());
        }
    }
}
