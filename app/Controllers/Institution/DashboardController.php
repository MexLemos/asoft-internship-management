<?php

declare(strict_types=1);

namespace App\Controllers\Institution;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\Competency;
use App\Models\Institution;
use App\Models\Intern;
use App\Models\TaskAssignment;
use PDO;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $pdo = Database::getConnection();

        // Get institution ID associated with this user
        $stmt = $pdo->prepare("SELECT institution_id FROM institution_users WHERE user_id = ? LIMIT 1");
        $stmt->execute([(int)$user['id']]);
        $instId = (int)$stmt->fetchColumn();

        if (!$instId) {
            // Fallback for first institution if not explicitly linked
            $instId = (int)$pdo->query("SELECT id FROM institutions LIMIT 1")->fetchColumn();
        }

        $institution = Institution::findById($instId);
        $interns = Intern::all(null, $instId);

        // Stats for institution
        $totalAlunos = count($interns);
        $normalCount = count(array_filter($interns, fn($i) => $i['risk_level'] === 'normal'));
        $attentionCount = count(array_filter($interns, fn($i) => $i['risk_level'] === 'attention'));
        $riskCount = count(array_filter($interns, fn($i) => $i['risk_level'] === 'risk'));

        return $this->render('institution.dashboard', [
            'title' => 'Portal da Instituição de Ensino - Asoftmedia',
            'institution' => $institution,
            'interns' => $interns,
            'stats' => [
                'total' => $totalAlunos,
                'normal' => $normalCount,
                'attention' => $attentionCount,
                'risk' => $riskCount
            ]
        ], 'institution');
    }

    public function showIntern(Request $request, string $id): Response
    {
        $internId = (int)$id;
        $intern = Intern::findById($internId);

        if (!$intern) {
            Session::flash('error', 'Estagiário não encontrado.');
            return $this->redirect('/institution/dashboard');
        }

        $attendance = Attendance::getForIntern($internId, 30);
        $tasks = TaskAssignment::getForIntern($internId);
        $competencies = Competency::getForIntern($internId);

        return $this->render('institution.intern_details', [
            'title' => 'Acompanhamento do Aluno: ' . htmlspecialchars($intern['full_name']),
            'intern' => $intern,
            'attendance' => $attendance,
            'tasks' => $tasks,
            'competencies' => $competencies
        ], 'institution');
    }
}
