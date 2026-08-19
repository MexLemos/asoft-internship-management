<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class AttendanceAttempt
{
    public static function log(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO attendance_attempts (
                intern_id, type, attempt_time, latitude, longitude,
                accuracy, distance_meters, is_within_radius, status,
                failure_reason, ip_address, user_agent, device_fingerprint
            ) VALUES (
                ?, ?, NOW(), ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $data['intern_id'],
            $data['type'],
            $data['latitude'],
            $data['longitude'],
            $data['accuracy'] ?? null,
            $data['distance_meters'],
            $data['is_within_radius'] ? 1 : 0,
            $data['status'],
            $data['failure_reason'] ?? null,
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
            $data['device_fingerprint'] ?? null
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function getRecentSuspicious(int $limit = 20): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT att.*, i.full_name, i.internship_code
            FROM attendance_attempts att
            INNER JOIN interns i ON i.id = att.intern_id
            WHERE att.status != 'success'
            ORDER BY att.attempt_time DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
