<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="/admin/users" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Voltar à lista de funcionários</a>
    </div>
    <h4 class="fw-bold mb-1">Cadastrar Novo Funcionário</h4>
    <p class="text-muted small mb-0">Adicione um novo membro à equipa interna da Asoftmedia com privilégios de gestão ou supervisão.</p>
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
                <form action="/admin/users/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Informações Pessoais & Contacto</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold">Nome Completo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="ex: Manuel António da Silva" required autofocus>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Email Corporativo / Pessoal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ex: manuel.silva@asoftmedia.ao" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold">Telefone / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="ex: +244 923 000 000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold">Estado Inicial da Conta</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Ativo (Acesso Imediato)</option>
                                <option value="blocked">Bloqueado / Inativo</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Credenciais de Acesso & Perfil</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label small fw-semibold">Nome de Utilizador (Username) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="ex: manuel.silva" required>
                            </div>
                            <div class="form-text small">Usado para iniciar sessão no portal.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label small fw-semibold">Função / Perfil no Sistema <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $role['name'] === 'supervisor' ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($role['display_name']) ?> - <?= \App\Helpers\e($role['description'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold">Palavra-passe Provisória <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="generateRandomPass()">
                                <i class="bi bi-shuffle me-1"></i> Gerar Senha Segura
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="/admin/users" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Gravar Funcionário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function generateRandomPass() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const input = document.getElementById('password');
    input.value = pass;
    input.type = 'text';
    alert('Palavra-passe gerada: ' + pass + '\nCopie e envie ao funcionário.');
}
</script>
