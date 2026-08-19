<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tarefas & Atividades Práticas</h4>
        <p class="text-muted small mb-0">Crie desafios técnicos, defina critérios de avaliação e atribua aos estagiários.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAssign">
            <i class="bi bi-person-check me-1"></i> Atribuir Tarefa
        </button>
        <a href="/supervisor/tasks/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nova Tarefa
        </a>
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
                        <th>Pontos / Horas</th>
                        <th>GitHub?</th>
                        <th>Atribuídos</th>
                        <th>Aprovados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Helpers\e($t['title']) ?></strong><br>
                                <span class="small text-muted"><?= \App\Helpers\truncate_text($t['description'], 70) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $t['color_badge'] ?>"><?= \App\Helpers\e($t['category_name']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase"><?= \App\Helpers\e($t['priority']) ?></span>
                            </td>
                            <td><?= $t['points'] ?> pts (<?= $t['estimated_hours'] ?>h)</td>
                            <td>
                                <?php if ($t['requires_github']): ?>
                                    <span class="badge bg-dark"><i class="bi bi-github me-1"></i> Exigido</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Não</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= $t['total_assigned'] ?> alunos</span></td>
                            <td><span class="badge bg-success"><?= $t['total_approved'] ?> aprovados</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Assign Task -->
<div class="modal fade" id="modalAssign" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/supervisor/tasks/assign" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Atribuir Tarefa ao Estagiário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Selecionar Tarefa *</label>
                        <select name="task_id" class="form-select" required>
                            <?php foreach ($tasks as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= \App\Helpers\e($t['title']) ?> (<?= $t['category_name'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Selecionar Estagiário *</label>
                        <select name="intern_id" class="form-select" required>
                            <?php foreach ($interns as $i): ?>
                                <option value="<?= $i['id'] ?>"><?= \App\Helpers\e($i['full_name']) ?> (<?= \App\Helpers\e($i['internship_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Data Início *</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Prazo de Entrega *</label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Atribuir Agora</button>
                </div>
            </form>
        </div>
    </div>
</div>
