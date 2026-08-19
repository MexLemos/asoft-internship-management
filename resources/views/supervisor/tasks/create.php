<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-pencil-square me-2"></i> Criar Nova Tarefa Técnica
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/supervisor/tasks/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Título da Tarefa *</label>
                            <input type="text" name="title" class="form-control" placeholder="ex: Desenvolvimento de API RESTful em PHP 8" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Categoria *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= \App\Helpers\e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Prioridade</label>
                            <select name="priority" class="form-select">
                                <option value="low">Baixa</option>
                                <option value="medium" selected>Média</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pontos (Gamificação)</label>
                            <input type="number" name="points" class="form-control" value="100" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tempo Estimado (Horas)</label>
                            <input type="number" step="0.5" name="estimated_hours" class="form-control" value="4.0">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrição Resumida *</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Resumo do desafio..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Instruções Passo a Passo</label>
                            <textarea name="instructions" class="form-control" rows="4" placeholder="1. Criar branch feature/...\n2. Implementar...\n3. Abrir Pull Request..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="requires_github" value="1" id="reqGithub" checked>
                                <label class="form-check-label fw-semibold" for="reqGithub">Exigir link de Repositório GitHub e Pull Request na entrega</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="/supervisor/tasks" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Publicar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
