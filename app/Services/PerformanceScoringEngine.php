<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Attendance;
use App\Models\Intern;
use App\Models\SystemSetting;
use PDO;

class PerformanceScoringEngine
{
    /**
     * Calculates the weighted performance score based strictly on real activity data.
     * When an intern has not yet performed any actions (no attendance, no tasks, no tests, etc.),
     * all component scores and the overall score return 0.00.
     */
    public function calculateForIntern(int $internId): array
    {
        $pdo = Database::getConnection();

        // 1. Get Weights from Settings
        $wAtt   = (int)SystemSetting::get('weight_attendance', 20);
        $wTasks = (int)SystemSetting::get('weight_tasks', 30);
        $wTests = (int)SystemSetting::get('weight_tests', 20);
        $wComp  = (int)SystemSetting::get('weight_competencies', 15);
        $wBehav = (int)SystemSetting::get('weight_behavior', 10);
        $wFinal = (int)SystemSetting::get('weight_final_eval', 5);

        // Normalize weights total
        $totalWeight = $wAtt + $wTasks + $wTests + $wComp + $wBehav + $wFinal;
        if ($totalWeight <= 0) {
            $totalWeight = 100;
        }

        // 2. Component 1: Attendance (Presença Real)
        $attStats = Attendance::getStats($internId);
        $presentCount = (int)($attStats['present_count'] ?? 0);
        $absentCount  = (int)($attStats['absent_count'] ?? 0);
        $totalDays    = $presentCount + $absentCount;
        $attScore     = ($totalDays > 0) ? round(($presentCount / $totalDays) * 100.0, 1) : 0.00;

        // 3. Component 2: Tasks (Tarefas Práticas Reais)
        $stmtTasks = $pdo->prepare("
            SELECT COUNT(*) as total_assigned,
                   COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                   AVG(CASE WHEN status = 'approved' THEN score END) as avg_score
            FROM task_assignments
            WHERE intern_id = ?
        ");
        $stmtTasks->execute([$internId]);
        $taskStats = $stmtTasks->fetch(PDO::FETCH_ASSOC);
        $totalAssigned = (int)($taskStats['total_assigned'] ?? 0);
        $approvedCount = (int)($taskStats['approved_count'] ?? 0);
        $avgScore      = (float)($taskStats['avg_score'] ?? 0);

        $taskScore = ($totalAssigned > 0 && $approvedCount > 0)
            ? round($avgScore * ($approvedCount / $totalAssigned), 1)
            : 0.00;

        // 4. Component 3: Tests (Testes / Quizzes Reais Aprovados)
        $stmtTests = $pdo->prepare("
            SELECT AVG(percentage) as avg_percentage, COUNT(*) as attempts_count 
            FROM test_attempts 
            WHERE intern_id = ? AND status = 'passed'
        ");
        $stmtTests->execute([$internId]);
        $testRow = $stmtTests->fetch(PDO::FETCH_ASSOC);
        $testAttempts = (int)($testRow['attempts_count'] ?? 0);
        $testScore = ($testAttempts > 0 && $testRow['avg_percentage'] !== null)
            ? round((float)$testRow['avg_percentage'], 1)
            : 0.00;

        // 5. Component 4: Competencies (Competências Técnicas Avaliadas, Escala 1 a 5 -> 0 a 100)
        $stmtComp = $pdo->prepare("
            SELECT AVG(current_level) as avg_level, COUNT(*) as comp_count 
            FROM intern_competencies 
            WHERE intern_id = ?
        ");
        $stmtComp->execute([$internId]);
        $compRow = $stmtComp->fetch(PDO::FETCH_ASSOC);
        $compCount = (int)($compRow['comp_count'] ?? 0);
        $compScore = ($compCount > 0 && $compRow['avg_level'] !== null)
            ? round(((float)$compRow['avg_level'] / 5.0) * 100.0, 1)
            : 0.00;

        // 6. Component 5: Behavior & Soft Skills (Competências Comportamentais)
        $stmtBehav = $pdo->prepare("
            SELECT AVG(ic.current_level) as avg_level, COUNT(*) as behav_count
            FROM intern_competencies ic
            INNER JOIN competencies c ON c.id = ic.competency_id
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            WHERE ic.intern_id = ? AND cat.name = 'Comportamentais'
        ");
        $stmtBehav->execute([$internId]);
        $behavRow = $stmtBehav->fetch(PDO::FETCH_ASSOC);
        $behavCount = (int)($behavRow['behav_count'] ?? 0);
        $behavScore = ($behavCount > 0 && $behavRow['avg_level'] !== null)
            ? round(((float)$behavRow['avg_level'] / 5.0) * 100.0, 1)
            : 0.00;

        // 7. Component 6: Final Evaluation (Avaliação Final Concluída)
        $stmtFinal = $pdo->prepare("SELECT average_score, status FROM final_evaluations WHERE intern_id = ?");
        $stmtFinal->execute([$internId]);
        $finalRow = $stmtFinal->fetch(PDO::FETCH_ASSOC);
        $finalScore = ($finalRow && $finalRow['average_score'] !== null)
            ? round(((float)$finalRow['average_score'] / 5.0) * 100.0, 1)
            : 0.00;

        // Check if there is any real activity or evaluation for this intern
        $hasAnyActivity = ($totalDays > 0 || $totalAssigned > 0 || $testAttempts > 0 || $compCount > 0 || $behavCount > 0 || !empty($finalRow));

        if (!$hasAnyActivity) {
            $overallScore = 0.00;
            $riskLevel = 'normal';
        } else {
            $weightedTotal = (
                ($attScore * $wAtt) +
                ($taskScore * $wTasks) +
                ($testScore * $wTests) +
                ($compScore * $wComp) +
                ($behavScore * $wBehav) +
                ($finalScore * $wFinal)
            ) / $totalWeight;

            $overallScore = round($weightedTotal, 2);

            // Determine Risk Level based strictly on real activity
            $minAttendance = (int)SystemSetting::get('min_attendance_percentage', 80);
            $minGrade      = (int)SystemSetting::get('min_passing_grade', 60);

            $riskLevel = 'normal';
            if (($totalDays >= 5 && $attScore < ($minAttendance - 10)) || ($overallScore > 0 && $overallScore < ($minGrade - 10)) || $absentCount >= 5) {
                $riskLevel = 'risk';
            } elseif (($totalDays >= 3 && $attScore < $minAttendance) || ($overallScore > 0 && $overallScore < $minGrade) || $absentCount >= 3) {
                $riskLevel = 'attention';
            }
        }

        // Update Intern Record with real numbers
        $stmtUpd = $pdo->prepare("UPDATE interns SET overall_score = ?, risk_level = ? WHERE id = ?");
        $stmtUpd->execute([$overallScore, $riskLevel, $internId]);

        $components = [
            'attendance'   => ['score' => $attScore,   'weight' => $wAtt,   'label' => 'Presença / Frequência'],
            'tasks'        => ['score' => $taskScore,  'weight' => $wTasks,  'label' => 'Tarefas Práticas'],
            'tests'        => ['score' => $testScore,  'weight' => $wTests,  'label' => 'Testes & Quizzes'],
            'competencies' => ['score' => $compScore,  'weight' => $wComp,  'label' => 'Competências'],
            'behavior'     => ['score' => $behavScore, 'weight' => $wBehav, 'label' => 'Comportamento'],
            'final_eval'   => ['score' => $finalScore, 'weight' => $wFinal, 'label' => 'Avaliação Final']
        ];

        return [
            'overall_score' => $overallScore,
            'risk_level'    => $riskLevel,
            'components'    => $components,
            'breakdown'     => $components
        ];
    }
}
