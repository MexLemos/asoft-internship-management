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

        if (!$intern) {
            return $this->redirect('/login');
        }

        $internId = (int)$intern['id'];
        $courses = Course::all($internId);
        $mandatoryStats = Course::getMandatoryStatsForIntern($internId);

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
        $internId = (int)$intern['id'];

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
            'title' => 'Zona de Estudo: ' . htmlspecialchars($course['title']),
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

        if (!empty($question) && $intern) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO content_doubts (content_id, intern_id, question, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$cId, (int)$intern['id'], $question]);

            // Notify Supervisor
            if (!empty($intern['supervisor_id'])) {
                Notification::create(
                    (int)$intern['supervisor_id'],
                    'doubt',
                    'Nova Dúvida de Estagiário na Academia',
                    "{$intern['full_name']} enviou uma dúvida na aula: \"{$question}\"",
                    "/admin/doubts"
                );
            }

            Session::flash('success', 'A sua dúvida foi enviada com sucesso! O orientador responderá em breve.');
        }

        $courseId = $request->input('course_id', '1');
        return $this->redirect("/intern/academy/course/{$courseId}?content={$cId}");
    }

    public function completeContent(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);
        $contentId = (int)$id;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO lesson_progress (intern_id, content_id, status, watch_percentage, completed_at)
            VALUES (?, ?, 'completed', 100.00, NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', watch_percentage = 100.00, completed_at = NOW()
        ");
        $stmt->execute([(int)$intern['id'], $contentId]);

        return $this->json(['success' => true]);
    }
}
