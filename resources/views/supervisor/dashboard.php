<div class="row g-4 mb-4">
    <!-- Submissions Queue -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary">
                    <i class="bi bi-inbox-fill me-2"></i> Fila de Tarefas Aguardando Revisão
                </span>
                <span class="badge bg-primary"><?= count($pendingReviews) ?> pendentes</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Estagiário</th>
                                <th>Tarefa</th>
                                <th>Submetido em</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingReviews)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Tudo limpo! Não há tarefas pendentes de revisão.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingReviews as $pr): ?>
                                    <tr>
                                        <td>
                                            <strong><?= \App\Helpers\e($pr['intern_name']) ?></strong><br>
                                            <span class="text-muted"><?= \App\Helpers\e($pr['internship_code']) ?></span>
                                        </td>
                                        <td><strong><?= \App\Helpers\e($pr['title']) ?></strong></td>
                                        <td><?= \App\Helpers\format_date($pr['submitted_at'], true) ?></td>
                                        <td class="text-end">
                                            <a href="/supervisor/tasks/review/<?= $pr['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-check2-circle me-1"></i> Avaliar
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

    <!-- Assigned Interns -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark">
                    <i class="bi bi-people-fill me-2 text-success"></i> Meus Estagiários sob Orientação (<?= count($interns) ?>)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Curso</th>
                                <th>Desempenho</th>
                                <th>Risco</th>
                                <th class="text-end">Competências</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interns as $i): ?>
                                <tr>
                                    <td>
                                        <strong><?= \App\Helpers\e($i['full_name']) ?></strong><br>
                                        <span class="text-muted"><?= \App\Helpers\e($i['internship_code']) ?></span>
                                    </td>
                                    <td><?= \App\Helpers\e($i['course']) ?></td>
                                    <td><strong class="fs-6"><?= number_format((float)$i['overall_score'], 1) ?></strong></td>
                                    <td>
                                        <?php if ($i['risk_level'] === 'normal'): ?>
                                            <span class="badge badge-risk-normal">Normal</span>
                                        <?php elseif ($i['risk_level'] === 'attention'): ?>
                                            <span class="badge badge-risk-attention">Atenção</span>
                                        <?php else: ?>
                                            <span class="badge badge-risk-risk">Risco</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/supervisor/competencies/evaluate/<?= $i['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-award"></i> Avaliar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
