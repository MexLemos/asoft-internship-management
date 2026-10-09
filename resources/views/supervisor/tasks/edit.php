<?php $basePath = !empty($adminContext) ? '/admin' : '/supervisor'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-pencil-square me-2"></i> Editar Tarefa Técnica
                </h5>
                <span class="badge bg-light text-dark border">ID #<?= $task['id'] ?></span>
            </div>
            <div class="card-body p-4">
                <form action="<?= $basePath ?>/tasks/<?= $task['id'] ?>/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Título da Tarefa *</label>
                            <input type="text" name="title" class="form-control" value="<?= \App\Helpers\e($task['title']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Categoria *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $task['category_id'] ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Prioridade</label>
                            <select name="priority" class="form-select">
                                <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Baixa</option>
                                <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Média</option>
                                <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>Alta</option>
                                <option value="urgent" <?= $task['priority'] === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pontos (Gamificação) *</label>
                            <input type="number" name="points" class="form-control" value="<?= (int)$task['points'] ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prazo de Entrega (Data Limite)</label>
                            <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars((string)($task['due_date'] ?? '')) ?>">
                            <input type="hidden" name="estimated_hours" value="<?= htmlspecialchars((string)($task['estimated_hours'] ?? '4.0')) ?>">
                            <div class="form-text small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Atraso: dedução de 30%.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Estado da Tarefa</label>
                            <select name="status" class="form-select">
                                <option value="published" <?= ($task['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicada</option>
                                <option value="draft" <?= ($task['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="archived" <?= ($task['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivada</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrição Resumida *</label>
                            <textarea name="description" class="form-control" rows="3" required><?= \App\Helpers\e($task['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Instruções Passo a Passo</label>
                            <textarea name="instructions" class="form-control" rows="4" placeholder="1. Criar branch feature/...\n2. Implementar...\n3. Abrir Pull Request..."><?= \App\Helpers\e($task['instructions'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Critérios de Avaliação</label>
                            <textarea name="evaluation_criteria" class="form-control" rows="3" placeholder="Critérios técnicos e funcionais para aprovação..."><?= \App\Helpers\e($task['evaluation_criteria'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="requires_github" value="1" id="reqGithub" <?= !empty($task['requires_github']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="reqGithub">Exigir link de Repositório GitHub e Pull Request na entrega</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="<?= $basePath ?>/tasks" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
