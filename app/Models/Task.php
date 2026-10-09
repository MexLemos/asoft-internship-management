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

    public static function paginate(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $pdo = Database::getConnection();
        $where = ["t.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = '%' . trim((string)$filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
        }

        $whereClause = implode(" AND ", $where);

        // Count total
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tasks t WHERE {$whereClause}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Allowed sort columns
        $allowedSort = [
            'id' => 't.id',
            'title' => 't.title',
            'points' => 't.points',
            'priority' => 't.priority',
            'due_date' => 't.due_date',
            'created_at' => 't.created_at'
        ];
        $sortKey = $filters['sort'] ?? 'id';
        $sortCol = $allowedSort[$sortKey] ?? 't.id';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $pdo->prepare("
            SELECT t.*, c.name as category_name, c.color_badge, u.name as creator_name,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.task_id = t.id) as total_assigned,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.task_id = t.id AND ta.status = 'approved') as total_approved
            FROM tasks t
            INNER JOIN task_categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.created_by
            WHERE {$whereClause}
            ORDER BY {$sortCol} {$direction}
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int)ceil($total / $perPage)),
            'sort' => $sortKey,
            'direction' => strtolower($direction)
        ];
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                category_id, created_by, title, description, objective,
                instructions, priority, points, estimated_hours, due_date,
                evaluation_criteria, requires_github, status
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
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
            !empty($data['due_date']) ? $data['due_date'] : null,
            $data['evaluation_criteria'] ?? null,
            !empty($data['requires_github']) ? 1 : 0,
            $data['status'] ?? 'published'
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE tasks SET
                category_id = ?,
                title = ?,
                description = ?,
                objective = ?,
                instructions = ?,
                priority = ?,
                points = ?,
                estimated_hours = ?,
                due_date = ?,
                evaluation_criteria = ?,
                requires_github = ?,
                status = ?
            WHERE id = ? AND deleted_at IS NULL
        ");

        return $stmt->execute([
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['objective'] ?? null,
            $data['instructions'] ?? null,
            $data['priority'] ?? 'medium',
            $data['points'] ?? 100,
            $data['estimated_hours'] ?? 4.00,
            !empty($data['due_date']) ? $data['due_date'] : null,
            $data['evaluation_criteria'] ?? null,
            !empty($data['requires_github']) ? 1 : 0,
            $data['status'] ?? 'published',
            $id
        ]);
    }
}
