<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\Test;
use App\Services\PerformanceScoringEngine;
use PDO;

class TestsController extends Controller
{
    public function show(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $testId = (int)$id;

        $test = Test::findByIdWithQuestions($testId, true);
        if (!$test) {
            Session::flash('error', 'Teste não encontrado.');
            return $this->redirect('/intern/academy');
        }

        $attempts = Test::getAttemptsForIntern($testId, (int)$intern['id']);
        $canAttempt = count($attempts) < (int)$test['max_attempts'];

        return $this->render('intern.tests.show', [
            'title' => 'Teste: ' . htmlspecialchars($test['title']),
            'test' => $test,
            'attempts' => $attempts,
            'canAttempt' => $canAttempt,
            'intern' => $intern
        ], 'intern');
    }

    public function submit(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $internId = (int)$intern['id'];
        $testId = (int)$id;

        $test = Test::findByIdWithQuestions($testId, false);
        if (!$test) {
            Session::flash('error', 'Teste não encontrado.');
            return $this->redirect('/intern/academy');
        }

        $answersData = (array)$request->input('answers', []);
        $totalPoints = 0.0;
        $earnedPoints = 0.0;
        $processedAnswers = [];

        $pdo = Database::getConnection();

        foreach ($test['questions'] as $q) {
            $qId = (int)$q['id'];
            $qPoints = (float)$q['score_points'];
            $totalPoints += $qPoints;

            $selectedOptionId = isset($answersData[$qId]) ? (int)$answersData[$qId] : null;
            $isCorrect = false;

            if ($selectedOptionId !== null) {
                $stmtCheck = $pdo->prepare("SELECT is_correct FROM question_options WHERE id = ? AND question_id = ?");
                $stmtCheck->execute([$selectedOptionId, $qId]);
                $isCorrect = (bool)$stmtCheck->fetchColumn();
            }

            $scoreEarned = $isCorrect ? $qPoints : 0.0;
            $earnedPoints += $scoreEarned;

            $processedAnswers[] = [
                'question_id' => $qId,
                'selected_option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
                'score_earned' => $scoreEarned
            ];
        }

        $percentage = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100.0 : 0.0;
        $passingScore = (float)$test['passing_score'];
        $status = ($percentage >= $passingScore) ? 'passed' : 'failed';

        $attemptId = Test::recordAttempt($testId, $internId, $earnedPoints, $percentage, $status, $processedAnswers);

        // Recalculate score
        $scoring = new PerformanceScoringEngine();
        $scoring->calculateForIntern($internId);

        AuditLog::log('test_submit', 'tests', $testId, null, [
            'attempt_id' => $attemptId,
            'percentage' => $percentage,
            'status' => $status
        ], 'success');

        if ($status === 'passed') {
            Session::flash('success', "Parabéns! Foi aprovado no teste com " . round($percentage, 1) . "% de aproveitamento.");
        } else {
            Session::flash('warning', "Obteve " . round($percentage, 1) . "%. A nota mínima para aprovação é {$passingScore}%.");
        }

        return $this->redirect("/intern/tests/{$testId}");
    }
}
