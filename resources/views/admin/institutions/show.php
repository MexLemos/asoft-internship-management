<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/admin/institutions">Instituições</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($institution['name']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-building text-primary me-2"></i> <?= \App\Helpers\e($institution['name']) ?>
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/institutions/<?= $institution['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Editar Instituição
        </a>
        <form action="/admin/institutions/<?= $institution['id'] ?>/toggle-status" method="POST" class="d-inline" onsubmit="return confirm('Pretende alterar o estado desta instituição?');">
            <?= \App\Helpers\csrf_field() ?>
            <?php if (($institution['status'] ?? 'active') === 'active'): ?>
                <button type="submit" class="btn btn-outline-warning btn-sm text-dark">
                    <i class="bi bi-pause-circle me-1"></i> Desativar
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-play-circle me-1"></i> Reativar
                </button>
            <?php endif; ?>
        </form>
        <a href="/admin/institutions" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Institution Information Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">Informações da Instituição</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Estado:</span>
                        <?php if (($institution['status'] ?? 'active') === 'active'): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inativo</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tipo:</span>
                        <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $institution['type']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">NIF:</span>
                        <code><?= \App\Helpers\e($institution['nif'] ?? 'Não informado') ?></code>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Email:</span>
                        <strong><?= \App\Helpers\e($institution['email'] ?? 'Não informado') ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Telefone:</span>
                        <span><?= \App\Helpers\e($institution['phone'] ?? 'Não informado') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Website:</span>
                        <?php if (!empty($institution['website'])): ?>
                            <a href="<?= \App\Helpers\e($institution['website']) ?>" target="_blank" class="text-truncate" style="max-width: 150px;"><?= \App\Helpers\e($institution['website']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Localização:</span>
                        <span><?= \App\Helpers\e($institution['city'] ?? 'Luanda') ?>, <?= \App\Helpers\e($institution['province'] ?? 'Luanda') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Endereço:</span>
                        <span class="text-end"><?= \App\Helpers\e($institution['address'] ?? 'Não informado') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Responsável:</span>
                        <strong><?= \App\Helpers\e($institution['contact_person'] ?? 'Não informado') ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Cargo:</span>
                        <span><?= \App\Helpers\e($institution['contact_role'] ?? 'Coordenador') ?></span>
                    </li>
                </ul>

                <hr>

                <h6 class="fw-bold mb-2 small text-dark">Conta de Acesso (Role 5 - Instituição)</h6>
                <?php if (!empty($institution['institution_user_id'])): ?>
                    <div class="p-3 bg-light rounded-3 border small">
                        <div class="mb-1"><strong>Utilizador:</strong> <code><?= \App\Helpers\e($institution['institution_username']) ?></code></div>
                        <div class="mb-1"><strong>Email:</strong> <?= \App\Helpers\e($institution['institution_user_email'] ?? $institution['email']) ?></div>
                        <div class="text-muted">Senha Padrão: <code>123EstagioAsoft</code></div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-clock me-1"></i> Acesso institucional pendente.
                    </div>
                <?php endif; ?>

                <?php if (!empty($institution['notes'])): ?>
                    <hr>
                    <h6 class="fw-bold mb-2 small text-dark">Notas / Observações</h6>
                    <p class="small text-muted mb-0"><?= nl2br(\App\Helpers\e($institution['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Interns Table Card -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people-fill text-primary me-2"></i> Estagiários Desta Instituição
                </h6>
                <span class="badge bg-primary"><?= count($interns) ?> alunos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Estagiário</th>
                                <th>Curso / Área</th>
                                <th>Período</th>
                                <th>Desempenho</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($interns)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Nenhum aluno cadastrado para esta instituição até o momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($interns as $itn): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <strong><?= \App\Helpers\e($itn['full_name']) ?></strong><br>
                                            <code><?= \App\Helpers\e($itn['internship_code']) ?></code>
                                        </td>
                                        <td>
                                            <?= \App\Helpers\e($itn['course']) ?><br>
                                            <span class="text-muted"><?= \App\Helpers\e($itn['internship_area'] ?? 'Geral') ?></span>
                                        </td>
                                        <td>
                                            <?= \App\Helpers\format_date($itn['start_date']) ?><br>
                                            <span class="text-muted">até <?= \App\Helpers\format_date($itn['end_date']) ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?= number_format((float)$itn['overall_score'], 1) ?></strong> / 100
                                        </td>
                                        <td>
                                            <?php if ($itn['status'] === 'active'): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php elseif ($itn['status'] === 'completed'): ?>
                                                <span class="badge bg-info text-dark">Concluído</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= \App\Helpers\e($itn['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="/admin/interns/<?= $itn['id'] ?>" class="btn btn-light btn-sm border" title="Ver Perfil">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
