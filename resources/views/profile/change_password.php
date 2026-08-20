<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-shield-lock-fill me-2"></i> Alteração de Palavra-passe
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($user['must_change_password'])): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                        <div>
                            <strong>Primeiro Acesso ao Sistema:</strong><br>
                            <span class="small">Por motivos de segurança, você deve definir uma nova palavra-passe pessoal antes de continuar.</span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/profile/update-password" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <?php if (empty($user['must_change_password'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Palavra-passe Atual *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nova Palavra-passe * (mínimo 8 caracteres)</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmar Nova Palavra-passe *</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <?php if (empty($user['must_change_password'])): ?>
                            <a href="/profile" class="btn btn-light">Cancelar</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check-circle me-1"></i> Gravar Nova Palavra-passe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
