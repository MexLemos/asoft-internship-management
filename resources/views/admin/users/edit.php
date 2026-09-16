<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="/admin/users" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Voltar à lista de funcionários</a>
    </div>
    <h4 class="fw-bold mb-1">Editar Funcionário</h4>
    <p class="text-muted small mb-0">Atualizar informações de cadastro, função ou redefinir a palavra-passe do colaborador.</p>
</div>

<?php if ($err = \App\Helpers\flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
        <i class="bi bi-exclamation-circle-fill me-1"></i> <?= \App\Helpers\e($err) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="/admin/users/<?= $user['id'] ?>/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Informações Pessoais & Contacto</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold">Nome Completo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" value="<?= \App\Helpers\e($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Email Corporativo / Pessoal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= \App\Helpers\e($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold">Telefone / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= \App\Helpers\e($user['phone'] ?? '') ?>" placeholder="ex: +244 923 000 000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold">Estado da Conta</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Ativo (Acesso Permitido)</option>
                                <option value="blocked" <?= $user['status'] === 'blocked' ? 'selected' : '' ?>>Bloqueado / Inativo</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Credenciais & Função no Sistema</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label small fw-semibold">Nome de Utilizador (Username) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" class="form-control" id="username" name="username" value="<?= \App\Helpers\e($user['username']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label small fw-semibold">Função / Perfil <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= in_array($role['name'], $user['roles'] ?? []) ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($role['display_name']) ?> - <?= \App\Helpers\e($role['description'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold">Nova Palavra-passe</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Deixe em branco para manter a atual">
                            </div>
                            <div class="form-text small text-muted">Preencha apenas se pretender alterar a senha deste utilizador.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="/admin/users" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Atualizar Dados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
