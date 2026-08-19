<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Competency;
use App\Models\Intern;
use App\Models\TaskAssignment;

class PortfolioController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        $internId = (int)$intern['id'];

        $tasks = TaskAssignment::getForIntern($internId);
        $approvedTasks = array_filter($tasks, fn($t) => $t['status'] === 'approved');
        $competencies = Competency::getForIntern($internId);

        // Fetch Badges
        $pdo = Database::getConnection();
        $stmtBadges = $pdo->prepare("
            SELECT b.*, ib.earned_at
            FROM badges b
            INNER JOIN intern_badges ib ON ib.badge_id = b.id
            WHERE ib.intern_id = ?
        ");
        $stmtBadges->execute([$internId]);
        $badges = $stmtBadges->fetchAll();

        // Generate LinkedIn share message
        $skillsStr = implode(', ', array_slice(array_column($competencies, 'name'), 0, 5));
        $linkedInText = "Concluí com sucesso o estágio curricular em " . $intern['internship_area'] . " na Asoftmedia! Durante este período, desenvolvi competências práticas em " . ($skillsStr ?: 'Desenvolvimento Web, PHP e MySQL') . " e participei ativamente no desenvolvimento de soluções tecnológicas reais. #Asoftmedia #Estagio #Desenvolvimento #Tecnologia";

        return $this->render('intern.portfolio.index', [
            'title' => 'Meu Portfólio & Conquistas - Asoftmedia',
            'intern' => $intern,
            'approvedTasks' => $approvedTasks,
            'competencies' => $competencies,
            'badges' => $badges,
            'linkedInText' => $linkedInText
        ], 'intern');
    }
}
