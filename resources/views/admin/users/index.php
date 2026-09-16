<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestão de Funcionários & Equipa</h4>
        <p class="text-muted small mb-0">Controle de acessos, supervisores técnicos e administradores da Asoftmedia.</p>
    </div>
    <a href="/admin/users/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Novo Funcionário
    </a>
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

<!-- Resumo em Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Equipa</span>
                        <h4 class="fw-bold mb-0 mt-1"><?= count($employees) ?></h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Supervisores</span>
                        <h4 class="fw-bold mb-0 mt-1">
                            <?= count(array_filter($employees, fn($e) => str_contains($e['roles_slugs'] ?? '', 'supervisor'))) ?>
                        </h4>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                        <i class="bi bi-person-workspace fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Administradores</span>
                        <h4 class="fw-bold mb-0 mt-1">
                            <?= count(array_filter($employees, fn($e) => str_contains($e['roles_slugs'] ?? '', 'admin') || str_contains($e['roles_slugs'] ?? '', 'super_admin'))) ?>
                        </h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Ativos</span>
                        <h4 class="fw-bold mb-0 mt-1">
                            <?= count(array_filter($employees, fn($e) => $e['status'] === 'active')) ?>
                        </h4>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros e Pesquisa -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/admin/users" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" name="q" value="<?= \App\Helpers\e($query) ?>" placeholder="Pesquisar por nome, email ou utilizador...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="role">
                    <option value="">Todos os Perfis</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administradores</option>
                    <option value="supervisor" <?= $roleFilter === 'supervisor' ? 'selected' : '' ?>>Supervisores</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Todos os Estados</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Ativos</option>
                    <option value="blocked" <?= $statusFilter === 'blocked' ? 'selected' : '' ?>>Bloqueados</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-filter"></i> Filtrar</button>
                <?php if ($query !== '' || $roleFilter !== '' || $statusFilter !== ''): ?>
                    <a href="/admin/users" class="btn btn-outline-secondary" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Funcionários -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Funcionário</th>
                        <th>Contacto</th>
                        <th>Perfil / Função</th>
                        <th>Último Login</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                Nenhum funcionário encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 1.1rem;">
                                            <?= mb_strtoupper(mb_substr($emp['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-dark"><?= \App\Helpers\e($emp['name']) ?></strong>
                                            <div class="small text-muted">@<?= \App\Helpers\e($emp['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark"><?= \App\Helpers\e($emp['email']) ?></div>
                                    <div class="small text-muted"><?= \App\Helpers\e($emp['phone'] ?? 'Sem telefone') ?></div>
                                </td>
                                <td>
                                    <?php 
                                        $rolesSlugs = explode(',', $emp['roles_slugs'] ?? '');
                                        foreach ($rolesSlugs as $slug):
                                            $slug = trim($slug);
                                            if ($slug === 'super_admin'): ?>
                                                <span class="badge bg-danger">Super Admin</span>
                                            <?php elseif ($slug === 'admin'): ?>
                                                <span class="badge bg-warning text-dark">Administrador</span>
                                            <?php elseif ($slug === 'supervisor'): ?>
                                                <span class="badge bg-info text-dark">Supervisor</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= \App\Helpers\e($slug) ?></span>
                                            <?php endif;
                                        endforeach;
                                    ?>
                                </td>
                                <td class="small text-muted">
                                    <?php if (!empty($emp['last_login_at'])): ?>
                                        <span title="<?= \App\Helpers\e($emp['last_login_at']) ?>">
                                            <?= date('d/m/Y H:i', strtotime($emp['last_login_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Nunca acedeu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($emp['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Bloqueado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-pill px-2" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="/admin/users/<?= $emp['id'] ?>/edit">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Editar Dados
                                                </a>
                                            </li>
                                            <li>
                                                <form action="/admin/users/<?= $emp['id'] ?>/toggle-status" method="POST" onsubmit="return confirm('Deseja realmente alterar o estado desta conta?');">
                                                    <?= \App\Helpers\csrf_field() ?>
                                                    <button type="submit" class="dropdown-item">
                                                        <?php if ($emp['status'] === 'active'): ?>
                                                            <i class="bi bi-lock me-2 text-warning"></i> Bloquear Acesso
                                                        <?php else: ?>
                                                            <i class="bi bi-unlock me-2 text-success"></i> Desbloquear Acesso
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="/admin/users/<?= $emp['id'] ?>/delete" method="POST" onsubmit="return confirm('Atenção: Tem a certeza que pretende remover este funcionário?');">
                                                    <?= \App\Helpers\csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash3 me-2"></i> Remover Funcionário
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
