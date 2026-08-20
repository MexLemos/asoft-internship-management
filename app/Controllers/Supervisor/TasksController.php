<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskCategory;
use App\Services\PerformanceScoringEngine;

class TasksController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $tasks = Task::all();
        $categories = TaskCategory::all();
        $interns = Intern::all((int)$user['id']);

        return $this->render('supervisor.tasks.index', [
            'title' => 'Gestão e Atribuição de Tarefas - Asoftmedia',
            'tasks' => $tasks,
            'categories' => $categories,
            'interns' => $interns
        ], 'supervisor');
    }

    public function create(Request $request): Response
    {
        $categories = TaskCategory::all();
        return $this->render('supervisor.tasks.create', [
            'title' => 'Criar Nova Tarefa Prática - Asoftmedia',
            'categories' => $categories
        ], 'supervisor');
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $user = Session::get('user');
        $data['created_by'] = $user['id'];

        $errors = $this->validate($data, [
            'title' => 'required|min:5',
            'description' => 'required',
            'category_id' => 'required|numeric',
            'points' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/supervisor/tasks/create');
        }

        $id = Task::create($data);
        AuditLog::log('task_create', 'tasks', $id, null, ['title' => $data['title']], 'success');

        Session::flash('success', 'Tarefa criada com sucesso!');
        return $this->redirect('/supervisor/tasks');
    }

    public function assign(Request $request): Response
    {
        $data = $request->all();
        $user = Session::get('user');

        $errors = $this->validate($data, [
            'task_id' => 'required|numeric',
            'start_date' => 'required',
            'due_date' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/supervisor/tasks');
        }

        $taskId = (int)$data['task_id'];
        $assignType = $data['assign_type'] ?? 'single';
        $startDate = $data['start_date'];
        $dueDate = $data['due_date'];

        if ($assignType === 'all') {
            // Bulk assign to all supervised interns
            $supervisedInterns = Intern::all((int)$user['id']);
            $internIds = array_column($supervisedInterns, 'id');

            $assignedCount = TaskAssignment::assignBulk($taskId, $internIds, (int)$user['id'], $startDate, $dueDate);

            AuditLog::log('task_bulk_assign', 'tasks', $taskId, null, [
                'assigned_count' => $assignedCount,
                'total_supervised' => count($internIds)
            ], 'success');

            Session::flash('success', "Tarefa atribuída com sucesso a {$assignedCount} estagiários (sem duplicar tarefas já existentes).");
        } else {
            $internId = (int)($data['intern_id'] ?? 0);
            if ($internId <= 0) {
                Session::flash('error', 'Selecione um estagiário válido para atribuir a tarefa.');
                return $this->redirect('/supervisor/tasks');
            }

            $assignId = TaskAssignment::assign($taskId, $internId, (int)$user['id'], $startDate, $dueDate);
            if ($assignId === null) {
                Session::flash('warning', 'Este estagiário já possui esta tarefa atribuída anteriormente.');
            } else {
                AuditLog::log('task_assign', 'tasks', $assignId, null, [
                    'task_id' => $taskId,
                    'intern_id' => $internId
                ], 'success');
                Session::flash('success', 'Tarefa atribuída ao estagiário com sucesso!');
            }
        }

        return $this->redirect('/supervisor/tasks');
    }

    public function review(Request $request, string $id): Response
    {
        $assignment = TaskAssignment::findById((int)$id);
        if (!$assignment) {
            Session::flash('error', 'Atribuição não encontrada.');
            return $this->redirect('/supervisor/dashboard');
        }

        return $this->render('supervisor.tasks.review', [
            'title' => 'Avaliar Submissão: ' . htmlspecialchars($assignment['title']),
            'assignment' => $assignment
        ], 'supervisor');
    }

    public function submitEvaluation(Request $request, string $id): Response
    {
        $assignmentId = (int)$id;
        $data = $request->all();
        $user = Session::get('user');

        $status = $data['status'] ?? 'approved';
        $score = isset($data['score']) ? (float)$data['score'] : 100.0;
        $feedback = trim((string)($data['supervisor_feedback'] ?? ''));

        TaskAssignment::evaluate($assignmentId, (int)$user['id'], $status, $score, $feedback);

        // Recalculate intern score
        $assignment = TaskAssignment::findById($assignmentId);
        if ($assignment) {
            $scoring = new PerformanceScoringEngine();
            $scoring->calculateForIntern((int)$assignment['intern_id']);
        }

        AuditLog::log('task_evaluation', 'tasks', $assignmentId, null, [
            'status' => $status,
            'score' => $score
        ], 'success');

        Session::flash('success', 'Parecer técnico gravado com sucesso!');
        return $this->redirect('/supervisor/dashboard');
    }

    public function addComment(Request $request, string $id): Response
    {
        $assignmentId = (int)$id;
        $comment = trim((string)$request->input('comment', ''));
        $user = Session::get('user');

        if (!empty($comment)) {
            TaskAssignment::addComment($assignmentId, (int)$user['id'], $comment);
        }

        return $this->redirect("/supervisor/tasks/review/{$assignmentId}");
    }
}
