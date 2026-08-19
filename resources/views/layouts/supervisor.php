<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\csrf_token() ?>">
    <title><?= \App\Helpers\e($title ?? 'Portal do Supervisor - Asoftmedia') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <i class="bi bi-person-workspace text-primary fs-4"></i>
                <span>ASOFTMEDIA</span>
            </div>
            
            <div class="nav-heading">Orientação & Supervisão</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/supervisor/dashboard') ? 'active' : '' ?>" href="/supervisor/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/supervisor/tasks') ? 'active' : '' ?>" href="/supervisor/tasks">
                    <i class="bi bi-list-task"></i> Gestão de Tarefas
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/supervisor/competencies') ? 'active' : '' ?>" href="/supervisor/competencies">
                    <i class="bi bi-award"></i> Competências
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark font-weight-bold"><?= \App\Helpers\e($title ?? 'Painel do Supervisor') ?></h5>
                
                <div class="d-flex align-items-center gap-3">
                    <?php $user = \App\Helpers\auth_user(); ?>
                    <span class="badge bg-info text-dark px-3 py-2">
                        <i class="bi bi-person-check-fill me-1"></i> Supervisor: <?= \App\Helpers\e($user['name'] ?? 'Supervisor') ?>
                    </span>
                    <form action="/logout" method="POST" class="d-inline mb-0">
                        <?= \App\Helpers\csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i> Sair
                        </button>
                    </form>
                </div>
            </header>

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
            </div>

            <main class="content-wrapper">
                <?= $content ?>
            </main>

            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                &copy; 2026 Asoftmedia - Portal do Orientador.
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
