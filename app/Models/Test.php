<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Test
{
    public static function findByIdWithQuestions(int $id, bool $hideAnswers = true): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, m.title as module_title, c.title as course_title
            FROM tests t
            INNER JOIN modules m ON m.id = t.module_id
            INNER JOIN courses c ON c.id = m.course_id
            WHERE t.id = ? AND t.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $test = $stmt->fetch();
        if (!$test) {
            return null;
        }

        $stmtQ = $pdo->prepare("
            SELECT * FROM questions 
            WHERE test_id = ? 
            ORDER BY order_index ASC
        ");
        $stmtQ->execute([$id]);
        $questions = $stmtQ->fetchAll();

        foreach ($questions as &$q) {
            $stmtOpt = $pdo->prepare("
                SELECT id, question_id, option_text, " . ($hideAnswers ? "0 as is_correct" : "is_correct") . ", order_index
                FROM question_options 
                WHERE question_id = ? 
                ORDER BY order_index ASC
            ");
            $stmtOpt->execute([$q['id']]);
            $q['options'] = $stmtOpt->fetchAll();
        }

        $test['questions'] = $questions;
        return $test;
    }

    public static function getAttemptsForIntern(int $testId, int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM test_attempts 
            WHERE test_id = ? AND intern_id = ? 
            ORDER BY attempt_number DESC
        ");
        $stmt->execute([$testId, $internId]);
        return $stmt->fetchAll();
    }

    public static function recordAttempt(int $testId, int $internId, float $scoreAchieved, float $percentage, string $status, array $answers): int
    {
        $pdo = Database::getConnection();
        
        // Find next attempt number
        $stmtNum = $pdo->prepare("SELECT COALESCE(MAX(attempt_number), 0) + 1 FROM test_attempts WHERE test_id = ? AND intern_id = ?");
        $stmtNum->execute([$testId, $internId]);
        $nextNum = (int)$stmtNum->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO test_attempts (test_id, intern_id, attempt_number, score_achieved, percentage, status, started_at, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$testId, $internId, $nextNum, $scoreAchieved, $percentage, $status]);
        $attemptId = (int)$pdo->lastInsertId();

        $stmtAns = $pdo->prepare("
            INSERT INTO test_answers (attempt_id, question_id, selected_option_id, text_response, is_correct, score_earned)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($answers as $ans) {
            $stmtAns->execute([
                $attemptId,
                $ans['question_id'],
                $ans['selected_option_id'] ?? null,
                $ans['text_response'] ?? null,
                $ans['is_correct'] ? 1 : 0,
                $ans['score_earned']
            ]);
        }

        return $attemptId;
    }
}
