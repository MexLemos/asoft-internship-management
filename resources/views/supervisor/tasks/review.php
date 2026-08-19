<div class="row g-4">
    <!-- Task & Submission Details -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-file-earmark-code me-2 text-primary"></i> <?= \App\Helpers\e($assignment['title']) ?>
                </span>
                <span class="badge bg-<?= $assignment['color_badge'] ?>"><?= \App\Helpers\e($assignment['category_name']) ?></span>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <h6 class="fw-bold text-muted small text-uppercase">Estagiário Responsável</h6>
                    <div class="fs-5 fw-bold text-dark"><?= \App\Helpers\e($assignment['intern_name']) ?> (<?= \App\Helpers\e($assignment['internship_code']) ?>)</div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-muted small text-uppercase">Instruções da Tarefa</h6>
                    <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-line;">
                        <?= \App\Helpers\e($assignment['instructions'] ?? $assignment['description']) ?>
                    </div>
                </div>

                <h6 class="fw-bold text-muted small text-uppercase border-bottom pb-2">Submissões & Evidências de Entrega</h6>
                <?php if (empty($assignment['submissions'])): ?>
                    <p class="text-muted small py-2">Nenhuma submissão enviada até o momento.</p>
                <?php else: ?>
                    <?php foreach ($assignment['submissions'] as $sub): ?>
                        <div class="p-3 border rounded-3 bg-white mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success">Versão #<?= $sub['version_number'] ?></span>
                                <span class="text-muted small"><?= \App\Helpers\format_date($sub['submitted_at'], true) ?></span>
                            </div>

                            <p class="small text-dark mb-2"><?= \App\Helpers\e($sub['notes'] ?? 'Sem observações adicionais.') ?></p>

                            <?php if (!empty($sub['github_repo_url']) || !empty($sub['github_pr_url'])): ?>
                                <div class="bg-dark text-white p-3 rounded-2 small mt-2">
                                    <div class="fw-bold mb-1"><i class="bi bi-github me-1"></i> Integração GitHub:</div>
                                    <?php if (!empty($sub['github_repo_url'])): ?>
                                        <div>Repositório: <a href="<?= \App\Helpers\e($sub['github_repo_url']) ?>" target="_blank" class="text-info"><?= \App\Helpers\e($sub['github_repo_url']) ?></a></div>
                                    <?php endif; ?>
                                    <?php if (!empty($sub['github_branch'])): ?>
                                        <div>Branch: <code><?= \App\Helpers\e($sub['github_branch']) ?></code></div>
                                    <?php endif; ?>
                                    <?php if (!empty($sub['github_pr_url'])): ?>
                                        <div>Pull Request: <a href="<?= \App\Helpers\e($sub['github_pr_url']) ?>" target="_blank" class="text-warning fw-bold"><?= \App\Helpers\e($sub['github_pr_url']) ?></a></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Comments Thread -->
                <h6 class="fw-bold text-muted small text-uppercase border-bottom pb-2 mt-4">Comentários & Feedback em Tempo Real</h6>
                <div class="mb-3">
                    <?php foreach ($assignment['comments'] as $c): ?>
                        <div class="p-2 border-bottom small">
                            <strong><?= \App\Helpers\e($c['user_name']) ?>:</strong> <?= \App\Helpers\e($c['comment']) ?>
                            <div class="text-muted" style="font-size: 10px;"><?= \App\Helpers\format_date($c['created_at'], true) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="/supervisor/tasks/review/<?= $assignment['id'] ?>/comment" method="POST">
                    <?= \App\Helpers\csrf_field() ?>
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control form-control-sm" placeholder="Escrever comentário para o estagiário..." required>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-success">
                    <i class="bi bi-award-fill me-2"></i> Parecer do Orientador
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/supervisor/tasks/review/<?= $assignment['id'] ?>/evaluate" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Decisão da Avaliação *</label>
                        <select name="status" class="form-select" required>
                            <option value="approved" selected>Aprovar Tarefa</option>
                            <option value="rejected">Reprovar Tarefa</option>
                            <option value="in_review">Solicitar Correções / Reabrir</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nota Atribuída (0 a 100) *</label>
                        <input type="number" step="0.5" name="score" class="form-control form-control-lg fw-bold" value="<?= $assignment['score'] ?? 95 ?>" min="0" max="100" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Feedback Detalhado & Orientações *</label>
                        <textarea name="supervisor_feedback" class="form-control" rows="5" placeholder="Elogios, pontos a melhorar e orientações técnicas para o aluno..." required><?= \App\Helpers\e($assignment['supervisor_feedback'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Gravar Parecer e Atualizar Nota
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
