<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestão de Estagiários</h4>
        <p class="text-muted small mb-0">Relação oficial de alunos integrados ao programa de estágio da Asoftmedia.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/interns/create" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Novo Estagiário
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="/admin/interns" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="sort" value="<?= \App\Helpers\e($sortBy) ?>">
            <input type="hidden" name="dir" value="<?= \App\Helpers\e($sortDir) ?>">

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Pesquisar por nome, email, curso, instituição, código..." value="<?= \App\Helpers\e($search) ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Todos os Estados</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>🟢 Ativo</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>🔵 Concluído</option>
                    <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>🔴 Suspenso</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="/admin/interns" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Interns Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=name&dir=<?= ($sortBy === 'name' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Nome / Código
                                <i class="bi <?= $sortBy === 'name' ? ($sortDir === 'ASC' ? 'bi-sort-alpha-down text-primary' : 'bi-sort-alpha-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=course&dir=<?= ($sortBy === 'course' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Curso & Nível
                                <i class="bi <?= $sortBy === 'course' ? ($sortDir === 'ASC' ? 'bi-sort-alpha-down text-primary' : 'bi-sort-alpha-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=institution&dir=<?= ($sortBy === 'institution' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Instituição
                                <i class="bi <?= $sortBy === 'institution' ? ($sortDir === 'ASC' ? 'bi-sort-alpha-down text-primary' : 'bi-sort-alpha-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=start_date&dir=<?= ($sortBy === 'start_date' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Início
                                <i class="bi <?= $sortBy === 'start_date' ? ($sortDir === 'ASC' ? 'bi-sort-numeric-down text-primary' : 'bi-sort-numeric-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=end_date&dir=<?= ($sortBy === 'end_date' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Conclusão Prevista
                                <i class="bi <?= $sortBy === 'end_date' ? ($sortDir === 'ASC' ? 'bi-sort-numeric-down text-primary' : 'bi-sort-numeric-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th>Desempenho</th>
                        <th class="sortable">
                            <a href="/admin/interns?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=status&dir=<?= ($sortBy === 'status' && $sortDir === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                Estado
                                <i class="bi <?= $sortBy === 'status' ? ($sortDir === 'ASC' ? 'bi-sort-down text-primary' : 'bi-sort-up text-primary') : 'bi-arrow-down-up text-muted' ?>"></i>
                            </a>
                        </th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($interns)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhum estagiário encontrado para os filtros informados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($interns as $i): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($i['full_name']) ?></strong><br>
                                    <code class="small text-muted"><?= \App\Helpers\e($i['internship_code']) ?></code>
                                </td>
                                <td>
                                    <span><?= \App\Helpers\e($i['course']) ?></span><br>
                                    <span class="badge bg-light text-dark border"><?= \App\Helpers\e($i['formation_level'] ?? '13ª') ?></span>
                                    <span class="badge bg-secondary"><?= \App\Helpers\e($i['internship_area'] ?? 'Geral') ?></span>
                                </td>
                                <td><?= \App\Helpers\e($i['institution_name']) ?></td>
                                <td><?= \App\Helpers\format_date($i['start_date']) ?></td>
                                <td>
                                    <strong><?= \App\Helpers\format_date($i['end_date']) ?></strong>
                                    <div class="text-muted" style="font-size: 10px;">Sexta-feira</div>
                                </td>
                                <td>
                                    <strong class="fs-6"><?= number_format((float)$i['overall_score'], 1) ?></strong>/100
                                    <div class="mt-1">
                                        <?php if ($i['risk_level'] === 'normal'): ?>
                                            <span class="badge badge-risk-normal">Normal</span>
                                        <?php elseif ($i['risk_level'] === 'attention'): ?>
                                            <span class="badge badge-risk-attention">Atenção</span>
                                        <?php else: ?>
                                            <span class="badge badge-risk-risk">Risco</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $i['display_badge']['class'] ?> px-2 py-1">
                                        <i class="bi <?= $i['display_badge']['icon'] ?> me-1"></i> <?= $i['display_badge']['label'] ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/interns/<?= $i['id'] ?>" class="btn btn-outline-primary btn-sm" title="Ver Perfil Completo">
                                        <i class="bi bi-eye-fill"></i> Abrir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                <div class="text-muted small">
                    Mostrando página <strong><?= $pagination['page'] ?></strong> de <strong><?= $pagination['total_pages'] ?></strong> (<?= $pagination['total'] ?> estagiários no total)
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="/admin/interns?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sortBy) ?>&dir=<?= urlencode($sortDir) ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
