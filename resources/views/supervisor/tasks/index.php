<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tarefas & Atividades Práticas</h4>
        <p class="text-muted small mb-0">Crie desafios técnicos, defina critérios de avaliação e atribua aos estagiários individualmente ou em lote.</p>
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
                                    <span class="badge bg-dark"><i class="bi bi-github me-1"></i> Obrigatório</span>
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

<!-- Modal Assign Task (With Bulk Option) -->
<div class="modal fade" id="modalAssign" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/supervisor/tasks/assign" method="POST" id="formAssignTask" onsubmit="return confirmBulkAssign()">
                <?= \App\Helpers\csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-send-check me-1 text-primary"></i> Atribuir Tarefa Prática
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Selecionar Tarefa *</label>
                        <select name="task_id" class="form-select" required>
                            <?php foreach ($tasks as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= \App\Helpers\e($t['title']) ?> (<?= $t['category_name'] ?> - <?= $t['points'] ?> pts)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Destinatários -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Destinatários *</label>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="assign_type" id="typeSingle" value="single" checked onchange="toggleAssignType(this.value)">
                                <label class="form-check-label fw-semibold" for="typeSingle">
                                    Estagiário específico
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="assign_type" id="typeAll" value="all" onchange="toggleAssignType(this.value)">
                                <label class="form-check-label fw-semibold text-primary" for="typeAll">
                                    Todos os meus estagiários sob orientação (<?= count($interns) ?> alunos)
                                </label>
                                <div class="form-text small text-muted ms-4">
                                    O sistema atribuirá automaticamente apenas aos estagiários que ainda não receberam esta tarefa.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="internSelectGroup">
                        <label class="form-label small fw-semibold">Selecionar Estagiário *</label>
                        <select name="intern_id" id="internSelect" class="form-select">
                            <option value="">Selecione o estagiário...</option>
                            <?php foreach ($interns as $i): ?>
                                <option value="<?= $i['id'] ?>"><?= \App\Helpers\e($i['full_name']) ?> (<?= \App\Helpers\e($i['internship_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Início *</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Prazo Final de Entrega *</label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Confirmar Atribuição
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAssignType(type) {
    const group = document.getElementById('internSelectGroup');
    const select = document.getElementById('internSelect');
    if (type === 'all') {
        group.classList.add('d-none');
        select.removeAttribute('required');
    } else {
        group.classList.remove('d-none');
        select.setAttribute('required', 'required');
    }
}

function confirmBulkAssign() {
    const type = document.querySelector('input[name="assign_type"]:checked').value;
    if (type === 'all') {
        const total = <?= count($interns) ?>;
        return confirm(`Esta tarefa será atribuída a todos os seus ${total} estagiários sob orientação (sem duplicar para quem já a recebeu). Deseja continuar?`);
    }
    return true;
}
</script>
