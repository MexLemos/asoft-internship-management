<?php

declare(strict_types=1);

use App\Controllers\Admin\AuditController;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\InstitutionsController;
use App\Controllers\Admin\InternsController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Institution\DashboardController as InstDashboard;
use App\Controllers\Intern\AcademyController;
use App\Controllers\Intern\AttendanceController as InternAttendance;
use App\Controllers\Intern\CertificateController as InternCert;
use App\Controllers\Intern\DashboardController as InternDashboard;
use App\Controllers\Intern\PortfolioController;
use App\Controllers\Intern\TasksController as InternTasks;
use App\Controllers\Intern\TestsController as InternTests;
use App\Controllers\Public\AuthController;
use App\Controllers\Public\CertificateValidationController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\SecurityHeadersMiddleware;

/** @var Router $router */

// Global Security Headers
// Note: router handles pipeline per route group

// Public Routes
$router->get('/', function ($request) {
    if (\App\Helpers\auth_check()) {
        $user = \App\Helpers\auth_user();
        $authService = new \App\Services\AuthService();
        $route = $authService->determineHomeRoute($user['roles'] ?? []);
        return (new \App\Core\Response())->redirect($route);
    }
    return (new \App\Core\Response())->redirect('/login');
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// Public Certificate & QR Code Validation
$router->get('/validar/{hash}', [CertificateValidationController::class, 'validate']);

// Admin Portal Routes (Roles: super_admin, admin)
$router->group([
    'prefix' => 'admin',
    'middleware' => [AuthMiddleware::class, new RoleMiddleware('super_admin', 'admin'), CsrfMiddleware::class]
], function (Router $r) {
    $r->get('/dashboard', [AdminDashboard::class, 'index']);
    
    // Interns Management
    $r->get('/interns', [InternsController::class, 'index']);
    $r->get('/interns/create', [InternsController::class, 'create']);
    $r->post('/interns/store', [InternsController::class, 'store']);
    $r->get('/interns/{id}', [InternsController::class, 'show']);
    $r->post('/interns/{id}/generate-certificate', [InternsController::class, 'generateCertificate']);

    // Institutions Management
    $r->get('/institutions', [InstitutionsController::class, 'index']);
    $r->get('/institutions/create', [InstitutionsController::class, 'create']);
    $r->post('/institutions/store', [InstitutionsController::class, 'store']);

    // Settings & Audit
    $r->get('/settings', [SettingsController::class, 'index']);
    $r->post('/settings/update', [SettingsController::class, 'update']);
    $r->get('/audit', [AuditController::class, 'index']);
});

// Supervisor Portal Routes (Role: supervisor, super_admin, admin)
$router->group([
    'prefix' => 'supervisor',
    'middleware' => [AuthMiddleware::class, new RoleMiddleware('supervisor', 'super_admin', 'admin'), CsrfMiddleware::class]
], function (Router $r) {
    $r->get('/dashboard', [\App\Controllers\Supervisor\DashboardController::class, 'index']);
    
    // Tasks
    $r->get('/tasks', [\App\Controllers\Supervisor\TasksController::class, 'index']);
    $r->get('/tasks/create', [\App\Controllers\Supervisor\TasksController::class, 'create']);
    $r->post('/tasks/store', [\App\Controllers\Supervisor\TasksController::class, 'store']);
    $r->post('/tasks/assign', [\App\Controllers\Supervisor\TasksController::class, 'assign']);
    $r->get('/tasks/review/{id}', [\App\Controllers\Supervisor\TasksController::class, 'review']);
    $r->post('/tasks/review/{id}/evaluate', [\App\Controllers\Supervisor\TasksController::class, 'submitEvaluation']);
    $r->post('/tasks/review/{id}/comment', [\App\Controllers\Supervisor\TasksController::class, 'addComment']);

    // Competencies
    $r->get('/competencies', [\App\Controllers\Supervisor\CompetenciesController::class, 'index']);
    $r->get('/competencies/evaluate/{id}', [\App\Controllers\Supervisor\CompetenciesController::class, 'evaluate']);
    $r->post('/competencies/evaluate/{id}/save', [\App\Controllers\Supervisor\CompetenciesController::class, 'save']);
});

// Intern Portal Routes (Role: intern, super_admin)
$router->group([
    'prefix' => 'intern',
    'middleware' => [AuthMiddleware::class, new RoleMiddleware('intern', 'super_admin'), CsrfMiddleware::class]
], function (Router $r) {
    $r->get('/dashboard', [InternDashboard::class, 'index']);
    
    // Attendance
    $r->get('/attendance', [InternAttendance::class, 'index']);
    $r->post('/attendance/check-in', [InternAttendance::class, 'checkIn']);
    $r->post('/attendance/check-out', [InternAttendance::class, 'checkOut']);

    // Tasks
    $r->get('/tasks', [InternTasks::class, 'index']);
    $r->get('/tasks/{id}', [InternTasks::class, 'show']);
    $r->post('/tasks/{id}/start', [InternTasks::class, 'start']);
    $r->post('/tasks/{id}/submit', [InternTasks::class, 'submit']);
    $r->post('/tasks/{id}/comment', [InternTasks::class, 'addComment']);

    // Academy
    $r->get('/academy', [AcademyController::class, 'index']);
    $r->get('/academy/course/{id}', [AcademyController::class, 'course']);
    $r->post('/academy/content/{id}/complete', [AcademyController::class, 'completeContent']);

    // Tests
    $r->get('/tests/{id}', [InternTests::class, 'show']);
    $r->post('/tests/{id}/submit', [InternTests::class, 'submit']);

    // Portfolio & Certificate
    $r->get('/portfolio', [PortfolioController::class, 'index']);
    $r->get('/certificate', [InternCert::class, 'index']);
});

// Institution Observer Portal (Role: institution, super_admin, admin)
$router->group([
    'prefix' => 'institution',
    'middleware' => [AuthMiddleware::class, new RoleMiddleware('institution', 'super_admin', 'admin'), CsrfMiddleware::class]
], function (Router $r) {
    $r->get('/dashboard', [InstDashboard::class, 'index']);
    $r->get('/interns/{id}', [InstDashboard::class, 'showIntern']);
});
