<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\csrf_token() ?>">
    <title><?= \App\Helpers\e($title ?? 'Portal do Estagiário - Asoftmedia') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Leaflet CSS for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="/assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
                <span>ASOFT ACADEMY</span>
            </div>
            
            <div class="nav-heading">Área do Aluno</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/dashboard') ? 'active' : '' ?>" href="/intern/dashboard">
                    <i class="bi bi-house-door-fill"></i> Meu Painel
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/attendance') ? 'active' : '' ?>" href="/intern/attendance">
                    <i class="bi bi-geo-alt-fill text-danger"></i> Marcar Presença
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/tasks') ? 'active' : '' ?>" href="/intern/tasks">
                    <i class="bi bi-check2-square text-success"></i> Tarefas & Projetos
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/academy') ? 'active' : '' ?>" href="/intern/academy">
                    <i class="bi bi-play-circle-fill text-primary"></i> Zona de Estudo
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/portfolio') ? 'active' : '' ?>" href="/intern/portfolio">
                    <i class="bi bi-briefcase-fill text-warning"></i> Portfólio & LinkedIn
                </a>
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/intern/certificate') ? 'active' : '' ?>" href="/intern/certificate">
                    <i class="bi bi-patch-check-fill text-info"></i> Certificado & QR Code
                </a>
            </nav>

            <div class="nav-heading mt-3">Conta & Privacidade</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/profile') && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'privacy') ? 'active' : '' ?>" href="/profile">
                    <i class="bi bi-person-circle"></i> Meu Perfil
                </a>
                <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/profile/privacy') ? 'active' : '' ?>" href="/profile/privacy">
                    <i class="bi bi-shield-check text-info"></i> Privacidade (Lei 22/11)
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="top-navbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()" title="Voltar para a página anterior">
                        <i class="bi bi-arrow-left me-1"></i> Voltar
                    </button>
                    <h5 class="mb-0 text-dark font-weight-bold"><?= \App\Helpers\e($title ?? 'Portal de Aprendizagem') ?></h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <?php $user = \App\Helpers\auth_user(); ?>
                    
                    <a href="/profile" class="text-decoration-none d-flex align-items-center gap-2 text-dark">
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-person-fill me-1"></i> <?= \App\Helpers\e($user['name'] ?? 'Estagiário') ?>
                        </span>
                    </a>

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

                <?php if ($warning = \App\Helpers\flash('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= \App\Helpers\e($warning) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>

            <main class="content-wrapper">
                <?= $content ?>
            </main>

            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                &copy; 2026 Asoftmedia - Plataforma de Formação e Estágios • <a href="/politica-privacidade" target="_blank" class="text-decoration-none">Privacidade (Lei 22/11)</a>
            </footer>
        </div>
    </div>

    <!-- Leaflet JS for Geofence Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/geolocation.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
