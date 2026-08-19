<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Minhas Tarefas & Atividades</h4>
        <p class="text-muted small mb-0">Acompanhe suas tarefas atribuídas, submeta seus repositórios GitHub e consulte feedbacks.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título da Tarefa</th>
                        <th>Categoria</th>
                        <th>Prioridade</th>
                        <th>Prazo de Entrega</th>
                        <th>Pontos</th>
                        <th>Estado</th>
                        <th>Nota</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhuma tarefa atribuída no momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $t): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($t['title']) ?></strong>
                                    <?php if ($t['requires_github']): ?>
                                        <span class="badge bg-dark ms-1"><i class="bi bi-github"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-<?= $t['color_badge'] ?>"><?= \App\Helpers\e($t['category_name']) ?></span></td>
                                <td><span class="badge bg-light text-dark border text-uppercase"><?= \App\Helpers\e($t['priority']) ?></span></td>
                                <td class="small text-danger fw-semibold"><?= \App\Helpers\format_date($t['due_date']) ?></td>
                                <td><?= $t['points'] ?> pts</td>
                                <td>
                                    <?php if ($t['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Aprovada</span>
                                    <?php elseif ($t['status'] === 'submitted'): ?>
                                        <span class="badge bg-info text-dark">Em Avaliação</span>
                                    <?php elseif ($t['status'] === 'in_progress'): ?>
                                        <span class="badge bg-warning text-dark">Em Andamento</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Atribuída</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="fs-6"><?= $t['score'] ? number_format((float)$t['score'], 1) : '-' ?></strong></td>
                                <td class="text-end">
                                    <a href="/intern/tasks/<?= $t['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-right-circle me-1"></i> Abrir
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
