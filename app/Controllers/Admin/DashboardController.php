<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Intern;
use App\Models\Institution;
use App\Models\Task;
use PDO;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $pdo = Database::getConnection();

        // 1. KPI Counts
        $totalInterns = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE deleted_at IS NULL")->fetchColumn();
        $activeInterns = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();
        $totalInstitutions = (int)$pdo->query("SELECT COUNT(*) FROM institutions WHERE deleted_at IS NULL")->fetchColumn();
        $pendingTasks = (int)$pdo->query("SELECT COUNT(*) FROM task_assignments WHERE status IN ('assigned', 'in_progress', 'submitted')")->fetchColumn();
        
        $riskNormal = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE risk_level = 'normal' AND status = 'active'")->fetchColumn();
        $riskAttention = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE risk_level = 'attention' AND status = 'active'")->fetchColumn();
        $riskHigh = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE risk_level = 'risk' AND status = 'active'")->fetchColumn();

        // 2. Average Attendance & Grade
        $avgScore = (float)$pdo->query("SELECT AVG(overall_score) FROM interns WHERE status = 'active'")->fetchColumn() ?: 0.0;
        
        // 3. Recent Activity & Attendance Today
        $todayAttendance = $pdo->query("
            SELECT a.*, i.full_name, i.internship_code, inst.name as institution_name
            FROM attendance a
            INNER JOIN interns i ON i.id = a.intern_id
            INNER JOIN institutions inst ON inst.id = i.institution_id
            WHERE a.date = CURDATE()
            ORDER BY a.check_in_time DESC
            LIMIT 10
        ")->fetchAll();

        // 4. Interns List with Risk Status
        $interns = Intern::all();

        return $this->render('admin.dashboard', [
            'title' => 'Painel de Controlo Executivo - Asoftmedia',
            'stats' => [
                'total_interns' => $totalInterns,
                'active_interns' => $activeInterns,
                'total_institutions' => $totalInstitutions,
                'pending_tasks' => $pendingTasks,
                'avg_score' => round($avgScore, 1),
                'risk_normal' => $riskNormal,
                'risk_attention' => $riskAttention,
                'risk_high' => $riskHigh,
            ],
            'todayAttendance' => $todayAttendance,
            'interns' => $interns
        ], 'admin');
    }
}
