<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $user['roles'] = self::getUserRoles((int)$user['id']);
            $user['permissions'] = self::getUserPermissions((int)$user['id']);
        }
        return $user ?: null;
    }

    public static function findByEmailOrUsername(string $identifier): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (email = ? OR username = ?) AND deleted_at IS NULL 
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        if ($user) {
            $user['roles'] = self::getUserRoles((int)$user['id']);
            $user['permissions'] = self::getUserPermissions((int)$user['id']);
        }
        return $user ?: null;
    }

    public static function getUserRoles(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT r.name 
            FROM roles r 
            INNER JOIN user_roles ur ON ur.role_id = r.id 
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getUserPermissions(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.slug 
            FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            INNER JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function updateLastLogin(int $userId, string $ip): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users 
            SET last_login_at = NOW(), last_login_ip = ?, login_attempts = 0, locked_until = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$ip, $userId]);
    }

    public static function incrementFailedAttempts(int $userId, int $maxAttempts = 5, int $lockoutMinutes = 15): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT login_attempts FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $attempts = (int)$stmt->fetchColumn() + 1;

        if ($attempts >= $maxAttempts) {
            $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$lockoutMinutes} minutes"));
            $upd = $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?");
            $upd->execute([$attempts, $lockedUntil, $userId]);
        } else {
            $upd = $pdo->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
            $upd->execute([$attempts, $userId]);
        }
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT u.*, GROUP_CONCAT(r.display_name SEPARATOR ', ') as roles_display
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Retorna apenas utilizadores com perfis de funcionários (super_admin, admin, supervisor).
     */
    public static function getEmployees(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT u.id, u.name, u.email, u.phone, u.avatar, u.username, u.status, u.last_login_at,
                   GROUP_CONCAT(r.display_name SEPARATOR ', ') as roles_display,
                   GROUP_CONCAT(r.name SEPARATOR ',') as roles_slugs,
                   MIN(r.id) as primary_role_id
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL 
              AND r.name IN ('super_admin', 'admin', 'supervisor')
            GROUP BY u.id, u.name, u.email, u.phone, u.avatar, u.username, u.status, u.last_login_at
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Cadastra um novo funcionário com perfil e senha segura.
     */
    public static function createEmployee(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, username, password_hash, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['username'],
                $passwordHash,
                $data['status'] ?? 'active'
            ]);
            $userId = (int)$pdo->lastInsertId();

            $roleId = (int)($data['role_id'] ?? 3); // 3 = supervisor por defeito
            $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmtRole->execute([$userId, $roleId]);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza dados de um funcionário.
     */
    public static function updateEmployee(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            if (!empty($data['password'])) {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET name = ?, email = ?, phone = ?, username = ?, status = ?, password_hash = ?
                    WHERE id = ? AND deleted_at IS NULL
                ");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    $data['username'],
                    $data['status'] ?? 'active',
                    password_hash($data['password'], PASSWORD_BCRYPT),
                    $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET name = ?, email = ?, phone = ?, username = ?, status = ?
                    WHERE id = ? AND deleted_at IS NULL
                ");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    $data['username'],
                    $data['status'] ?? 'active',
                    $id
                ]);
            }

            if (!empty($data['role_id'])) {
                $delRole = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
                $delRole->execute([$id]);

                $insRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $insRole->execute([$id, (int)$data['role_id']]);
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Alterna o estado do funcionário (active <-> blocked).
     */
    public static function toggleStatus(int $id): string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $curr = $stmt->fetchColumn();
        $newStatus = ($curr === 'active') ? 'blocked' : 'active';

        $upd = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $id]);
        return $newStatus;
    }

    /**
     * Soft delete do funcionário.
     */
    public static function deleteEmployee(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Retorna perfis atribuíveis para funcionários.
     */
    public static function getAssignableRoles(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name, display_name, description FROM roles WHERE name IN ('admin', 'supervisor') ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
