<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Competency;
use App\Models\Institution;
use App\Models\Intern;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\CertificateGeneratorService;
use App\Services\PerformanceScoringEngine;
use PDO;

class InternsController extends Controller
{
    public function index(Request $request): Response
    {
        $interns = Intern::all();
        $institutions = Institution::all();

        return $this->render('admin.interns.index', [
            'title' => 'Gestão de Estagiários - Asoftmedia',
            'interns' => $interns,
            'institutions' => $institutions
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $institutions = Institution::all();
        $pdo = Database::getConnection();
        $supervisors = $pdo->query("
            SELECT u.id, u.name 
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE r.name IN ('supervisor', 'admin', 'super_admin') AND u.deleted_at IS NULL
        ")->fetchAll();

        return $this->render('admin.interns.create', [
            'title' => 'Cadastrar Novo Estagiário - Asoftmedia',
            'institutions' => $institutions,
            'supervisors' => $supervisors
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        $errors = $this->validate($data, [
            'full_name' => 'required|min:3',
            'email' => 'required|email',
            'bi_number' => 'required',
            'institution_id' => 'required|numeric',
            'course' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/admin/interns/create');
        }

        $pdo = Database::getConnection();

        // 1. Create User Account
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $data['full_name'])[0])) . '.' . rand(100, 999);
        $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare("
                INSERT INTO users (name, email, phone, username, password_hash, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmtUser->execute([
                $data['full_name'],
                $data['email'],
                $data['phone'] ?? null,
                $username,
                $passwordHash
            ]);
            $userId = (int)$pdo->lastInsertId();

            // Assign 'intern' role
            $roleId = (int)$pdo->query("SELECT id FROM roles WHERE name = 'intern'")->fetchColumn();
            $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmtRole->execute([$userId, $roleId]);

            // Generate Internship Code
            $count = (int)$pdo->query("SELECT COUNT(*) FROM interns")->fetchColumn() + 1;
            $code = 'AST-2026-' . sprintf('%03d', $count);

            $data['user_id'] = $userId;
            $data['internship_code'] = $code;
            $data['active_days'] = array_map('intval', (array)($data['days'] ?? [1, 2, 4, 5]));

            $internId = Intern::create($data);

            $pdo->commit();

            AuditLog::log('intern_create', 'interns', $internId, null, ['code' => $code, 'name' => $data['full_name']], 'success');

            Session::flash('success', "Estagiário cadastrado com sucesso! Código: {$code}. Utilizador: {$username}, Palavra-passe provisória: Password123!");
            return $this->redirect('/admin/interns');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', 'Erro ao cadastrar estagiário: ' . $e->getMessage());
            return $this->redirect('/admin/interns/create');
        }
    }

    public function show(Request $request, string $id): Response
    {
        $internId = (int)$id;
        $intern = Intern::findById($internId);
        if (!$intern) {
            Session::flash('error', 'Estagiário não encontrado.');
            return $this->redirect('/admin/interns');
        }

        // Recalculate score
        $scoring = new PerformanceScoringEngine();
        $scoreData = $scoring->calculateForIntern($internId);
        $intern['overall_score'] = $scoreData['overall_score'];
        $intern['risk_level'] = $scoreData['risk_level'];

        $attendance = Attendance::getForIntern($internId, 30);
        $tasks = TaskAssignment::getForIntern($internId);
        $competencies = Competency::getForIntern($internId);
        
        $certService = new CertificateGeneratorService();
        $eligibility = $certService->checkEligibility($internId);

        return $this->render('admin.interns.show', [
            'title' => 'Perfil do Estagiário: ' . htmlspecialchars($intern['full_name']),
            'intern' => $intern,
            'attendance' => $attendance,
            'tasks' => $tasks,
            'competencies' => $competencies,
            'scoreData' => $scoreData,
            'eligibility' => $eligibility
        ], 'admin');
    }

    public function generateCertificate(Request $request, string $id): Response
    {
        $internId = (int)$id;
        $service = new CertificateGeneratorService();
        $res = $service->generateCertificate($internId);

        if (!$res['success']) {
            Session::flash('error', $res['message']);
            return $this->redirect("/admin/interns/{$internId}");
        }

        AuditLog::log('certificate_generated', 'certificates', $internId, null, [
            'code' => $res['certificate']['certificate_code']
        ], 'success');

        Session::flash('success', 'Declaração e Certificado gerados com sucesso com QR Code!');
        return $this->redirect("/admin/interns/{$internId}");
    }
}
