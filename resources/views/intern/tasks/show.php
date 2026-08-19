<div class="row g-4">
    <!-- Task Description -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-5">
                    <?= \App\Helpers\e($assignment['title']) ?>
                </span>
                <span class="badge bg-<?= $assignment['color_badge'] ?>"><?= \App\Helpers\e($assignment['category_name']) ?></span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 mb-3 small">
                    <span class="text-muted"><i class="bi bi-calendar me-1"></i> Início: <?= \App\Helpers\format_date($assignment['start_date']) ?></span>
                    <span class="text-danger fw-semibold"><i class="bi bi-alarm me-1"></i> Prazo: <?= \App\Helpers\format_date($assignment['due_date']) ?></span>
                    <span class="text-primary fw-semibold"><i class="bi bi-award me-1"></i> <?= $assignment['points'] ?> Pontos</span>
                </div>

                <h6 class="fw-bold text-dark border-bottom pb-2">Objetivo da Tarefa</h6>
                <p class="small text-muted mb-4"><?= \App\Helpers\e($assignment['objective'] ?? $assignment['description']) ?></p>

                <h6 class="fw-bold text-dark border-bottom pb-2">Instruções de Execução</h6>
                <div class="p-3 bg-light rounded-3 text-dark small mb-4" style="white-space: pre-line;">
                    <?= \App\Helpers\e($assignment['instructions'] ?? $assignment['description']) ?>
                </div>

                <!-- Supervisor Feedback Banner if reviewed -->
                <?php if (!empty($assignment['supervisor_feedback'])): ?>
                    <div class="p-3 border rounded-3 bg-success bg-opacity-10 border-success mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-success"><i class="bi bi-chat-quote-fill me-1"></i> Feedback do Supervisor:</strong>
                            <span class="badge bg-success fs-6">Nota: <?= number_format((float)$assignment['score'], 1) ?> / 100</span>
                        </div>
                        <p class="small text-dark mb-0"><?= \App\Helpers\e($assignment['supervisor_feedback']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Comments Thread -->
                <h6 class="fw-bold text-dark border-bottom pb-2">Comentários & Dúvidas com o Orientador</h6>
                <div class="mb-3">
                    <?php if (empty($assignment['comments'])): ?>
                        <p class="small text-muted py-2">Sem comentários ainda.</p>
                    <?php else: ?>
                        <?php foreach ($assignment['comments'] as $c): ?>
                            <div class="p-2 border-bottom small">
                                <strong><?= \App\Helpers\e($c['user_name']) ?>:</strong> <?= \App\Helpers\e($c['comment']) ?>
                                <div class="text-muted" style="font-size: 10px;"><?= \App\Helpers\format_date($c['created_at'], true) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form action="/intern/tasks/<?= $assignment['id'] ?>/comment" method="POST">
                    <?= \App\Helpers\csrf_field() ?>
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control form-control-sm" placeholder="Escrever mensagem..." required>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Execution & Submission Column -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-send-check-fill me-2"></i> Submissão da Tarefa
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if ($assignment['status'] === 'assigned'): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-play-circle display-4 text-primary d-block mb-3"></i>
                        <h6 class="fw-bold">Pronto para começar?</h6>
                        <p class="small text-muted mb-4">Clique no botão abaixo para marcar o início da execução da tarefa.</p>
                        <form action="/intern/tasks/<?= $assignment['id'] ?>/start" method="POST">
                            <?= \App\Helpers\csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-4">
                                <i class="bi bi-play-fill me-1"></i> Iniciar Tarefa
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <form action="/intern/tasks/<?= $assignment['id'] ?>/submit" method="POST">
                        <?= \App\Helpers\csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">URL do Repositório GitHub</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white"><i class="bi bi-github"></i></span>
                                <input type="url" name="github_repo_url" class="form-control" placeholder="https://github.com/usuario/repo">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Branch</label>
                                <input type="text" name="github_branch" class="form-control" placeholder="ex: feature/modulo-crud">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Commit Hash</label>
                                <input type="text" name="github_commit_hash" class="form-control" placeholder="ex: a1b2c3d">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Link do Pull Request (PR)</label>
                            <input type="url" name="github_pr_url" class="form-control" placeholder="https://github.com/usuario/repo/pull/1">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Notas & Resumo da Solução</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Descreva brevemente como resolveu a tarefa, bibliotecas utilizadas ou dificuldades encontradas..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                            <i class="bi bi-send-check me-1"></i> Enviar Submissão para Avaliação
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
