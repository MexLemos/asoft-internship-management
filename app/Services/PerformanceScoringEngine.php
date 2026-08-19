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
    public function calculateForIntern(int $internId): array
    {
        $pdo = Database::getConnection();

        // 1. Get Weights
        $wAtt = (int)SystemSetting::get('weight_attendance', 20);
        $wTasks = (int)SystemSetting::get('weight_tasks', 30);
        $wTests = (int)SystemSetting::get('weight_tests', 20);
        $wComp = (int)SystemSetting::get('weight_competencies', 15);
        $wBehav = (int)SystemSetting::get('weight_behavior', 10);
        $wFinal = (int)SystemSetting::get('weight_final_eval', 5);

        // Normalize weights total
        $totalWeight = $wAtt + $wTasks + $wTests + $wComp + $wBehav + $wFinal;
        if ($totalWeight <= 0) {
            $totalWeight = 100;
        }

        // 2. Component 1: Attendance
        $attStats = Attendance::getStats($internId);
        $totalDays = $attStats['present_count'] + $attStats['absent_count'];
        $attScore = ($totalDays > 0) ? ($attStats['present_count'] / $totalDays) * 100 : 100.00;

        // 3. Component 2: Tasks
        $stmtTasks = $pdo->prepare("
            SELECT COUNT(*) as total_assigned,
                   COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                   AVG(CASE WHEN status = 'approved' THEN score END) as avg_score
            FROM task_assignments
            WHERE intern_id = ?
        ");
        $stmtTasks->execute([$internId]);
        $taskStats = $stmtTasks->fetch();
        $taskScore = ($taskStats['total_assigned'] > 0)
            ? (float)($taskStats['avg_score'] ?? 0) * ($taskStats['approved_count'] / $taskStats['total_assigned'])
            : 85.00;

        // 4. Component 3: Tests
        $stmtTests = $pdo->prepare("SELECT AVG(percentage) as avg_percentage FROM test_attempts WHERE intern_id = ? AND status = 'passed'");
        $stmtTests->execute([$internId]);
        $testScore = (float)($stmtTests->fetchColumn() ?: 80.00);

        // 5. Component 4: Competencies (1 to 5 scale -> convert to 0 to 100)
        $stmtComp = $pdo->prepare("SELECT AVG(current_level) as avg_level FROM intern_competencies WHERE intern_id = ?");
        $stmtComp->execute([$internId]);
        $avgLevel = (float)($stmtComp->fetchColumn() ?: 3.5);
        $compScore = ($avgLevel / 5.0) * 100.00;

        // 6. Component 5: Behavior & Soft Skills
        $stmtBehav = $pdo->prepare("
            SELECT AVG(ic.current_level) 
            FROM intern_competencies ic
            INNER JOIN competencies c ON c.id = ic.competency_id
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            WHERE ic.intern_id = ? AND cat.name = 'Comportamentais'
        ");
        $stmtBehav->execute([$internId]);
        $avgBehav = (float)($stmtBehav->fetchColumn() ?: 4.0);
        $behavScore = ($avgBehav / 5.0) * 100.00;

        // 7. Component 6: Final Evaluation
        $stmtFinal = $pdo->prepare("SELECT average_score FROM final_evaluations WHERE intern_id = ?");
        $stmtFinal->execute([$internId]);
        $finalAvg = (float)($stmtFinal->fetchColumn() ?: 4.0);
        $finalScore = ($finalAvg / 5.0) * 100.00;

        // Weighted Overall Calculation
        $weightedTotal = (
            ($attScore * $wAtt) +
            ($taskScore * $wTasks) +
            ($testScore * $wTests) +
            ($compScore * $wComp) +
            ($behavScore * $wBehav) +
            ($finalScore * $wFinal)
        ) / $totalWeight;

        $overallScore = round($weightedTotal, 2);

        // Determine Risk Level
        $minAttendance = (int)SystemSetting::get('min_attendance_percentage', 80);
        $minGrade = (int)SystemSetting::get('min_passing_grade', 60);

        $riskLevel = 'normal';
        if ($attScore < ($minAttendance - 10) || $overallScore < ($minGrade - 10) || $attStats['absent_count'] >= 5) {
            $riskLevel = 'risk';
        } elseif ($attScore < $minAttendance || $overallScore < $minGrade || $attStats['absent_count'] >= 3) {
            $riskLevel = 'attention';
        }

        // Update Intern Record
        $stmtUpd = $pdo->prepare("UPDATE interns SET overall_score = ?, risk_level = ? WHERE id = ?");
        $stmtUpd->execute([$overallScore, $riskLevel, $internId]);

        return [
            'overall_score' => $overallScore,
            'risk_level' => $riskLevel,
            'components' => [
                'attendance' => ['score' => round($attScore, 1), 'weight' => $wAtt],
                'tasks' => ['score' => round($taskScore, 1), 'weight' => $wTasks],
                'tests' => ['score' => round($testScore, 1), 'weight' => $wTests],
                'competencies' => ['score' => round($compScore, 1), 'weight' => $wComp],
                'behavior' => ['score' => round($behavScore, 1), 'weight' => $wBehav],
                'final_eval' => ['score' => round($finalScore, 1), 'weight' => $wFinal]
            ]
        ];
    }
}
