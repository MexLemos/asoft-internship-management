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
}
