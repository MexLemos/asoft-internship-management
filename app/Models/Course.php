<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Course
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as total_modules,
                   (SELECT COUNT(*) FROM lessons l INNER JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) as total_lessons
            FROM courses c
            WHERE c.status = 'published'
            ORDER BY c.order_index ASC
        ");
        return $stmt->fetchAll();
    }

    public static function findWithModules(int $id, int $internId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $course = $stmt->fetch();
        if (!$course) {
            return null;
        }

        // Get modules
        $stmtMod = $pdo->prepare("
            SELECT m.*, 
                   (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) as total_lessons,
                   (SELECT t.id FROM tests t WHERE t.module_id = m.id LIMIT 1) as test_id
            FROM modules m
            WHERE m.course_id = ?
            ORDER BY m.order_index ASC
        ");
        $stmtMod->execute([$id]);
        $modules = $stmtMod->fetchAll();

        foreach ($modules as &$mod) {
            $stmtLes = $pdo->prepare("
                SELECT l.*, 
                       (SELECT COUNT(*) FROM learning_contents lc WHERE lc.lesson_id = l.id) as total_contents
                FROM lessons l
                WHERE l.module_id = ?
                ORDER BY l.order_index ASC
            ");
            $stmtLes->execute([$mod['id']]);
            $mod['lessons'] = $stmtLes->fetchAll();

            foreach ($mod['lessons'] as &$les) {
                $stmtCont = $pdo->prepare("
                    SELECT lc.*, lp.status as progress_status, lp.watch_percentage
                    FROM learning_contents lc
                    LEFT JOIN lesson_progress lp ON lp.content_id = lc.id AND lp.intern_id = ?
                    WHERE lc.lesson_id = ?
                    ORDER BY lc.order_index ASC
                ");
                $stmtCont->execute([$internId, $les['id']]);
                $les['contents'] = $stmtCont->fetchAll();
            }
        }

        $course['modules'] = $modules;
        return $course;
    }
}
