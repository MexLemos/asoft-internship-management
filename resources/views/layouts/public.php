<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\e($title ?? 'Asoftmedia') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-mortarboard-fill text-primary me-2"></i> ASOFTMEDIA
            </a>
            <div class="d-flex align-items-center">
                <?php if (\App\Helpers\auth_check()): ?>
                    <a href="/login" class="btn btn-outline-light btn-sm">Aceder ao Meu Painel</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary btn-sm">Iniciar Sessão</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <?= $content ?>
        </div>
    </main>

    <footer class="bg-white border-top py-4 text-center text-muted small mt-auto">
        <div class="container">
            &copy; 2026 Asoftmedia - Sistema de Gestão de Estágios. Todos os direitos reservados.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
