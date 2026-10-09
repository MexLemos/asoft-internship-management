<?php 
$basePath = !empty($adminContext) ? '/admin' : '/supervisor'; 
$currentSort = $filters['sort'] ?? 'id';
$currentDir = $filters['direction'] ?? 'desc';

function sortUrlHelper($column, $currentSort, $currentDir, $filters, $basePath) {
    $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($filters, ['sort' => $column, 'direction' => $nextDir, 'page' => 1]);
    return $basePath . '/tasks?' . http_build_query($params);
}

function sortIconHelper($column, $currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted opacity-50 small ms-1"></i>';
    }
    return $currentDir === 'asc' 
        ? '<i class="bi bi-sort-up text-primary small ms-1"></i>' 
        : '<i class="bi bi-sort-down text-primary small ms-1"></i>';
}
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tarefas & Atividades Práticas</h4>
        <p class="text-muted small mb-0">Crie desafios técnicos com prazos, critérios de avaliação e atribua aos estagiários.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAssign">
            <i class="bi bi-person-check me-1"></i> Atribuir Tarefa
        </button>
        <a href="<?= $basePath ?>/tasks/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nova Tarefa
        </a>
    </div>
</div>

<!-- Filtros de Pesquisa e Ordenação -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="<?= $basePath ?>/tasks" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="sort" value="<?= \App\Helpers\e($currentSort) ?>">
            <input type="hidden" name="direction" value="<?= \App\Helpers\e($currentDir) ?>">
            
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por título ou descrição..." value="<?= \App\Helpers\e($filters['search'] ?? '') ?>">
                </div>
            </div>
            
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (!empty($filters['category_id']) && (int)$filters['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= \App\Helpers\e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">Prioridade: Todas</option>
                    <option value="low" <?= (($filters['priority'] ?? '') === 'low') ? 'selected' : '' ?>>Baixa</option>
                    <option value="medium" <?= (($filters['priority'] ?? '') === 'medium') ? 'selected' : '' ?>>Média</option>
                    <option value="high" <?= (($filters['priority'] ?? '') === 'high') ? 'selected' : '' ?>>Alta</option>
                    <option value="urgent" <?= (($filters['priority'] ?? '') === 'urgent') ? 'selected' : '' ?>>Urgente</option>
                </select>
            </div>
            
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['category_id']) || !empty($filters['priority'])): ?>
                    <a href="<?= $basePath ?>/tasks" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="<?= sortUrlHelper('title', $currentSort, $currentDir, $filters, $basePath) ?>" class="text-decoration-none text-dark fw-bold">
                                Título da Tarefa <?= sortIconHelper('title', $currentSort, $currentDir) ?>
                            </a>
                        </th>
                        <th>Categoria</th>
                        <th>
                            <a href="<?= sortUrlHelper('priority', $currentSort, $currentDir, $filters, $basePath) ?>" class="text-decoration-none text-dark fw-bold">
                                Prioridade <?= sortIconHelper('priority', $currentSort, $currentDir) ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?= sortUrlHelper('points', $currentSort, $currentDir, $filters, $basePath) ?>" class="text-decoration-none text-dark fw-bold">
                                Pontos <?= sortIconHelper('points', $currentSort, $currentDir) ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?= sortUrlHelper('due_date', $currentSort, $currentDir, $filters, $basePath) ?>" class="text-decoration-none text-dark fw-bold">
                                Prazo <?= sortIconHelper('due_date', $currentSort, $currentDir) ?>
                            </a>
                        </th>
                        <th>GitHub?</th>
                        <th>Atribuídos</th>
                        <th>Aprovados</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Nenhuma tarefa encontrada com os filtros selecionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $t): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($t['title']) ?></strong><br>
                                    <span class="small text-muted"><?= \App\Helpers\truncate_text($t['description'], 65) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $t['color_badge'] ?>"><?= \App\Helpers\e($t['category_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase"><?= \App\Helpers\e($t['priority']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= $t['points'] ?> pts</span>
                                    <span class="text-muted small d-block"><?= $t['estimated_hours'] ?>h est.</span>
                                </td>
                                <td>
                                    <?php if (!empty($t['due_date'])): ?>
                                        <?php 
                                            $isPast = strtotime($t['due_date']) < strtotime(date('Y-m-d')); 
                                        ?>
                                        <span class="badge <?= $isPast ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-light text-dark border' ?>">
                                            <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($t['due_date'])) ?>
                                        </span>
                                        <?php if ($isPast): ?>
                                            <div class="small text-danger" style="font-size: 0.72rem;">Expirado (-30%)</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Sem prazo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['requires_github']): ?>
                                        <span class="badge bg-dark"><i class="bi bi-github me-1"></i> Obrigatório</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= $t['total_assigned'] ?> alunos</span></td>
                                <td><span class="badge bg-success"><?= $t['total_approved'] ?> aprovados</span></td>
                                <td class="text-end">
                                    <a href="<?= $basePath ?>/tasks/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Editar Tarefa">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="card-footer bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
            <div class="text-muted small mb-2 mb-md-0">
                Mostrando <strong><?= count($tasks) ?></strong> de <strong><?= $pagination['total'] ?></strong> tarefas (Página <?= $pagination['page'] ?> de <?= $pagination['total_pages'] ?>)
            </div>
            <nav aria-label="Navegação de tarefas">
                <ul class="pagination pagination-sm mb-0">
                    <?php 
                    $pageHelper = function($p) use ($filters, $basePath) {
                        return $basePath . '/tasks?' . http_build_query(array_merge($filters, ['page' => $p]));
                    };
                    ?>
                    <li class="page-item <?= ($pagination['page'] <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $pageHelper(max(1, $pagination['page'] - 1)) ?>">&laquo; Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['page']) <= 2): ?>
                            <li class="page-item <?= ($i === $pagination['page']) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $pageHelper($i) ?>"><?= $i ?></a>
                            </li>
                        <?php elseif ($i == 2 || $i == $pagination['total_pages'] - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <li class="page-item <?= ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $pageHelper(min($pagination['total_pages'], $pagination['page'] + 1)) ?>">Próxima &raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Assign Task (With Bulk Option) -->
<div class="modal fade" id="modalAssign" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= $basePath ?>/tasks/assign" method="POST" id="formAssignTask" onsubmit="return confirmBulkAssign()">
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
