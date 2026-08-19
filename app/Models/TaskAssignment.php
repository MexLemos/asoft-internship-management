<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class TaskAssignment
{
    public static function getForIntern(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT ta.*, t.title, t.description, t.objective, t.instructions, t.priority, t.points, t.estimated_hours, t.requires_github,
                   c.name as category_name, c.color_badge,
                   u.name as assigned_by_name,
                   rev.name as reviewer_name
            FROM task_assignments ta
            INNER JOIN tasks t ON t.id = ta.task_id
            INNER JOIN task_categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = ta.assigned_by
            LEFT JOIN users rev ON rev.id = ta.reviewed_by
            WHERE ta.intern_id = ?
            ORDER BY ta.due_date ASC, ta.id DESC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT ta.*, t.title, t.description, t.objective, t.instructions, t.priority, t.points, t.estimated_hours, t.requires_github, t.evaluation_criteria,
                   c.name as category_name, c.color_badge,
                   i.full_name as intern_name, i.internship_code, i.user_id as intern_user_id,
                   u.name as assigned_by_name,
                   rev.name as reviewer_name
            FROM task_assignments ta
            INNER JOIN tasks t ON t.id = ta.task_id
            INNER JOIN task_categories c ON c.id = t.category_id
            INNER JOIN interns i ON i.id = ta.intern_id
            INNER JOIN users u ON u.id = ta.assigned_by
            LEFT JOIN users rev ON rev.id = ta.reviewed_by
            WHERE ta.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        if ($res) {
            $res['submissions'] = self::getSubmissions((int)$res['id']);
            $res['comments'] = self::getComments((int)$res['id']);
        }
        return $res ?: null;
    }

    public static function assign(int $taskId, int $internId, int $assignedBy, string $startDate, string $dueDate): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO task_assignments (task_id, intern_id, assigned_by, start_date, due_date, status)
            VALUES (?, ?, ?, ?, ?, 'assigned')
        ");
        $stmt->execute([$taskId, $internId, $assignedBy, $startDate, $dueDate]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateStatus(int $assignmentId, string $status): bool
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE task_assignments SET status = ?";
        if ($status === 'in_progress') {
            $sql .= ", started_at = COALESCE(started_at, NOW())";
        } elseif ($status === 'approved' || $status === 'submitted') {
            $sql .= ", completed_at = NOW()";
        }
        $sql .= " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$status, $assignmentId]);
    }

    public static function evaluate(int $assignmentId, int $reviewerId, string $status, float $score, ?string $feedback): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE task_assignments
            SET status = ?, score = ?, supervisor_feedback = ?, reviewed_by = ?, reviewed_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$status, $score, $feedback, $reviewerId, $assignmentId]);
    }

    public static function getSubmissions(int $assignmentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT ts.*, i.full_name as intern_name
            FROM task_submissions ts
            INNER JOIN interns i ON i.id = ts.intern_id
            WHERE ts.assignment_id = ?
            ORDER BY ts.submitted_at DESC
        ");
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public static function getComments(int $assignmentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT tc.*, u.name as user_name, u.avatar
            FROM task_comments tc
            INNER JOIN users u ON u.id = tc.user_id
            WHERE tc.assignment_id = ?
            ORDER BY tc.created_at ASC
        ");
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public static function addComment(int $assignmentId, int $userId, string $comment): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO task_comments (assignment_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$assignmentId, $userId, $comment]);
        return (int)$pdo->lastInsertId();
    }

    public static function addSubmission(int $assignmentId, int $internId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO task_submissions (
                assignment_id, intern_id, notes, github_repo_url,
                github_branch, github_commit_hash, github_pr_url, submitted_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, NOW()
            )
        ");
        $stmt->execute([
            $assignmentId,
            $internId,
            $data['notes'] ?? null,
            $data['github_repo_url'] ?? null,
            $data['github_branch'] ?? null,
            $data['github_commit_hash'] ?? null,
            $data['github_pr_url'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }
}
