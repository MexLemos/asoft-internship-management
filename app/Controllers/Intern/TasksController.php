<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\Notification;
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
            TaskAssignment::updateStatus((int)$id, 'in_progress', (int)$user['id'], 'Estagiário iniciou a execução da tarefa.');
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

        // Strict Git/GitHub Check
        if (!empty($assignment['requires_github'])) {
            $repoUrl = trim((string)($data['github_repo_url'] ?? ''));
            $prUrl = trim((string)($data['github_pr_url'] ?? ''));
            
            if (empty($repoUrl) && empty($prUrl)) {
                Session::flash('error', 'Esta tarefa exige o uso de Git/GitHub. Informe o link do repositório ou Pull Request antes de submeter.');
                return $this->redirect("/intern/tasks/{$id}");
            }

            if (!empty($repoUrl) && (!filter_var($repoUrl, FILTER_VALIDATE_URL) || !str_starts_with($repoUrl, 'http'))) {
                Session::flash('error', 'Informe um link válido para o repositório GitHub (ex: https://github.com/usuario/repo).');
                return $this->redirect("/intern/tasks/{$id}");
            }

            if (!empty($prUrl) && (!filter_var($prUrl, FILTER_VALIDATE_URL) || !str_starts_with($prUrl, 'http'))) {
                Session::flash('error', 'Informe um link válido para o Pull Request (ex: https://github.com/usuario/repo/pull/1).');
                return $this->redirect("/intern/tasks/{$id}");
            }
        }

        TaskAssignment::addSubmission((int)$id, (int)$intern['id'], $data);
        TaskAssignment::updateStatus((int)$id, 'submitted', (int)$user['id'], 'Estagiário enviou submissão para avaliação.');

        // Notify Supervisor
        $supervisorUserId = (int)($assignment['assigned_by'] ?? 0);
        if ($supervisorUserId > 0) {
            Notification::create(
                $supervisorUserId,
                'task',
                'Nova Submissão de Tarefa: ' . $assignment['title'],
                "O estagiário {$intern['full_name']} submeteu a tarefa '{$assignment['title']}' para a sua avaliação.",
                "/supervisor/tasks/review/{$id}"
            );
        }

        AuditLog::log('task_submit', 'tasks', (int)$id, null, [
            'github_repo' => $data['github_repo_url'] ?? null,
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
                
                // Notify Supervisor
                $supervisorUserId = (int)($assignment['assigned_by'] ?? 0);
                if ($supervisorUserId > 0) {
                    Notification::create(
                        $supervisorUserId,
                        'comment',
                        'Novo Comentário na Tarefa: ' . $assignment['title'],
                        "{$intern['full_name']} comentou: \"{$comment}\"",
                        "/supervisor/tasks/review/{$id}"
                    );
                }
            }
        }

        return $this->redirect("/intern/tasks/{$id}");
    }
}
