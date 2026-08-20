<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class AuditLog
{
    public static function log(string $action, string $module, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null, string $result = 'success'): void
    {
        $pdo = Database::getConnection();
        $user = \App\Core\Session::get('user');
        $userId = $user['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, module, record_id, old_values, new_values, ip_address, user_agent, result)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $action,
            $module,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ip,
            $ua,
            $result
        ]);
    }

    public static function getRecent(int $limit = 50): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getUnreadNotificationsCount(int $userId): int
    {
        return Notification::getUnreadCount($userId);
    }
}
