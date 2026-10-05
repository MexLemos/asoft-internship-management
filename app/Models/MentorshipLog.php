<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class MentorshipLog
{
    public const TYPES = [
        '1_on_1' => 'Reunião 1-on-1 de Orientação',
        'periodic_review' => 'Acompanhamento Periódico',
        'technical_orientation' => 'Orientação Técnica / Discussão',
        'feedback' => 'Feedback Contínuo de Desempenho',
        'disciplinary' => 'Alinhamento Disciplinar',
        'other' => 'Outro Acompanhamento'
    ];

    /**
     * Creates a new mentorship/supervision log entry.
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO intern_mentorship_logs (
                intern_id, supervisor_id, session_date, session_type, title,
                summary, topics_discussed, action_items, rating, is_private
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            (int)$data['intern_id'],
            (int)$data['supervisor_id'],
            $data['session_date'] ?? date('Y-m-d H:i:s'),
            $data['session_type'] ?? '1_on_1',
            trim((string)$data['title']),
            trim((string)$data['summary']),
            !empty($data['topics_discussed']) ? trim((string)$data['topics_discussed']) : null,
            !empty($data['action_items']) ? trim((string)$data['action_items']) : null,
            !empty($data['rating']) ? (int)$data['rating'] : null,
            !empty($data['is_private']) ? 1 : 0
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Updates an existing mentorship log.
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE intern_mentorship_logs SET
                session_date = ?,
                session_type = ?,
                title = ?,
                summary = ?,
                topics_discussed = ?,
                action_items = ?,
                rating = ?,
                is_private = ?
            WHERE id = ? AND deleted_at IS NULL
        ");

        return $stmt->execute([
            $data['session_date'] ?? date('Y-m-d H:i:s'),
            $data['session_type'] ?? '1_on_1',
            trim((string)$data['title']),
            trim((string)$data['summary']),
            !empty($data['topics_discussed']) ? trim((string)$data['topics_discussed']) : null,
            !empty($data['action_items']) ? trim((string)$data['action_items']) : null,
            !empty($data['rating']) ? (int)$data['rating'] : null,
            !empty($data['is_private']) ? 1 : 0,
            $id
        ]);
    }

    /**
     * Soft-deletes a mentorship record.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE intern_mentorship_logs SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Finds a single mentorship log entry by ID.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   i.full_name as intern_name, 
                   i.internship_code,
                   sup.name as supervisor_name,
                   sup.email as supervisor_email
            FROM intern_mentorship_logs m
            INNER JOIN interns i ON i.id = m.intern_id
            INNER JOIN users sup ON sup.id = m.supervisor_id
            WHERE m.id = ? AND m.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Retrieves mentorship sessions for a given intern.
     */
    public static function getForIntern(int $internId, bool $includePrivate = true): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT m.*, 
                   sup.name as supervisor_name,
                   sup.email as supervisor_email,
                   sup.avatar as supervisor_avatar
            FROM intern_mentorship_logs m
            INNER JOIN users sup ON sup.id = m.supervisor_id
            WHERE m.intern_id = ? AND m.deleted_at IS NULL
        ";

        if (!$includePrivate) {
            $sql .= " AND m.is_private = 0";
        }

        $sql .= " ORDER BY m.session_date DESC, m.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$internId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves mentorship sessions conducted by a specific supervisor.
     */
    public static function getForSupervisor(int $supervisorId, int $limit = 50): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   i.full_name as intern_name,
                   i.internship_code,
                   i.photo as intern_photo
            FROM intern_mentorship_logs m
            INNER JOIN interns i ON i.id = m.intern_id
            WHERE m.supervisor_id = ? AND m.deleted_at IS NULL
            ORDER BY m.session_date DESC, m.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $supervisorId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves recent mentorship logs across the company.
     */
    public static function getRecent(int $limit = 10): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   i.full_name as intern_name,
                   sup.name as supervisor_name
            FROM intern_mentorship_logs m
            INNER JOIN interns i ON i.id = m.intern_id
            INNER JOIN users sup ON sup.id = m.supervisor_id
            WHERE m.deleted_at IS NULL
            ORDER BY m.session_date DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all mentorship logs across the company or filtered by intern/supervisor.
     */
    public static function getAll(int $limit = 100, ?int $internId = null, ?int $supervisorId = null): array
    {
        $pdo = Database::getConnection();
        $conditions = ["m.deleted_at IS NULL"];
        $params = [];

        if ($internId !== null && $internId > 0) {
            $conditions[] = "m.intern_id = ?";
            $params[] = $internId;
        }

        if ($supervisorId !== null && $supervisorId > 0) {
            $conditions[] = "m.supervisor_id = ?";
            $params[] = $supervisorId;
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT m.*, 
                   i.full_name as intern_name,
                   i.internship_code,
                   i.photo as intern_photo,
                   sup.name as supervisor_name,
                   sup.email as supervisor_email
            FROM intern_mentorship_logs m
            INNER JOIN interns i ON i.id = m.intern_id
            INNER JOIN users sup ON sup.id = m.supervisor_id
            WHERE {$where}
            ORDER BY m.session_date DESC, m.id DESC
            LIMIT ?
        ";

        $stmt = $pdo->prepare($sql);
        $idx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($idx++, $p, PDO::PARAM_INT);
        }
        $stmt->bindValue($idx, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves high-level statistics about mentorship sessions across the company.
     */
    public static function getStats(?int $supervisorId = null): array
    {
        $pdo = Database::getConnection();
        $where = "deleted_at IS NULL";
        $params = [];

        if ($supervisorId !== null && $supervisorId > 0) {
            $where .= " AND supervisor_id = ?";
            $params[] = $supervisorId;
        }

        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_sessions,
                COUNT(DISTINCT intern_id) as total_interns_mentored,
                COUNT(DISTINCT supervisor_id) as total_supervisors,
                AVG(CASE WHEN rating IS NOT NULL THEN rating END) as avg_rating,
                SUM(CASE WHEN session_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as sessions_last_30_days
            FROM intern_mentorship_logs
            WHERE {$where}
        ");
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_sessions' => (int)($res['total_sessions'] ?? 0),
            'total_interns_mentored' => (int)($res['total_interns_mentored'] ?? 0),
            'total_supervisors' => (int)($res['total_supervisors'] ?? 0),
            'avg_rating' => $res['avg_rating'] !== null ? round((float)$res['avg_rating'], 1) : null,
            'sessions_last_30_days' => (int)($res['sessions_last_30_days'] ?? 0),
        ];
    }
}

