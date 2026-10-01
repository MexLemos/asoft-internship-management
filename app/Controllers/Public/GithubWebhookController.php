<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\Notification;
use App\Models\TaskAssignment;
use App\Services\PerformanceScoringEngine;
use PDO;

class GithubWebhookController extends Controller
{
    /**
     * Endpoint receptor de Webhooks do GitHub para sincronização de Pull Requests e Tarefas.
     */
    public function handle(Request $request): Response
    {
        $rawPayload = file_get_contents('php://input');
        $signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'ping';

        // 1. Validação de Assinatura Criptográfica HMAC (se secret configurado no .env)
        $secret = $_ENV['GITHUB_WEBHOOK_SECRET'] ?? getenv('GITHUB_WEBHOOK_SECRET') ?: '';
        if (!empty($secret)) {
            if (empty($signatureHeader) || !str_starts_with($signatureHeader, 'sha256=')) {
                return (new Response())->json(['error' => 'Assinatura X-Hub-Signature-256 ausente ou inválida.'], 403);
            }

            $expectedHash = 'sha256=' . hash_hmac('sha256', $rawPayload, $secret);
            if (!hash_equals($expectedHash, $signatureHeader)) {
                AuditLog::log('github_webhook_invalid_signature', 'tasks', null, null, [
                    'ip' => $request->ip()
                ], 'suspicious');

                return (new Response())->json(['error' => 'Assinatura inválida.'], 403);
            }
        }

        // 2. Responder a Pings de teste do GitHub
        if ($event === 'ping') {
            return (new Response())->json([
                'success' => true,
                'message' => 'Pong! Webhook do GitHub recebido e validado com sucesso pela Asoftmedia.'
            ]);
        }

        // 3. Processamento de Eventos de Pull Request
        if ($event === 'pull_request') {
            $payload = json_decode($rawPayload, true);
            if (!is_array($payload)) {
                return (new Response())->json(['error' => 'JSON payload inválido.'], 400);
            }

            $action = $payload['action'] ?? '';
            $pr = $payload['pull_request'] ?? [];
            $repo = $payload['repository'] ?? [];

            $prNumber = (int)($pr['number'] ?? 0);
            $prTitle = (string)($pr['title'] ?? '');
            $prBody = (string)($pr['body'] ?? '');
            $prUrl = (string)($pr['html_url'] ?? '');
            $repoUrl = (string)($repo['html_url'] ?? '');
            $branch = (string)($pr['head']['ref'] ?? '');
            $commitSha = (string)($pr['head']['sha'] ?? '');
            $githubUser = (string)($pr['user']['login'] ?? '');
            $isMerged = !empty($pr['merged']);

            // Localizar atribuição de tarefa vinculada
            $assignment = $this->resolveAssignment($prTitle, $prBody, $repoUrl, $githubUser);

            if (!$assignment) {
                return (new Response())->json([
                    'success' => true,
                    'message' => 'Evento recebido, mas nenhuma tarefa ativa no AIMS correspondeu a este PR.'
                ]);
            }

            $assignmentId = (int)$assignment['id'];
            $internId = (int)$assignment['intern_id'];
            $supervisorId = (int)$assignment['assigned_by'];
            $pdo = Database::getConnection();

            if ($action === 'opened' || $action === 'reopened') {
                // Quando o PR é aberto no GitHub, a tarefa transita para 'submitted' automaticamente
                $stmtSub = $pdo->prepare("
                    INSERT INTO task_submissions (assignment_id, intern_id, notes, github_repo_url, github_branch, github_commit_hash, github_pr_url, submitted_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $notes = "Submissão gerada automaticamente via GitHub Webhook (PR #{$prNumber}: {$prTitle})";
                $stmtSub->execute([$assignmentId, $internId, $notes, $repoUrl, $branch, $commitSha, $prUrl]);

                // Atualizar status da tarefa para 'submitted'
                $pdo->prepare("
                    UPDATE task_assignments 
                    SET status = 'submitted' 
                    WHERE id = ? AND status IN ('assigned', 'in_progress')
                ")->execute([$assignmentId]);

                TaskAssignment::recordHistory(
                    $assignmentId,
                    (int)($assignment['intern_user_id'] ?? 1),
                    'github_pr_opened',
                    $assignment['status'],
                    'submitted',
                    null,
                    "Pull Request #{$prNumber} aberto no GitHub: {$prUrl}"
                );

                // Notificar Supervisor
                Notification::create(
                    $supervisorId,
                    'task',
                    'Novo PR Aberto no GitHub: ' . $assignment['title'],
                    "O estagiário abriu o Pull Request #{$prNumber} no GitHub ({$prTitle}). Pronto para revisão!",
                    "/supervisor/tasks/review/{$assignmentId}"
                );

                AuditLog::log('github_pr_opened', 'tasks', $assignmentId, null, [
                    'pr_number' => $prNumber,
                    'repo' => $repoUrl
                ], 'success');

                return (new Response())->json([
                    'success' => true,
                    'message' => "Tarefa #{$assignmentId} atualizada para 'submitted' com o PR #{$prNumber}."
                ]);
            }

            if ($action === 'closed' && $isMerged) {
                // Quando o PR é aprovado e mesclado (Merged) no GitHub
                $mergedBy = (string)($pr['merged_by']['login'] ?? 'Supervisor');
                $defaultPoints = (float)($assignment['points'] ?? 100.0);

                TaskAssignment::evaluate(
                    $assignmentId,
                    $supervisorId,
                    'approved',
                    $defaultPoints,
                    "Aprovado automaticamente via GitHub Merge do PR #{$prNumber} por @{$mergedBy}."
                );

                // Recalcular pontuação do estagiário
                $scoring = new PerformanceScoringEngine();
                $scoring->calculateForIntern($internId);

                // Notificar Estagiário
                $internUserId = (int)($assignment['intern_user_id'] ?? 0);
                if ($internUserId > 0) {
                    Notification::create(
                        $internUserId,
                        'task',
                        'Pull Request Aprovado no GitHub: ' . $assignment['title'],
                        "Parabéns! O seu Pull Request #{$prNumber} foi aprovado e integrado no repositório GitHub com sucesso!",
                        "/intern/tasks/{$assignmentId}"
                    );
                }

                AuditLog::log('github_pr_merged', 'tasks', $assignmentId, null, [
                    'pr_number' => $prNumber,
                    'merged_by' => $mergedBy
                ], 'success');

                return (new Response())->json([
                    'success' => true,
                    'message' => "Tarefa #{$assignmentId} aprovada automaticamente por GitHub PR Merge."
                ]);
            }
        }

        return (new Response())->json(['success' => true, 'message' => 'Evento processado.']);
    }

    /**
     * Localiza a tarefa correspondente no sistema AIMS.
     */
    private function resolveAssignment(string $title, string $body, string $repoUrl, string $githubUser): ?array
    {
        $pdo = Database::getConnection();

        // 1. Procurar por identificador explícito no título ou corpo (#task-123, [task:123], #123)
        if (preg_match('/(?:#task-|task:|\btask-)(\d+)/i', $title . ' ' . $body, $m)) {
            $taskId = (int)$m[1];
            $stmt = $pdo->prepare("SELECT * FROM task_assignments WHERE id = ? LIMIT 1");
            $stmt->execute([$taskId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                return TaskAssignment::findById((int)$res['id']);
            }
        }

        // 2. Procurar por utilizador do GitHub e repositório
        if (!empty($githubUser)) {
            $stmt = $pdo->prepare("
                SELECT ta.id 
                FROM task_assignments ta
                INNER JOIN interns i ON i.id = ta.intern_id
                INNER JOIN users u ON u.id = i.user_id
                WHERE (u.github_url LIKE ? OR ta.id IN (
                    SELECT assignment_id FROM task_submissions WHERE github_repo_url LIKE ?
                ))
                AND ta.status IN ('assigned', 'in_progress', 'submitted')
                ORDER BY ta.id DESC LIMIT 1
            ");
            $stmt->execute(['%' . $githubUser . '%', '%' . basename($repoUrl) . '%']);
            $assignId = (int)$stmt->fetchColumn();
            if ($assignId > 0) {
                return TaskAssignment::findById($assignId);
            }
        }

        // 3. Fallback: tarefa mais recente vinculada a este repositório
        $stmtRepo = $pdo->prepare("
            SELECT ta.id 
            FROM task_assignments ta
            INNER JOIN task_submissions ts ON ts.assignment_id = ta.id
            WHERE ts.github_repo_url LIKE ?
            AND ta.status IN ('assigned', 'in_progress', 'submitted')
            ORDER BY ta.id DESC LIMIT 1
        ");
        $stmtRepo->execute(['%' . basename($repoUrl) . '%']);
        $assignId = (int)$stmtRepo->fetchColumn();
        if ($assignId > 0) {
            return TaskAssignment::findById($assignId);
        }

        return null;
    }
}
