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
        $interns = Intern::all((int)$user['id']);

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

        $competencies = Competency::getForIntern($id);

        return $this->render('supervisor.competencies.evaluate', [
            'title' => 'Matriz de Competências: ' . htmlspecialchars($intern['full_name']),
            'intern' => $intern,
            'competencies' => $competencies
        ], 'supervisor');
    }

    public function save(Request $request, string $internId): Response
    {
        $id = (int)$internId;
        $user = Session::get('user');
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
