<?php

declare(strict_types=1);

namespace App\Controllers\Supervisor;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Course;

/**
 * Supervisor: Gestão de Cursos & Zona de Estudo
 * Mirrors Admin CoursesController but renders with the supervisor layout.
 */
class CoursesController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::allAdmin();

        return $this->render('admin.courses.index', [
            'title'   => 'Cursos & Zona de Estudo - Asoftmedia',
            'courses' => $courses,
            'supervisorContext' => true,
        ], 'supervisor');
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.courses.create', [
            'title' => 'Criar Novo Curso - Academia Asoftmedia',
            'supervisorContext' => true,
        ], 'supervisor');
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        $errors = $this->validate($data, [
            'title'  => 'required|min:3',
            'status' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/supervisor/courses/create');
        }

        $courseId = Course::create($data);
        AuditLog::log('course_create', 'courses', $courseId, null, ['title' => $data['title']], 'success');

        Session::flash('success', "Curso '{$data['title']}' criado com sucesso! Adicione agora os módulos e aulas.");
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function edit(Request $request, string $id): Response
    {
        $courseId = (int)$id;
        $course = Course::findWithStructure($courseId);

        if (!$course) {
            Session::flash('error', 'Curso não encontrado.');
            return $this->redirect('/supervisor/courses');
        }

        return $this->render('admin.courses.edit', [
            'title'  => 'Gerir Estrutura do Curso: ' . $course['title'],
            'course' => $course,
            'supervisorContext' => true,
        ], 'supervisor');
    }

    public function update(Request $request, string $id): Response
    {
        $courseId = (int)$id;
        $data = $request->all();

        $errors = $this->validate($data, [
            'title'  => 'required|min:3',
            'status' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect("/supervisor/courses/{$courseId}/edit");
        }

        Course::update($courseId, $data);
        AuditLog::log('course_update', 'courses', $courseId, null, ['title' => $data['title']], 'success');

        Session::flash('success', 'Dados do curso atualizados com sucesso!');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function delete(Request $request, string $id): Response
    {
        $courseId = (int)$id;
        Course::delete($courseId);
        AuditLog::log('course_delete', 'courses', $courseId, null, null, 'success');

        Session::flash('success', 'Curso removido da academia com sucesso.');
        return $this->redirect('/supervisor/courses');
    }

    public function addModule(Request $request, string $courseId): Response
    {
        $cId   = (int)$courseId;
        $title = trim((string)$request->input('title', ''));
        $description = trim((string)$request->input('description', ''));
        $order = (int)$request->input('order_index', 1);

        if (empty($title)) {
            Session::flash('error', 'Informe o título do módulo.');
            return $this->redirect("/supervisor/courses/{$cId}/edit");
        }

        Course::addModule($cId, $title, $description, $order);
        Session::flash('success', 'Módulo adicionado com sucesso!');
        return $this->redirect("/supervisor/courses/{$cId}/edit");
    }

    public function deleteModule(Request $request, string $moduleId): Response
    {
        $mId      = (int)$moduleId;
        $courseId = (int)$request->input('course_id', 1);
        Course::deleteModule($mId);
        Session::flash('success', 'Módulo removido com sucesso.');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function addLesson(Request $request, string $moduleId): Response
    {
        $mId      = (int)$moduleId;
        $courseId = (int)$request->input('course_id', 1);
        $title    = trim((string)$request->input('title', ''));
        $order    = (int)$request->input('order_index', 1);

        if (empty($title)) {
            Session::flash('error', 'Informe o título da aula.');
            return $this->redirect("/supervisor/courses/{$courseId}/edit");
        }

        Course::addLesson($mId, $title, $order);
        Session::flash('success', 'Aula criada com sucesso!');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function deleteLesson(Request $request, string $lessonId): Response
    {
        $lId      = (int)$lessonId;
        $courseId = (int)$request->input('course_id', 1);
        Course::deleteLesson($lId);
        Session::flash('success', 'Aula removida com sucesso.');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function addContent(Request $request, string $lessonId): Response
    {
        $lId        = (int)$lessonId;
        $courseId   = (int)$request->input('course_id', 1);
        $title      = trim((string)$request->input('title', ''));
        $type       = (string)$request->input('content_type', 'youtube_video');
        $duration   = (int)$request->input('duration_minutes', 10);
        $order      = (int)$request->input('order_index', 1);
        $articleBody = trim((string)$request->input('article_body', ''));
        $urlOrPath  = trim((string)$request->input('content_url_or_path', ''));

        // Handle PDF upload
        if ($type === 'pdf_document' && !empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                Session::flash('error', 'Apenas ficheiros PDF são permitidos para documentos PDF.');
                return $this->redirect("/supervisor/courses/{$courseId}/edit");
            }

            $targetDir = dirname(__DIR__, 3) . '/public/uploads/materials/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = 'material_' . bin2hex(random_bytes(6)) . '.pdf';
            move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetDir . $fileName);
            $urlOrPath = '/uploads/materials/' . $fileName;
        }

        if (empty($title)) {
            Session::flash('error', 'Informe o título do conteúdo.');
            return $this->redirect("/supervisor/courses/{$courseId}/edit");
        }

        if ($type !== 'text_document' && $type !== 'article_html' && empty($urlOrPath)) {
            Session::flash('error', 'Informe a URL do vídeo ou envie o ficheiro PDF.');
            return $this->redirect("/supervisor/courses/{$courseId}/edit");
        }

        Course::addContent($lId, $title, $type, $urlOrPath, $articleBody, $duration, $order);
        Session::flash('success', 'Conteúdo adicionado com sucesso à Zona de Estudo!');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }

    public function deleteContent(Request $request, string $contentId): Response
    {
        $cId      = (int)$contentId;
        $courseId = (int)$request->input('course_id', 1);
        Course::deleteContent($cId);
        Session::flash('success', 'Conteúdo removido da aula.');
        return $this->redirect("/supervisor/courses/{$courseId}/edit");
    }
}
