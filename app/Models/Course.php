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

    public static function allAdmin(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as total_modules,
                   (SELECT COUNT(*) FROM lessons l INNER JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) as total_lessons,
                   (SELECT COUNT(*) FROM learning_contents lc INNER JOIN lessons l ON l.id = lc.lesson_id INNER JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) as total_contents
            FROM courses c
            ORDER BY c.order_index ASC, c.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $course = $stmt->fetch();
        return $course ?: null;
    }

    public static function findWithStructure(int $id): ?array
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
                   (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) as total_lessons
            FROM modules m
            WHERE m.course_id = ?
            ORDER BY m.order_index ASC, m.id ASC
        ");
        $stmtMod->execute([$id]);
        $modules = $stmtMod->fetchAll();

        foreach ($modules as &$mod) {
            $stmtLes = $pdo->prepare("
                SELECT l.*, 
                       (SELECT COUNT(*) FROM learning_contents lc WHERE lc.lesson_id = l.id) as total_contents
                FROM lessons l
                WHERE l.module_id = ?
                ORDER BY l.order_index ASC, l.id ASC
            ");
            $stmtLes->execute([$mod['id']]);
            $mod['lessons'] = $stmtLes->fetchAll();

            foreach ($mod['lessons'] as &$les) {
                $stmtCont = $pdo->prepare("
                    SELECT lc.*
                    FROM learning_contents lc
                    WHERE lc.lesson_id = ?
                    ORDER BY lc.order_index ASC, lc.id ASC
                ");
                $stmtCont->execute([$les['id']]);
                $les['contents'] = $stmtCont->fetchAll();
            }
        }

        $course['modules'] = $modules;
        return $course;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $slug = self::generateSlug($data['title']);

        $stmt = $pdo->prepare("
            INSERT INTO courses (title, slug, description, cover_image, is_mandatory, status, order_index)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $slug,
            $data['description'] ?? null,
            $data['cover_image'] ?? null,
            !empty($data['is_mandatory']) ? 1 : 0,
            $data['status'] ?? 'published',
            (int)($data['order_index'] ?? 1)
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $slug = self::generateSlug($data['title']);

        $stmt = $pdo->prepare("
            UPDATE courses
            SET title = ?, slug = ?, description = ?, is_mandatory = ?, status = ?, order_index = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $slug,
            $data['description'] ?? null,
            !empty($data['is_mandatory']) ? 1 : 0,
            $data['status'] ?? 'published',
            (int)($data['order_index'] ?? 1),
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function addModule(int $courseId, string $title, ?string $description = null, int $orderIndex = 1): int
    {
        $pdo = Database::getConnection();
        $slug = self::generateSlug($title);
        $stmt = $pdo->prepare("
            INSERT INTO modules (course_id, title, slug, description, order_index)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$courseId, $title, $slug, $description, $orderIndex]);
        return (int)$pdo->lastInsertId();
    }

    public static function deleteModule(int $moduleId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
        return $stmt->execute([$moduleId]);
    }

    public static function addLesson(int $moduleId, string $title, int $orderIndex = 1): int
    {
        $pdo = Database::getConnection();
        $slug = self::generateSlug($title);
        $stmt = $pdo->prepare("
            INSERT INTO lessons (module_id, title, slug, order_index)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$moduleId, $title, $slug, $orderIndex]);
        return (int)$pdo->lastInsertId();
    }

    public static function deleteLesson(int $lessonId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        return $stmt->execute([$lessonId]);
    }

    public static function addContent(
        int $lessonId,
        string $title,
        string $contentType,
        string $urlOrPath,
        ?string $articleBody = null,
        int $durationMinutes = 10,
        int $orderIndex = 1
    ): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO learning_contents (lesson_id, title, content_type, content_url_or_path, duration_minutes, article_body, order_index)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$lessonId, $title, $contentType, $urlOrPath, $durationMinutes, $articleBody, $orderIndex]);
        return (int)$pdo->lastInsertId();
    }

    public static function deleteContent(int $contentId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM learning_contents WHERE id = ?");
        return $stmt->execute([$contentId]);
    }

    private static function generateSlug(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        return $slug ?: 'curso-' . bin2hex(random_bytes(3));
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
