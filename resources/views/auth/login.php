<div class="card auth-card shadow-lg border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3">
                <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Asoftmedia" style="width: 76px; height: 76px; object-fit: contain;">
            </div>
            <h4 class="fw-bold text-dark mb-1">ASOFTMEDIA</h4>
            <p class="text-muted small">Gestão & Formação de Estagiários</p>
        </div>

        <?php if ($err = \App\Helpers\flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?= \App\Helpers\e($err) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($suc = \App\Helpers\flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= \App\Helpers\e($suc) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($inf = \App\Helpers\flash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show small" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i> <?= \App\Helpers\e($inf) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <?= \App\Helpers\csrf_field() ?>

            <div class="mb-3">
                <label for="identifier" class="form-label small fw-semibold">Email ou Nome de Utilizador</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="identifier" name="identifier" placeholder="ex: estagiario@asoftmedia-ao.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold mb-1">Palavra-passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="text-end mt-1">
                    <a href="/forgot-password" class="small text-decoration-none text-primary">Esqueceu a palavra-passe?</a>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mt-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sessão
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="/politica-privacidade" target="_blank" class="small text-muted text-decoration-none">
                <i class="bi bi-shield-check me-1"></i> Política de Privacidade (Lei 22/11)
            </a>
        </div>
    </div>
</div>
