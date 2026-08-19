<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Competency
{
    public static function allWithCategories(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, cat.name as category_name
            FROM competencies c
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            ORDER BY cat.name ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function getForIntern(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, 
                   COALESCE(ic.current_level, 1) as current_level,
                   ic.evidence_notes,
                   ic.evaluated_at,
                   u.name as evaluator_name
            FROM competencies c
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            LEFT JOIN intern_competencies ic ON ic.competency_id = c.id AND ic.intern_id = ?
            LEFT JOIN users u ON u.id = ic.evaluated_by
            ORDER BY cat.name ASC, c.name ASC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll();
    }

    public static function evaluate(int $internId, int $competencyId, int $level, int $evaluatorId, ?string $notes): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO intern_competencies (intern_id, competency_id, current_level, evaluated_by, evidence_notes, evaluated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                current_level = VALUES(current_level),
                evaluated_by = VALUES(evaluated_by),
                evidence_notes = VALUES(evidence_notes),
                evaluated_at = NOW()
        ");
        return $stmt->execute([$internId, $competencyId, $level, $evaluatorId, $notes]);
    }
}
