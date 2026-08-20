<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Notification
{
    public static function create(int $userId, string $type, string $title, string $message, ?string $actionUrl = null): bool
    {
        $pdo = Database::getConnection();
        // Allowed enum types: 'info','warning','danger','success'
        $validTypes = ['info', 'warning', 'danger', 'success'];
        $enumType = in_array($type, $validTypes, true) ? $type : 'info';

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, action_url, read_at, created_at)
            VALUES (?, ?, ?, ?, ?, NULL, NOW())
        ");
        return $stmt->execute([$userId, $enumType, $title, $message, $actionUrl]);
    }

    public static function getUnreadCount(int $userId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function getForUser(int $userId, int $limit = 30): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public static function markAllAsRead(int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
        return $stmt->execute([$userId]);
    }
}
