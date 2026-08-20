<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-person-circle me-2"></i> Meu Perfil de Utilizador
                </h5>
                <a href="/profile/change-password" class="btn btn-outline-warning text-dark btn-sm fw-bold">
                    <i class="bi bi-key-fill me-1"></i> Alterar Palavra-passe
                </a>
            </div>
            <div class="card-body p-4">
                <form action="/profile/update" method="POST" enctype="multipart/form-data">
                    <?= \App\Helpers\csrf_field() ?>

                    <!-- Profile Photo Display & Upload -->
                    <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                        <div class="position-relative">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <img src="/uploads/avatars/<?= \App\Helpers\e($user['profile_photo']) ?>" class="rounded-circle object-fit-cover shadow-sm" style="width: 80px; height: 80px;" alt="Avatar">
                            <?php else: ?>
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-fill fs-1"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Atualizar Foto de Perfil</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp">
                            <div class="form-text small">Formatos: JPG, PNG, WebP (Máx: 2MB).</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome de Exibição *</label>
                            <input type="text" name="name" class="form-control" value="<?= \App\Helpers\e($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email de Contacto</label>
                            <input type="email" class="form-control bg-light" value="<?= \App\Helpers\e($user['email']) ?>" readonly>
                            <div class="form-text small">Para alterar o email oficial, contacte a administração.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefone de Contacto</label>
                            <input type="text" name="phone" class="form-control" value="<?= \App\Helpers\e($user['phone'] ?? '') ?>" placeholder="+244 923 000 000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome de Utilizador (Login)</label>
                            <input type="text" class="form-control bg-light" value="<?= \App\Helpers\e($user['username'] ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Perfil do LinkedIn (URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-linkedin"></i></span>
                                <input type="url" name="linkedin_url" class="form-control" value="<?= \App\Helpers\e($user['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/usuario">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Perfil do GitHub (URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white"><i class="bi bi-github"></i></span>
                                <input type="url" name="github_url" class="form-control" value="<?= \App\Helpers\e($user['github_url'] ?? '') ?>" placeholder="https://github.com/usuario">
                            </div>
                        </div>
                    </div>

                    <!-- Dados Académicos Protegidos (Apenas Leitura) -->
                    <?php if ($intern): ?>
                        <h6 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-3">
                            <i class="bi bi-mortarboard-fill me-1 text-primary"></i> Informações do Estágio Curricular (Dados Protegidos)
                        </h6>
                        <div class="row g-3 p-3 bg-light rounded-3 border">
                            <div class="col-md-4">
                                <span class="text-muted small">Número do BI:</span><br>
                                <strong><?= \App\Helpers\e($intern['bi_number']) ?></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Curso:</span><br>
                                <strong><?= \App\Helpers\e($intern['course']) ?></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Área de Estágio:</span><br>
                                <strong><?= \App\Helpers\e($intern['internship_area'] ?? 'Geral') ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small">Instituição de Ensino:</span><br>
                                <strong><?= \App\Helpers\e($intern['institution_name']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small">Período de Estágio:</span><br>
                                <strong><?= \App\Helpers\format_date($intern['start_date']) ?> a <?= \App\Helpers\format_date($intern['end_date']) ?></strong>
                            </div>
                        </div>
                        <div class="form-text small text-muted mt-1">
                            * Alterações no BI, Curso, Área ou Instituição devem ser solicitadas à administração da Asoftmedia.
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
