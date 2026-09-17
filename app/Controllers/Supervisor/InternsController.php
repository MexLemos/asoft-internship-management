<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\Competency;
use App\Models\Intern;
use App\Models\TaskAssignment;
use App\Services\PerformanceScoringEngine;
use PDO;

/**
 * Supervisor: Detalhes dos Estagiários
 * Lists interns supervised by the current user and provides
 * a detail view with attendance delays and performance data.
 */
class InternsController extends Controller
{
    public function index(Request $request): Response
    {
        $user         = Session::get('user');
        $supervisorId = (int)$user['id'];

        $interns = Intern::all($supervisorId);

        return $this->render('supervisor.interns.index', [
            'title'   => 'Meus Estagiários - Asoftmedia',
            'interns' => $interns,
        ], 'supervisor');
    }

    public function show(Request $request, string $id): Response
    {
        $user         = Session::get('user');
        $supervisorId = (int)$user['id'];
        $internId     = (int)$id;

        $intern = Intern::findById($internId);

        if (!$intern || (int)$intern['supervisor_id'] !== $supervisorId) {
            Session::flash('error', 'Estagiário não encontrado ou sem permissão de acesso.');
            return $this->redirect('/supervisor/interns');
        }

        // Performance score
        $scoring   = new PerformanceScoringEngine();
        $scoreData = $scoring->calculateForIntern($internId);
        $intern['overall_score'] = $scoreData['overall_score'];
        $intern['risk_level']    = $scoreData['risk_level'];

        // Last 30-day attendance
        $attendance = Attendance::getForIntern($internId, 30);

        // Compute delays / absences from attendance records
        $attendanceSummary = $this->buildAttendanceSummary($internId);

        $tasks       = TaskAssignment::getForIntern($internId);
        $competencies = Competency::getForIntern($internId);

        return $this->render('supervisor.interns.show', [
            'title'             => 'Detalhe do Estagiário: ' . $intern['full_name'],
            'intern'            => $intern,
            'attendance'        => $attendance,
            'attendanceSummary' => $attendanceSummary,
            'tasks'             => $tasks,
            'competencies'      => $competencies,
            'scoreData'         => $scoreData,
        ], 'supervisor');
    }

    /**
     * Build a summary of attendance: present days, absences, lates, and recent late records.
     */
    private function buildAttendanceSummary(int $internId): array
    {
        $pdo = Database::getConnection();

        // Overall counters
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END)  as present,
                SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END)  as absent,
                SUM(CASE WHEN status = 'late'    THEN 1 ELSE 0 END)  as late,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END)  as excused
            FROM attendance
            WHERE intern_id = ?
        ");
        $stmt->execute([$internId]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        // Last 10 late arrivals with details
        $stmtLate = $pdo->prepare("
            SELECT date, check_in_time, notes
            FROM attendance
            WHERE intern_id = ? AND status = 'late'
            ORDER BY date DESC
            LIMIT 10
        ");
        $stmtLate->execute([$internId]);
        $lateRecords = $stmtLate->fetchAll(PDO::FETCH_ASSOC);

        // Last 10 absences
        $stmtAbsent = $pdo->prepare("
            SELECT date, notes
            FROM attendance
            WHERE intern_id = ? AND status = 'absent'
            ORDER BY date DESC
            LIMIT 10
        ");
        $stmtAbsent->execute([$internId]);
        $absentRecords = $stmtAbsent->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total'         => (int)($counts['total_records'] ?? 0),
            'present'       => (int)($counts['present']       ?? 0),
            'absent'        => (int)($counts['absent']        ?? 0),
            'late'          => (int)($counts['late']          ?? 0),
            'excused'       => (int)($counts['excused']       ?? 0),
            'late_records'  => $lateRecords,
            'absent_records'=> $absentRecords,
        ];
    }
}
