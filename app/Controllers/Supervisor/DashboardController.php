<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Intern;
use PDO;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $supervisorId = (int)$user['id'];

        $interns = Intern::all($supervisorId);
        $pdo = Database::getConnection();

        // Pending Submissions to Review
        $stmtPending = $pdo->prepare("
            SELECT ta.*, t.title, t.priority, t.points, i.full_name as intern_name, i.internship_code,
                   ts.github_repo_url, ts.github_pr_url, ts.submitted_at
            FROM task_assignments ta
            INNER JOIN tasks t ON t.id = ta.task_id
            INNER JOIN interns i ON i.id = ta.intern_id
            LEFT JOIN task_submissions ts ON ts.assignment_id = ta.id
            WHERE i.supervisor_id = ? AND ta.status IN ('submitted', 'in_review')
            ORDER BY ts.submitted_at ASC
        ");
        $stmtPending->execute([$supervisorId]);
        $pendingReviews = $stmtPending->fetchAll();

        return $this->render('supervisor.dashboard', [
            'title' => 'Painel do Supervisor / Orientador - Asoftmedia',
            'interns' => $interns,
            'pendingReviews' => $pendingReviews
        ], 'supervisor');
    }
}
