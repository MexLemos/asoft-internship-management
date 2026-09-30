<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class InternStatusHistory
{
    /**
     * Records a status transition in the history table.
     */
    public static function log(
        int $internId,
        string $fromStatus,
        string $toStatus,
        int $changedBy,
        string $reason,
        ?array $metadata = null
    ): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO intern_status_history (intern_id, from_status, to_status, changed_by, reason, metadata)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $internId,
            $fromStatus,
            $toStatus,
            $changedBy,
            $reason,
            $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Gets the full status history for an intern, ordered chronologically.
     */
    public static function getHistoryForIntern(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT h.*, u.name as changer_name, u.email as changer_email
            FROM intern_status_history h
            INNER JOIN users u ON u.id = h.changed_by
            WHERE h.intern_id = ?
            ORDER BY h.created_at DESC, h.id DESC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a single history record by ID.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT h.*, u.name as changer_name, u.email as changer_email
            FROM intern_status_history h
            INNER JOIN users u ON u.id = h.changed_by
            WHERE h.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
