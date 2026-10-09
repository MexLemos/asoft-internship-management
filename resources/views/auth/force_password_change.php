<div class="card shadow border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-shield-lock-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Primeiro Acesso ao Sistema</h4>
            <p class="text-muted small">
                Olá, <strong><?= \App\Helpers\e($user['name'] ?? $user['username']) ?></strong>! Por motivos de segurança, é <strong>obrigatório</strong> substituir a sua senha padrão por uma senha pessoal antes de continuar.
            </p>
        </div>

        <form action="/force-password-change" method="POST">
            <?= \App\Helpers\csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nova Palavra-passe Pessoal * (mínimo 8 caracteres)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="new_password" class="form-control" minlength="8" placeholder="Digite uma senha forte..." required autofocus>
                </div>
                <div class="form-text small">Escolha uma senha que não seja óbvia ou genérica.</div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Confirmar Nova Palavra-passe *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" placeholder="Repita a nova senha..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                <i class="bi bi-shield-check me-1"></i> Gravar Senha e Aceder ao Sistema
            </button>

            <div class="text-center">
                <form action="/logout" method="POST" class="d-inline">
                    <?= \App\Helpers\csrf_field() ?>
                    <button type="submit" class="btn btn-link text-decoration-none small text-muted p-0">
                        <i class="bi bi-box-arrow-left me-1"></i> Terminar Sessão
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>
