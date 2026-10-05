<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\MentorshipLog;
use App\Services\PerformanceScoringEngine;

class MentorshipController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedInternId = $request->input('intern_id') ? (int)$request->input('intern_id') : null;

        $logs = MentorshipLog::getAll(150, $selectedInternId);
        $stats = MentorshipLog::getStats();
        $interns = Intern::all();

        return $this->render('supervisor.mentorship.index', [
            'title' => 'Mentorias e Acompanhamento 1-on-1 - Asoftmedia',
            'logs' => $logs,
            'stats' => $stats,
            'interns' => $interns,
            'selectedInternId' => $selectedInternId,
            'types' => MentorshipLog::TYPES,
        ], 'supervisor');
    }

    public function create(Request $request): Response
    {
        $interns = Intern::all();
        $preselectedInternId = (int)$request->input('intern_id', 0);

        return $this->render('supervisor.mentorship.create', [
            'title' => 'Nova Sessão de Mentoria / 1-on-1 - Asoftmedia',
            'interns' => $interns,
            'preselectedInternId' => $preselectedInternId,
            'types' => MentorshipLog::TYPES,
        ], 'supervisor');
    }

    public function store(Request $request): Response
    {
        $user = Session::get('user');
        $supervisorId = (int)$user['id'];
        $data = $request->all();

        $errors = $this->validate($data, [
            'intern_id' => 'required|numeric',
            'title' => 'required|min:3',
            'summary' => 'required|min:5',
            'session_type' => 'required',
            'session_date' => 'required'
        ]);

        $internId = (int)($data['intern_id'] ?? 0);
        $intern = Intern::findById($internId);

        if (!$intern) {
            $errors['intern_id'] = 'Estagiário selecionado inválido ou não encontrado.';
        }

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/supervisor/mentorship/create' . ($internId > 0 ? "?intern_id={$internId}" : ''));
        }

        $data['supervisor_id'] = $supervisorId;
        if (!empty($data['rating'])) {
            $data['rating'] = max(1, min(5, (int)$data['rating']));
        }

        $logId = MentorshipLog::create($data);

        // Recalculate intern score with the new mentorship rating
        $scoring = new PerformanceScoringEngine();
        $scoreResult = $scoring->calculateForIntern($internId);

        AuditLog::log('mentorship_session_created', 'mentorship', $logId, null, [
            'intern_id' => $internId,
            'intern_name' => $intern['full_name'],
            'title' => $data['title'],
            'rating' => $data['rating'] ?? null,
            'new_overall_score' => $scoreResult['overall_score']
        ], 'success');

        Session::flash('success', "Sessão de mentoria registada com sucesso! A nota do estagiário foi atualizada para {$scoreResult['overall_score']}%.");
        return $this->redirect("/supervisor/mentorship/{$logId}");
    }

    public function show(Request $request, string $id): Response
    {
        $logId = (int)$id;
        $log = MentorshipLog::findById($logId);

        if (!$log) {
            Session::flash('error', 'Registo de mentoria não encontrado.');
            return $this->redirect('/supervisor/mentorship');
        }

        $intern = Intern::findById((int)$log['intern_id']);
        $scoring = new PerformanceScoringEngine();
        $scoreData = $intern ? $scoring->calculateForIntern((int)$intern['id']) : null;

        return $this->render('supervisor.mentorship.show', [
            'title' => 'Sessão de Mentoria: ' . $log['title'],
            'log' => $log,
            'intern' => $intern,
            'scoreData' => $scoreData,
            'types' => MentorshipLog::TYPES,
        ], 'supervisor');
    }

    public function edit(Request $request, string $id): Response
    {
        $logId = (int)$id;
        $log = MentorshipLog::findById($logId);

        if (!$log) {
            Session::flash('error', 'Registo de mentoria não encontrado.');
            return $this->redirect('/supervisor/mentorship');
        }

        $interns = Intern::all();

        return $this->render('supervisor.mentorship.edit', [
            'title' => 'Editar Sessão de Mentoria: ' . $log['title'],
            'log' => $log,
            'interns' => $interns,
            'types' => MentorshipLog::TYPES,
        ], 'supervisor');
    }

    public function update(Request $request, string $id): Response
    {
        $logId = (int)$id;
        $log = MentorshipLog::findById($logId);

        if (!$log) {
            Session::flash('error', 'Registo de mentoria não encontrado.');
            return $this->redirect('/supervisor/mentorship');
        }

        $data = $request->all();
        $errors = $this->validate($data, [
            'title' => 'required|min:3',
            'summary' => 'required|min:5',
            'session_type' => 'required',
            'session_date' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect("/supervisor/mentorship/{$logId}/edit");
        }

        if (!empty($data['rating'])) {
            $data['rating'] = max(1, min(5, (int)$data['rating']));
        }

        MentorshipLog::update($logId, $data);

        // Recalculate score
        $internId = (int)$log['intern_id'];
        $scoring = new PerformanceScoringEngine();
        $scoreResult = $scoring->calculateForIntern($internId);

        Session::flash('success', "Sessão de mentoria atualizada com sucesso! Nota recalculada ({$scoreResult['overall_score']}%).");
        return $this->redirect("/supervisor/mentorship/{$logId}");
    }

    public function delete(Request $request, string $id): Response
    {
        $logId = (int)$id;
        $log = MentorshipLog::findById($logId);

        if (!$log) {
            Session::flash('error', 'Registo de mentoria não encontrado.');
            return $this->redirect('/supervisor/mentorship');
        }

        $internId = (int)$log['intern_id'];
        MentorshipLog::delete($logId);

        // Recalculate score after deletion
        $scoring = new PerformanceScoringEngine();
        $scoring->calculateForIntern($internId);

        Session::flash('success', 'Registo de mentoria removido com sucesso.');
        return $this->redirect('/supervisor/mentorship');
    }
}
