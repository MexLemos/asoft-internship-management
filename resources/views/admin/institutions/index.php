<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Instituições de Ensino Parceiras</h4>
        <p class="text-muted small mb-0">Universidades, institutos médios e centros de formação conveniados com a Asoftmedia.</p>
    </div>
    <div class="d-flex gap-2">
        <form action="/admin/institutions/sync-users" method="POST" onsubmit="return confirm('Pretende gerar ou sincronizar credenciais para todas as instituições pendentes? Palavra-passe padrão: 123EstagioAsoft');">
            <?= \App\Helpers\csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary" title="Cria e vincula automaticamente contas de utilizador para instituições que ainda não tenham acesso">
                <i class="bi bi-arrow-repeat me-1"></i> Sincronizar Acessos
            </button>
        </form>
        <a href="/admin/institutions/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-building-add me-1"></i> Nova Instituição
        </a>
    </div>
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

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nome da Instituição</th>
                        <th>Tipo</th>
                        <th>NIF</th>
                        <th>Contacto Principal</th>
                        <th>Utilizador / Acesso (Role 5)</th>
                        <th>Estagiários</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($institutions as $inst): ?>
                        <tr>
                            <td class="ps-4">
                                <strong><?= \App\Helpers\e($inst['name']) ?></strong><br>
                                <span class="small text-muted"><?= \App\Helpers\e($inst['city']) ?>, <?= \App\Helpers\e($inst['province']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $inst['type']) ?></span>
                            </td>
                            <td><code><?= \App\Helpers\e($inst['nif'] ?? 'N/A') ?></code></td>
                            <td>
                                <strong><?= \App\Helpers\e($inst['contact_person'] ?? 'Não informado') ?></strong><br>
                                <span class="small text-muted"><?= \App\Helpers\e($inst['contact_role'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($inst['institution_user_id'])): ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-person-check-fill me-1"></i> <?= \App\Helpers\e($inst['institution_username']) ?>
                                        </span>
                                    </div>
                                    <span class="text-muted" style="font-size: 0.72rem;">Senha: <code>123EstagioAsoft</code></span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-clock me-1"></i> Acesso Pendente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary fs-6"><?= $inst['total_interns'] ?? 0 ?> alunos</span>
                            </td>
                            <td>
                                <?php if (($inst['status'] ?? 'active') === 'active'): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="/admin/institutions/<?= $inst['id'] ?>" class="btn btn-outline-info btn-sm" title="Ver Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/admin/institutions/<?= $inst['id'] ?>/edit" class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="/admin/institutions/<?= $inst['id'] ?>/toggle-status" method="POST" class="d-inline" onsubmit="return confirm('Pretende <?= ($inst['status'] ?? 'active') === 'active' ? 'desativar' : 'ativar' ?> esta instituição?');">
                                        <?= \App\Helpers\csrf_field() ?>
                                        <?php if (($inst['status'] ?? 'active') === 'active'): ?>
                                            <button type="submit" class="btn btn-outline-warning btn-sm" title="Desativar Instituição">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Ativar Instituição">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
