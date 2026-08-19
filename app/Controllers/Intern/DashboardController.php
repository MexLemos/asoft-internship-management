<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Intern;
use App\Models\TaskAssignment;
use App\Services\CertificateGeneratorService;
use App\Services\PerformanceScoringEngine;
use PDO;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            Session::flash('error', 'Registo de estagiário não encontrado.');
            return $this->redirect('/login');
        }

        $internId = (int)$intern['id'];

        // Recalculate score
        $scoring = new PerformanceScoringEngine();
        $scoreData = $scoring->calculateForIntern($internId);
        $intern['overall_score'] = $scoreData['overall_score'];

        // Attendance stats
        $attStats = Attendance::getStats($internId);
        $todayAttendance = Attendance::getTodayForIntern($internId);

        // Tasks
        $tasks = TaskAssignment::getForIntern($internId);
        $pendingTasks = array_filter($tasks, fn($t) => in_array($t['status'], ['assigned', 'in_progress', 'reopened']));
        $completedTasks = array_filter($tasks, fn($t) => $t['status'] === 'approved');

        // Course Progress
        $courses = Course::all();

        // Eligibility for certificate
        $certService = new CertificateGeneratorService();
        $eligibility = $certService->checkEligibility($internId);

        // Badges
        $pdo = Database::getConnection();
        $stmtBadges = $pdo->prepare("
            SELECT b.*, ib.earned_at
            FROM badges b
            INNER JOIN intern_badges ib ON ib.badge_id = b.id
            WHERE ib.intern_id = ?
        ");
        $stmtBadges->execute([$internId]);
        $earnedBadges = $stmtBadges->fetchAll();

        // Calculate progress percentage
        $totalHoursExpected = (float)($intern['total_required_hours'] ?? 300);
        $hoursCompleted = (float)($attStats['total_hours_worked'] ?? 0);
        $progressPct = ($totalHoursExpected > 0) ? min(100.0, ($hoursCompleted / $totalHoursExpected) * 100) : 0.0;

        return $this->render('intern.dashboard', [
            'title' => 'Portal do Estagiário - Asoftmedia',
            'intern' => $intern,
            'attStats' => $attStats,
            'todayAttendance' => $todayAttendance,
            'pendingTasks' => $pendingTasks,
            'completedTasks' => $completedTasks,
            'courses' => $courses,
            'eligibility' => $eligibility,
            'earnedBadges' => $earnedBadges,
            'progressPct' => round($progressPct, 1),
            'hoursCompleted' => round($hoursCompleted, 1),
            'totalHoursExpected' => round($totalHoursExpected, 0)
        ], 'intern');
    }
}
