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
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
                <span>ASOFTMEDIA</span>
            </div>
            
            <div class="nav-heading">Administração</div>
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
            </nav>

            <div class="nav-heading">Configuração & Segurança</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') ? 'active' : '' ?>" href="/admin/settings">
                    <i class="bi bi-gear-fill"></i> Definições Globais
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/audit') ? 'active' : '' ?>" href="/admin/audit">
                    <i class="bi bi-shield-check"></i> Auditoria & Logs
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <header class="top-navbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 text-dark font-weight-bold"><?= \App\Helpers\e($title ?? 'Painel de Gestão') ?></h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <?php $user = \App\Helpers\auth_user(); ?>
                    <span class="badge bg-primary px-3 py-2">
                        <i class="bi bi-shield-lock me-1"></i> Admin: <?= \App\Helpers\e($user['name'] ?? 'Administrador') ?>
                    </span>
                    <form action="/logout" method="POST" class="d-inline mb-0">
                        <?= \App\Helpers\csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i> Sair
                        </button>
                    </form>
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
