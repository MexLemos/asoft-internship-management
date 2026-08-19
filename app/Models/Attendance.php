<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Attendance
{
    public static function getForIntern(int $internId, int $limit = 30): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM attendance 
            WHERE intern_id = ? 
            ORDER BY date DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $internId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getTodayForIntern(int $internId): ?array
    {
        $pdo = Database::getConnection();
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE intern_id = ? AND date = ? LIMIT 1");
        $stmt->execute([$internId, $today]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function recordCheckIn(int $internId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO attendance (
                intern_id, date, check_in_time, check_in_lat, check_in_lng,
                check_in_accuracy, check_in_distance_meters, check_in_ip, check_in_device, check_in_status, status
            ) VALUES (
                ?, CURDATE(), CURTIME(), ?, ?,
                ?, ?, ?, ?, ?, 'present'
            )
            ON DUPLICATE KEY UPDATE
                check_in_time = CURTIME(),
                check_in_lat = VALUES(check_in_lat),
                check_in_lng = VALUES(check_in_lng),
                check_in_accuracy = VALUES(check_in_accuracy),
                check_in_distance_meters = VALUES(check_in_distance_meters),
                check_in_ip = VALUES(check_in_ip),
                check_in_device = VALUES(check_in_device),
                check_in_status = VALUES(check_in_status),
                status = 'present'
        ");

        $stmt->execute([
            $internId,
            $data['lat'],
            $data['lng'],
            $data['accuracy'] ?? null,
            $data['distance_meters'],
            $data['ip'] ?? null,
            $data['device'] ?? null,
            $data['status'] ?? 'on_time'
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function recordCheckOut(int $internId, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE attendance
            SET check_out_time = CURTIME(),
                check_out_lat = ?,
                check_out_lng = ?,
                check_out_accuracy = ?,
                check_out_distance_meters = ?,
                check_out_ip = ?,
                check_out_device = ?,
                check_out_status = ?,
                hours_worked = ROUND(TIMESTAMPDIFF(MINUTE, CONCAT(date, ' ', check_in_time), NOW()) / 60, 2)
            WHERE intern_id = ? AND date = CURDATE()
        ");

        return $stmt->execute([
            $data['lat'],
            $data['lng'],
            $data['accuracy'] ?? null,
            $data['distance_meters'],
            $data['ip'] ?? null,
            $data['device'] ?? null,
            $data['status'] ?? 'normal',
            $internId
        ]);
    }

    public static function getStats(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                COUNT(CASE WHEN status = 'justified_absence' THEN 1 END) as justified_count,
                COUNT(CASE WHEN check_in_status = 'late' THEN 1 END) as late_count,
                COALESCE(SUM(hours_worked), 0) as total_hours_worked
            FROM attendance 
            WHERE intern_id = ?
        ");
        $stmt->execute([$internId]);
        return $stmt->fetch() ?: [
            'present_count' => 0,
            'absent_count' => 0,
            'justified_count' => 0,
            'late_count' => 0,
            'total_hours_worked' => 0
        ];
    }
}
