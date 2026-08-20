<div class="card shadow border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-key-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Recuperar Palavra-passe</h4>
            <p class="text-muted small">Informe o seu email para receber um link seguro de recuperação.</p>
        </div>

        <?php if ($demoLink = \App\Helpers\flash('reset_demo_link')): ?>
            <div class="alert alert-info small">
                <strong>Modo Demonstração:</strong><br>
                Link gerado: <a href="<?= $demoLink ?>" class="alert-link"><?= $demoLink ?></a>
            </div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST">
            <?= \App\Helpers\csrf_field() ?>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Email Cadastrado no Sistema</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="seu.email@asoftmedia.ao" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                <i class="bi bi-send me-1"></i> Enviar Link de Recuperação
            </button>

            <div class="text-center">
                <a href="/login" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para o Login
                </a>
            </div>
        </form>
    </div>
</div>
