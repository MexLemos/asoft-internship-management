<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Task
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT t.*, c.name as category_name, c.color_badge, u.name as creator_name,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.task_id = t.id) as total_assigned,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.task_id = t.id AND ta.status = 'approved') as total_approved
            FROM tasks t
            INNER JOIN task_categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.created_by
            WHERE t.deleted_at IS NULL
            ORDER BY t.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, c.name as category_name, c.color_badge, u.name as creator_name
            FROM tasks t
            INNER JOIN task_categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.created_by
            WHERE t.id = ? AND t.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                category_id, created_by, title, description, objective,
                instructions, priority, points, estimated_hours,
                evaluation_criteria, requires_github, status
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?
            )
        ");

        $stmt->execute([
            $data['category_id'],
            $data['created_by'],
            $data['title'],
            $data['description'],
            $data['objective'] ?? null,
            $data['instructions'] ?? null,
            $data['priority'] ?? 'medium',
            $data['points'] ?? 100,
            $data['estimated_hours'] ?? 4.00,
            $data['evaluation_criteria'] ?? null,
            !empty($data['requires_github']) ? 1 : 0,
            $data['status'] ?? 'published'
        ]);

        return (int)$pdo->lastInsertId();
    }
}
