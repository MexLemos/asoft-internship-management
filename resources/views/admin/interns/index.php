<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestão de Estagiários</h4>
        <p class="text-muted small mb-0">Controle completo de estagiários, períodos e orientadores da Asoftmedia.</p>
    </div>
    <a href="/admin/interns/create" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Cadastrar Estagiário
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nome Completo</th>
                        <th>BI</th>
                        <th>Instituição & Curso</th>
                        <th>Supervisor</th>
                        <th>Período</th>
                        <th>Estado</th>
                        <th>Desempenho</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interns as $i): ?>
                        <tr>
                            <td><code><?= \App\Helpers\e($i['internship_code']) ?></code></td>
                            <td>
                                <strong><?= \App\Helpers\e($i['full_name']) ?></strong><br>
                                <span class="small text-muted"><?= \App\Helpers\e($i['user_email']) ?></span>
                            </td>
                            <td class="small"><?= \App\Helpers\e($i['bi_number']) ?></td>
                            <td>
                                <div class="fw-semibold small"><?= \App\Helpers\e($i['institution_name']) ?></div>
                                <div class="text-muted small"><?= \App\Helpers\e($i['course']) ?></div>
                            </td>
                            <td class="small"><?= \App\Helpers\e($i['supervisor_name'] ?? 'Não atribuído') ?></td>
                            <td class="small">
                                <?= \App\Helpers\format_date($i['start_date']) ?> - <?= \App\Helpers\format_date($i['end_date']) ?>
                            </td>
                            <td>
                                <span class="badge bg-success">Ativo</span>
                            </td>
                            <td>
                                <span class="fw-bold fs-6"><?= number_format((float)$i['overall_score'], 1) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="/admin/interns/<?= $i['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye-fill me-1"></i> Perfil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
