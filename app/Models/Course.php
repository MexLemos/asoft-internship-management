<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Course
{
    public static function all(?int $internId = null): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as total_modules,
                   (SELECT COUNT(*) FROM lessons l INNER JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) as total_lessons,
                   (SELECT COUNT(*) FROM learning_contents lc INNER JOIN lessons l ON l.id = lc.lesson_id INNER JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) as total_contents
            FROM courses c
            WHERE c.status = 'published'
            ORDER BY c.order_index ASC
        ");
        $courses = $stmt->fetchAll();

        if ($internId !== null) {
            foreach ($courses as &$c) {
                $c['progress_percentage'] = self::getProgressForIntern((int)$c['id'], $internId);
            }
        }

        return $courses;
    }

    public static function getProgressForIntern(int $courseId, int $internId): float
    {
        $pdo = Database::getConnection();
        
        $stmtTotal = $pdo->prepare("
            SELECT COUNT(lc.id)
            FROM learning_contents lc
            INNER JOIN lessons l ON l.id = lc.lesson_id
            INNER JOIN modules m ON m.id = l.module_id
            WHERE m.course_id = ?
        ");
        $stmtTotal->execute([$courseId]);
        $totalContents = (int)$stmtTotal->fetchColumn();

        if ($totalContents === 0) {
            return 0.0;
        }

        $stmtCompleted = $pdo->prepare("
            SELECT COUNT(lp.id)
            FROM lesson_progress lp
            INNER JOIN learning_contents lc ON lc.id = lp.content_id
            INNER JOIN lessons l ON l.id = lc.lesson_id
            INNER JOIN modules m ON m.id = l.module_id
            WHERE m.course_id = ? AND lp.intern_id = ? AND lp.status = 'completed'
        ");
        $stmtCompleted->execute([$courseId, $internId]);
        $completedContents = (int)$stmtCompleted->fetchColumn();

        return round(($completedContents / $totalContents) * 100, 1);
    }

    public static function getMandatoryStatsForIntern(int $internId): array
    {
        $pdo = Database::getConnection();
        
        $stmtMandatory = $pdo->query("SELECT id, title FROM courses WHERE is_mandatory = 1 AND status = 'published'");
        $mandatoryCourses = $stmtMandatory->fetchAll();

        $totalMandatory = count($mandatoryCourses);
        if ($totalMandatory === 0) {
            return ['percentage' => 100.0, 'completed' => 0, 'total' => 0];
        }

        $totalProgressSum = 0.0;
        $completedCoursesCount = 0;

        foreach ($mandatoryCourses as $mc) {
            $prog = self::getProgressForIntern((int)$mc['id'], $internId);
            $totalProgressSum += $prog;
            if ($prog >= 100.0) {
                $completedCoursesCount++;
            }
        }

        $overallMandatoryProgress = round($totalProgressSum / $totalMandatory, 1);

        return [
            'percentage' => $overallMandatoryProgress,
            'completed' => $completedCoursesCount,
            'total' => $totalMandatory
        ];
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

        $course['progress_percentage'] = self::getProgressForIntern($id, $internId);

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
