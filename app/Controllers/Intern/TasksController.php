<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\TaskAssignment;

class TasksController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        $tasks = TaskAssignment::getForIntern((int)$intern['id']);

        return $this->render('intern.tasks.index', [
            'title' => 'Minhas Tarefas & Atividades - Asoftmedia',
            'tasks' => $tasks,
            'intern' => $intern
        ], 'intern');
    }

    public function show(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $assignment = TaskAssignment::findById((int)$id);

        if (!$assignment || (int)$assignment['intern_id'] !== (int)$intern['id']) {
            Session::flash('error', 'Tarefa não encontrada.');
            return $this->redirect('/intern/tasks');
        }

        return $this->render('intern.tasks.show', [
            'title' => 'Tarefa: ' . htmlspecialchars($assignment['title']),
            'assignment' => $assignment,
            'intern' => $intern
        ], 'intern');
    }

    public function start(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $assignment = TaskAssignment::findById((int)$id);

        if ($assignment && (int)$assignment['intern_id'] === (int)$intern['id']) {
            TaskAssignment::updateStatus((int)$id, 'in_progress');
            AuditLog::log('task_start', 'tasks', (int)$id, null, null, 'success');
            Session::flash('success', 'Tarefa iniciada! Bom trabalho.');
        }

        return $this->redirect("/intern/tasks/{$id}");
    }

    public function submit(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $assignment = TaskAssignment::findById((int)$id);

        if (!$assignment || (int)$assignment['intern_id'] !== (int)$intern['id']) {
            Session::flash('error', 'Tarefa não encontrada.');
            return $this->redirect('/intern/tasks');
        }

        $data = $request->all();

        TaskAssignment::addSubmission((int)$id, (int)$intern['id'], $data);
        TaskAssignment::updateStatus((int)$id, 'submitted');

        AuditLog::log('task_submit', 'tasks', (int)$id, null, [
            'github_pr' => $data['github_pr_url'] ?? null
        ], 'success');

        Session::flash('success', 'Tarefa submetida para avaliação com sucesso!');
        return $this->redirect("/intern/tasks/{$id}");
    }

    public function addComment(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $assignment = TaskAssignment::findById((int)$id);

        if ($assignment && (int)$assignment['intern_id'] === (int)$intern['id']) {
            $comment = trim((string)$request->input('comment', ''));
            if (!empty($comment)) {
                TaskAssignment::addComment((int)$id, (int)$user['id'], $comment);
            }
        }

        return $this->redirect("/intern/tasks/{$id}");
    }
}
