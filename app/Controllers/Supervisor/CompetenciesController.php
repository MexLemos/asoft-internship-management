<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Competency;
use App\Models\Intern;
use App\Services\PerformanceScoringEngine;

class CompetenciesController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $userRoles = $user['roles'] ?? [];
        $isStaffAdmin = in_array('super_admin', $userRoles, true) || in_array('admin', $userRoles, true);
        $interns = Intern::all($isStaffAdmin ? null : (int)$user['id']);

        return $this->render('supervisor.competencies.index', [
            'title' => 'Avaliação de Competências dos Estagiários - Asoftmedia',
            'interns' => $interns
        ], 'supervisor');
    }

    public function evaluate(Request $request, string $internId): Response
    {
        $id = (int)$internId;
        $intern = Intern::findById($id);
        if (!$intern) {
            Session::flash('error', 'Estagiário não encontrado.');
            return $this->redirect('/supervisor/competencies');
        }

        $user = Session::get('user');
        $userRoles = $user['roles'] ?? [];
        $isStaffAdmin = in_array('super_admin', $userRoles, true) || in_array('admin', $userRoles, true);

        if (!$isStaffAdmin && (int)$intern['supervisor_id'] !== (int)$user['id']) {
            Session::flash('error', 'Acesso negado: Este estagiário não está atribuído à sua supervisão.');
            return $this->redirect('/supervisor/competencies');
        }

        $competencies = Competency::getForIntern($id);

        return $this->render('supervisor.competencies.evaluate', [
            'title' => 'Matriz de Competências: ' . $intern['full_name'],
            'intern' => $intern,
            'competencies' => $competencies
        ], 'supervisor');
    }

    public function save(Request $request, string $internId): Response
    {
        $id = (int)$internId;
        $intern = Intern::findById($id);
        if (!$intern) {
            Session::flash('error', 'Estagiário não encontrado.');
            return $this->redirect('/supervisor/competencies');
        }

        $user = Session::get('user');
        $userRoles = $user['roles'] ?? [];
        $isStaffAdmin = in_array('super_admin', $userRoles, true) || in_array('admin', $userRoles, true);

        if (!$isStaffAdmin && (int)$intern['supervisor_id'] !== (int)$user['id']) {
            Session::flash('error', 'Acesso negado: Não tem permissão para avaliar estagiários de outro supervisor.');
            return $this->redirect('/supervisor/competencies');
        }

        $data = $request->all();
        $levels = (array)($data['levels'] ?? []);
        $notes = (array)($data['notes'] ?? []);

        foreach ($levels as $compId => $lvl) {
            $compLevel = max(1, min(5, (int)$lvl));
            $note = $notes[$compId] ?? null;
            Competency::evaluate($id, (int)$compId, $compLevel, (int)$user['id'], $note);
        }

        $scoring = new PerformanceScoringEngine();
        $scoring->calculateForIntern($id);

        AuditLog::log('competencies_evaluated', 'competencies', $id, null, ['count' => count($levels)], 'success');

        Session::flash('success', 'Matriz de competências atualizada com sucesso!');
        return $this->redirect("/supervisor/competencies/evaluate/{$id}");
    }
}
