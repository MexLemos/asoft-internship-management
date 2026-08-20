<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
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
        $linkedInText = "Concluí com sucesso atividades práticas de estágio na Asoftmedia na área de " . ($intern['internship_area'] ?? 'Geral') . "! Durante este período, consolidei competências técnicas em " . ($skillsStr ?: 'Desenvolvimento de Software, PHP, MySQL e Redes') . " e participei em projetos reais da empresa. #Asoftmedia #Estagio #Tecnologia #Angola";

        // Check if frozen
        $isFrozen = (bool)($intern['portfolio_frozen'] ?? false) || ($intern['status'] === 'completed');

        return $this->render('intern.portfolio.index', [
            'title' => 'Meu Portfólio & Conquistas - Asoftmedia',
            'intern' => $intern,
            'approvedTasks' => $approvedTasks,
            'competencies' => $competencies,
            'badges' => $badges,
            'linkedInText' => $linkedInText,
            'isFrozen' => $isFrozen
        ], 'intern');
    }

    public function saveCustomization(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        if ($intern['status'] === 'completed' || !empty($intern['portfolio_frozen'])) {
            Session::flash('error', 'O seu portfólio está congelado e não pode mais ser alterado pois o estágio já foi concluído.');
            return $this->redirect('/intern/portfolio');
        }

        $html = (string)$request->input('portfolio_html', '');
        $css = (string)$request->input('portfolio_css', '');
        $js = (string)$request->input('portfolio_js', '');

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE interns 
            SET portfolio_html = ?, portfolio_css = ?, portfolio_js = ? 
            WHERE id = ?
        ");
        $stmt->execute([$html, $css, $js, (int)$intern['id']]);

        AuditLog::log('portfolio_customized', 'interns', (int)$intern['id'], null, null, 'success');

        Session::flash('success', 'Personalização do portfólio gravada com sucesso!');
        return $this->redirect('/intern/portfolio');
    }

    public function recordSocialShare(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $network = $request->input('network', 'linkedin');
        $certCode = $request->input('certificate_code', 'GENERAL');

        AuditLog::log('social_share', 'certificates', (int)$intern['id'], null, [
            'network' => $network,
            'certificate_code' => $certCode
        ], 'success');

        return $this->json(['success' => true, 'network' => $network]);
    }
}
