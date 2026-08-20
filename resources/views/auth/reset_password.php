<div class="card shadow border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-shield-check fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Definir Nova Palavra-passe</h4>
            <p class="text-muted small">Conta: <strong><?= \App\Helpers\e($email) ?></strong></p>
        </div>

        <form action="/reset-password/<?= \App\Helpers\e($token) ?>" method="POST">
            <?= \App\Helpers\csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nova Palavra-passe * (mínimo 8 caracteres)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="new_password" class="form-control" minlength="8" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Confirmar Nova Palavra-passe *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm mb-3">
                <i class="bi bi-check2-circle me-1"></i> Gravar Nova Palavra-passe
            </button>

            <div class="text-center">
                <a href="/login" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Cancelar e ir para o Login
                </a>
            </div>
        </form>
    </div>
</div>
