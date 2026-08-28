<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Intern;
use App\Models\Notification;

class AcademyController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        $internId = $intern ? (int)$intern['id'] : 0;
        $courses = Course::all($internId > 0 ? $internId : null);
        $mandatoryStats = $internId > 0 ? Course::getMandatoryStatsForIntern($internId) : [
            'percentage' => 0.0,
            'completed' => 0,
            'total' => count(array_filter($courses, fn($c) => !empty($c['is_mandatory'])))
        ];

        return $this->render('intern.academy.index', [
            'title' => 'Academia Asoftmedia - Cursos & Zona de Estudo',
            'courses' => $courses,
            'mandatoryStats' => $mandatoryStats,
            'intern' => $intern
        ], 'intern');
    }

    public function course(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $internId = $intern ? (int)$intern['id'] : 0;

        $course = Course::findWithModules((int)$id, $internId);
        if (!$course) {
            Session::flash('error', 'Curso não encontrado.');
            return $this->redirect('/intern/academy');
        }

        // Active content
        $contentId = $request->input('content') ? (int)$request->input('content') : null;
        $activeContent = null;

        foreach ($course['modules'] as $mod) {
            foreach ($mod['lessons'] as $les) {
                foreach ($les['contents'] as $cnt) {
                    if ($contentId !== null && (int)$cnt['id'] === $contentId) {
                        $activeContent = $cnt;
                        break 3;
                    }
                    if ($activeContent === null) {
                        $activeContent = $cnt;
                    }
                }
            }
        }

        // Load Doubts / Q&A for this content
        $doubts = [];
        if ($activeContent) {
            $pdo = Database::getConnection();
            $stmtDoubts = $pdo->prepare("
                SELECT cd.*, i.full_name as intern_name, u.name as answerer_name
                FROM content_doubts cd
                INNER JOIN interns i ON i.id = cd.intern_id
                LEFT JOIN users u ON u.id = cd.answered_by
                WHERE cd.content_id = ?
                ORDER BY cd.created_at DESC
            ");
            $stmtDoubts->execute([(int)$activeContent['id']]);
            $doubts = $stmtDoubts->fetchAll();
        }

        return $this->render('intern.academy.study_zone', [
            'title' => 'Zona de Estudo: ' . $course['title'],
            'course' => $course,
            'activeContent' => $activeContent,
            'doubts' => $doubts,
            'intern' => $intern
        ], 'intern');
    }

    public function submitDoubt(Request $request, string $contentId): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $question = trim((string)$request->input('question', ''));
        $cId = (int)$contentId;

        if (!$intern) {
            Session::flash('error', 'Apenas estagiários matriculados podem enviar dúvidas ao orientador.');
            return $this->redirect('/intern/academy');
        }

        if (empty($question)) {
            Session::flash('error', 'Por favor, escreva a sua dúvida antes de enviar.');
            return $this->redirect("/intern/academy/course/" . $request->input('course_id', 1) . "?content={$cId}");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO content_doubts (content_id, intern_id, question, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$cId, (int)$intern['id'], $question]);

        AuditLog::log('doubt_submitted', 'academy', (int)$pdo->lastInsertId(), null, ['content_id' => $cId], 'success');

        Session::flash('success', 'A sua dúvida foi enviada com sucesso! O orientador responderá em breve.');
        return $this->redirect("/intern/academy/course/" . $request->input('course_id', 1) . "?content={$cId}");
    }

    public function completeContent(Request $request, string $contentId): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->json(['success' => false, 'message' => 'Estagiário não autenticado.'], 401);
        }

        $cId = (int)$contentId;
        $internId = (int)$intern['id'];

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO lesson_progress (intern_id, content_id, status, watch_percentage, completed_at, updated_at)
            VALUES (?, ?, 'completed', 100.0, NOW(), NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', watch_percentage = 100.0, completed_at = NOW()
        ");
        $stmt->execute([$internId, $cId]);

        AuditLog::log('content_completed', 'academy', $cId, null, null, 'success');

        return $this->json(['success' => true, 'message' => 'Aula concluída com sucesso!']);
    }
}
