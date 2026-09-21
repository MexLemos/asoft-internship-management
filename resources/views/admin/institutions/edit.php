<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-pencil-square me-2"></i> Editar Instituição Parceira
                </h5>
                <a href="/admin/institutions" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                </a>
            </div>
            <div class="card-body p-4">
                <form action="/admin/institutions/<?= $institution['id'] ?>/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nome Oficial da Instituição *</label>
                            <input type="text" name="name" class="form-control" value="<?= \App\Helpers\e($institution['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo de Instituição *</label>
                            <select name="type" class="form-select" required>
                                <option value="universidade" <?= $institution['type'] === 'universidade' ? 'selected' : '' ?>>Universidade / Ensino Superior</option>
                                <option value="instituto_medio" <?= $institution['type'] === 'instituto_medio' ? 'selected' : '' ?>>Instituto Médio Técnico</option>
                                <option value="colegio" <?= $institution['type'] === 'colegio' ? 'selected' : '' ?>>Colégio</option>
                                <option value="centro_formacao" <?= $institution['type'] === 'centro_formacao' ? 'selected' : '' ?>>Centro de Formação Profissional</option>
                                <option value="outro" <?= $institution['type'] === 'outro' ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIF da Instituição</label>
                            <input type="text" name="nif" class="form-control" value="<?= \App\Helpers\e($institution['nif'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Institucional *</label>
                            <input type="email" name="email" class="form-control" value="<?= \App\Helpers\e($institution['email'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefone de Contacto</label>
                            <input type="text" name="phone" class="form-control" value="<?= \App\Helpers\e($institution['phone'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Website</label>
                            <input type="url" name="website" class="form-control" value="<?= \App\Helpers\e($institution['website'] ?? '') ?>" placeholder="https://...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Estado de Atividade</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($institution['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inactive" <?= ($institution['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo / Desativado</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pessoa Responsável / Contacto</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= \App\Helpers\e($institution['contact_person'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cargo do Responsável</label>
                            <input type="text" name="contact_role" class="form-control" value="<?= \App\Helpers\e($institution['contact_role'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cidade</label>
                            <input type="text" name="city" class="form-control" value="<?= \App\Helpers\e($institution['city'] ?? 'Luanda') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Província</label>
                            <input type="text" name="province" class="form-control" value="<?= \App\Helpers\e($institution['province'] ?? 'Luanda') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Endereço Completo</label>
                            <input type="text" name="address" class="form-control" value="<?= \App\Helpers\e($institution['address'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notas / Observações</label>
                            <textarea name="notes" class="form-control" rows="3"><?= \App\Helpers\e($institution['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="/admin/institutions" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
