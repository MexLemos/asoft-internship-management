<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Notification;

/**
 * Supervisor: Dúvidas dos Alunos
 * Allows supervisors to view and answer intern doubts.
 * Renders with the supervisor layout and redirects back to supervisor routes.
 */
class DoubtsController extends Controller
{
    public function index(Request $request): Response
    {
        $pdo    = Database::getConnection();
        $user   = Session::get('user');
        $supervisorId = (int)$user['id'];

        $filter = $request->input('filter', 'all');

        // Supervisors see doubts from interns they supervise
        $sql = "
            SELECT cd.*, 
                   i.full_name as intern_name, 
                   i.internship_code,
                   lc.title as content_title,
                   l.title as lesson_title,
                   c.title as course_title,
                   u.name as answered_by_name
            FROM content_doubts cd
            INNER JOIN interns i ON i.id = cd.intern_id
            INNER JOIN learning_contents lc ON lc.id = cd.content_id
            INNER JOIN lessons l ON l.id = lc.lesson_id
            INNER JOIN modules m ON m.id = l.module_id
            INNER JOIN courses c ON c.id = m.course_id
            LEFT JOIN users u ON u.id = cd.answered_by
            WHERE i.supervisor_id = :supervisor_id
        ";

        if ($filter === 'pending') {
            $sql .= " AND cd.answer IS NULL";
        } elseif ($filter === 'answered') {
            $sql .= " AND cd.answer IS NOT NULL";
        }

        $sql .= " ORDER BY cd.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':supervisor_id' => $supervisorId]);
        $doubts = $stmt->fetchAll();

        return $this->render('supervisor.doubts.index', [
            'title'  => 'Dúvidas dos Meus Alunos - Asoftmedia',
            'doubts' => $doubts,
            'filter' => $filter,
        ], 'supervisor');
    }

    public function answer(Request $request, string $id): Response
    {
        $doubtId = (int)$id;
        $answer  = trim((string)$request->input('answer', ''));
        $sessionUser = Session::get('user');
        $userId  = (int)$sessionUser['id'];

        if (empty($answer)) {
            Session::flash('error', 'Escreva uma resposta para enviar ao aluno.');
            return $this->redirect('/supervisor/doubts');
        }

        $pdo = Database::getConnection();

        $stmtDoubt = $pdo->prepare("
            SELECT cd.*, i.user_id as intern_user_id, i.supervisor_id, lc.title as content_title
            FROM content_doubts cd
            INNER JOIN interns i ON i.id = cd.intern_id
            INNER JOIN learning_contents lc ON lc.id = cd.content_id
            WHERE cd.id = ?
        ");
        $stmtDoubt->execute([$doubtId]);
        $doubt = $stmtDoubt->fetch();

        if (!$doubt || (int)$doubt['supervisor_id'] !== $userId) {
            Session::flash('error', 'Dúvida não encontrada ou sem permissão.');
            return $this->redirect('/supervisor/doubts');
        }

        $stmtUpd = $pdo->prepare("
            UPDATE content_doubts 
            SET answer = ?, answered_by = ?, answered_at = NOW() 
            WHERE id = ?
        ");
        $stmtUpd->execute([$answer, $userId, $doubtId]);

        // Notify intern
        if (!empty($doubt['intern_user_id'])) {
            Notification::create(
                (int)$doubt['intern_user_id'],
                'info',
                'Dúvida Respondida: ' . $doubt['content_title'],
                "O orientador respondeu à sua dúvida na aula: \"{$answer}\"",
                "/intern/academy"
            );
        }

        AuditLog::log('doubt_answered', 'academy', $doubtId, null, null, 'success');

        Session::flash('success', 'Resposta gravada e enviada ao estagiário com sucesso!');
        return $this->redirect('/supervisor/doubts');
    }
}
