<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Instituições de Ensino Parceiras</h4>
        <p class="text-muted small mb-0">Universidades, institutos médios e centros de formação conveniados com a Asoftmedia.</p>
    </div>
    <a href="/admin/institutions/create" class="btn btn-primary">
        <i class="bi bi-building-add me-1"></i> Nova Instituição
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome da Instituição</th>
                        <th>Tipo</th>
                        <th>NIF</th>
                        <th>Contacto Principal</th>
                        <th>Email / Telefone</th>
                        <th>Estagiários</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($institutions as $inst): ?>
                        <tr>
                            <td>
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
                            <td class="small">
                                <?= \App\Helpers\e($inst['email'] ?? '-') ?><br>
                                <?= \App\Helpers\e($inst['phone'] ?? '-') ?>
                            </td>
                            <td>
                                <span class="badge bg-primary fs-6"><?= $inst['total_interns'] ?? 0 ?> alunos</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Ativo</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
