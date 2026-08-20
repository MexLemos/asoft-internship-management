<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\csrf_token() ?>">
    <title><?= \App\Helpers\e($title ?? 'Asoftmedia Internship System') ?></title>
    
    <!-- Bootstrap 5 CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Fixed Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
                <span>ASOFTMEDIA</span>
            </div>
            
            <!-- 1. Gestão -->
            <div class="nav-heading">Gestão</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') ? 'active' : '' ?>" href="/admin/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/interns') ? 'active' : '' ?>" href="/admin/interns">
                    <i class="bi bi-people-fill"></i> Estagiários
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/institutions') ? 'active' : '' ?>" href="/admin/institutions">
                    <i class="bi bi-building"></i> Instituições
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/supervisors') ? 'active' : '' ?>" href="/admin/supervisors">
                    <i class="bi bi-person-badge"></i> Supervisores
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'active' : '' ?>" href="/admin/users">
                    <i class="bi bi-person-gear"></i> Usuários & Perfis
                </a>
            </nav>

            <!-- 2. Formação -->
            <div class="nav-heading">Formação</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/courses') ? 'active' : '' ?>" href="/admin/courses">
                    <i class="bi bi-book-half"></i> Cursos & Aulas
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/tests') ? 'active' : '' ?>" href="/admin/tests">
                    <i class="bi bi-patch-question"></i> Testes Online
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/doubts') ? 'active' : '' ?>" href="/admin/doubts">
                    <i class="bi bi-question-circle"></i> Dúvidas dos Alunos
                </a>
            </nav>

            <!-- 3. Avaliação -->
            <div class="nav-heading">Avaliação</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/competencies') ? 'active' : '' ?>" href="/admin/competencies">
                    <i class="bi bi-award-fill"></i> Matriz Competências
                </a>
            </nav>

            <!-- 4. Operação -->
            <div class="nav-heading">Operação</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/tasks') ? 'active' : '' ?>" href="/admin/tasks">
                    <i class="bi bi-list-task"></i> Tarefas Práticas
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/attendance') ? 'active' : '' ?>" href="/admin/attendance">
                    <i class="bi bi-geo-alt-fill"></i> Presenças GPS
                </a>
            </nav>

            <!-- 5. Documentos -->
            <div class="nav-heading">Documentos</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/certificates') ? 'active' : '' ?>" href="/admin/certificates">
                    <i class="bi bi-patch-check-fill"></i> Declarações & Certificados
                </a>
            </nav>

            <!-- 6. Comunicação -->
            <div class="nav-heading">Comunicação</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/messages') ? 'active' : '' ?>" href="/admin/messages">
                    <i class="bi bi-chat-dots-fill"></i> Mensagens Institucionais
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/notifications') ? 'active' : '' ?>" href="/notifications">
                    <i class="bi bi-bell-fill"></i> Notificações
                </a>
            </nav>

            <!-- 7. Sistema -->
            <div class="nav-heading">Sistema</div>
            <nav class="nav flex-column mb-4">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') ? 'active' : '' ?>" href="/admin/settings">
                    <i class="bi bi-sliders"></i> Configurações
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/privacy') ? 'active' : '' ?>" href="/admin/privacy">
                    <i class="bi bi-shield-lock-fill"></i> Privacidade (Lei 22/11)
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/audit') ? 'active' : '' ?>" href="/admin/audit">
                    <i class="bi bi-journal-text"></i> Auditoria & Logs
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <header class="top-navbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <?php if (($_SERVER['REQUEST_URI'] ?? '') !== '/admin/dashboard'): ?>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm btn-back">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    <?php endif; ?>
                    <h5 class="mb-0 text-dark fw-bold"><?= \App\Helpers\e($title ?? 'Painel de Gestão') ?></h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Notification Bell -->
                    <a href="/notifications" class="notification-bell text-decoration-none">
                        <i class="bi bi-bell"></i>
                        <?php 
                        $unreadCount = \App\Models\AuditLog::getUnreadNotificationsCount((int)\App\Helpers\auth_user()['id']); 
                        if ($unreadCount > 0): ?>
                            <span class="badge bg-danger notification-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-6 text-primary"></i>
                            <span class="fw-semibold small"><?= \App\Helpers\e(\App\Helpers\auth_user()['name'] ?? 'Administrador') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item small" href="/profile">
                                    <i class="bi bi-person me-2"></i> Meu Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small" href="/profile/privacy">
                                    <i class="bi bi-shield-check me-2"></i> Privacidade & Dados
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/logout" method="POST" class="d-inline mb-0 w-100">
                                    <?= \App\Helpers\csrf_field() ?>
                                    <button type="submit" class="dropdown-item small text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Alerts Partial -->
            <div class="px-4 pt-3">
                <?php if ($success = \App\Helpers\flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= \App\Helpers\e($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error = \App\Helpers\flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= \App\Helpers\e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($warning = \App\Helpers\flash('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= \App\Helpers\e($warning) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Page Content -->
            <main class="content-wrapper">
                <?= $content ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                &copy; 2026 Asoftmedia - Sistema Integrado de Gestão e Formação de Estagiários (AIMS).
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
