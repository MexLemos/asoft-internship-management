<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Course;
use App\Models\Intern;

class AcademyController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        $courses = Course::all();

        return $this->render('intern.academy.index', [
            'title' => 'Academia Asoftmedia - Cursos & Zona de Estudo',
            'courses' => $courses,
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

        // Search active content or select first available
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

        return $this->render('intern.academy.study_zone', [
            'title' => 'Zona de Estudo: ' . htmlspecialchars($course['title']),
            'course' => $course,
            'activeContent' => $activeContent,
            'intern' => $intern
        ], 'intern');
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
