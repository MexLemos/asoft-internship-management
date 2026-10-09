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
        $page = max(1, (int)$request->input('page', 1));
        $filters = [
            'search' => trim((string)$request->input('search', '')),
            'category_id' => $request->input('category_id'),
            'priority' => $request->input('priority'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $paginated = Task::paginate($page, 10, $filters);
        $categories = TaskCategory::all();
        $interns = Intern::all();

        return $this->render('supervisor.tasks.index', [
            'title' => 'Gestão e Atribuição de Tarefas - Asoftmedia',
            'tasks' => $paginated['data'],
            'pagination' => $paginated,
            'filters' => $filters,
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
        $data['due_date'] = !empty($data['due_date']) ? $data['due_date'] : null;

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

    public function edit(Request $request, string $id): Response
    {
        $taskId = (int)$id;
        $task = Task::findById($taskId);
        if (!$task) {
            Session::flash('error', 'Tarefa não encontrada.');
            return $this->redirect('/supervisor/tasks');
        }

        $categories = TaskCategory::all();
        return $this->render('supervisor.tasks.edit', [
            'title' => 'Editar Tarefa: ' . $task['title'],
            'task' => $task,
            'categories' => $categories
        ], 'supervisor');
    }

    public function update(Request $request, string $id): Response
    {
        $taskId = (int)$id;
        $task = Task::findById($taskId);
        if (!$task) {
            Session::flash('error', 'Tarefa não encontrada.');
            return $this->redirect('/supervisor/tasks');
        }

        $data = $request->all();
        $errors = $this->validate($data, [
            'title' => 'required|min:5',
            'description' => 'required',
            'category_id' => 'required|numeric',
            'points' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect("/supervisor/tasks/{$taskId}/edit");
        }

        $data['due_date'] = !empty($data['due_date']) ? $data['due_date'] : null;
        Task::update($taskId, $data);
        AuditLog::log('task_update', 'tasks', $taskId, null, ['title' => $data['title']], 'success');

        Session::flash('success', 'Tarefa atualizada com sucesso!');
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
            // Bulk assign to all interns
            $allInterns = Intern::all();
            $internIds = array_column($allInterns, 'id');

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
            return $this->redirect('/supervisor/tasks');
        }

        return $this->render('supervisor.tasks.review', [
            'title' => 'Avaliar Submissão: ' . $assignment['title'],
            'assignment' => $assignment
        ], 'supervisor');
    }

    public function submitEvaluation(Request $request, string $id): Response
    {
        $assignmentId = (int)$id;
        $assignment = TaskAssignment::findById($assignmentId);
        if (!$assignment) {
            Session::flash('error', 'Atribuição não encontrada.');
            return $this->redirect('/supervisor/tasks');
        }

        $user = Session::get('user');

        $data = $request->all();
        $status = $data['status'] ?? 'approved';
        $score = isset($data['score']) ? (float)$data['score'] : 100.0;
        $feedback = trim((string)($data['supervisor_feedback'] ?? ''));

        TaskAssignment::evaluate($assignmentId, (int)$user['id'], $status, $score, $feedback);

        // Recalculate intern score
        $scoring = new PerformanceScoringEngine();
        $scoring->calculateForIntern((int)$assignment['intern_id']);

        AuditLog::log('task_evaluation', 'tasks', $assignmentId, null, [
            'status' => $status,
            'score' => $score
        ], 'success');

        Session::flash('success', 'Parecer técnico gravado com sucesso!');
        return $this->redirect('/supervisor/tasks');
    }

    public function addComment(Request $request, string $id): Response
    {
        $assignmentId = (int)$id;
        $assignment = TaskAssignment::findById($assignmentId);
        if (!$assignment) {
            Session::flash('error', 'Atribuição não encontrada.');
            return $this->redirect('/supervisor/tasks');
        }

        $user = Session::get('user');

        $comment = trim((string)$request->input('comment', ''));
        if (!empty($comment)) {
            TaskAssignment::addComment($assignmentId, (int)$user['id'], $comment);
        }

        return $this->redirect("/supervisor/tasks/review/{$assignmentId}");
    }
}
