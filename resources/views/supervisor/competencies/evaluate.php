<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-award-fill me-2"></i> Matriz de Competências: <?= \App\Helpers\e($intern['full_name']) ?>
                    </h5>
                    <span class="small text-muted"><?= \App\Helpers\e($intern['internship_code']) ?> • <?= \App\Helpers\e($intern['course']) ?></span>
                </div>
                <a href="/supervisor/competencies" class="btn btn-outline-secondary btn-sm">Voltar</a>
            </div>
            <div class="card-body p-4">
                <form action="/supervisor/competencies/evaluate/<?= $intern['id'] ?>/save" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="alert alert-light border small mb-4">
                        <strong>Escala de Avaliação (1 a 5):</strong><br>
                        <code>1</code>: Iniciante • <code>2</code>: Básico • <code>3</code>: Intermediário • <code>4</code>: Avançado • <code>5</code>: Excelente
                    </div>

                    <?php if (empty($competencies)): ?>
                        <div class="alert alert-warning text-center py-4 my-4 rounded-3">
                            <i class="bi bi-exclamation-triangle fs-2 d-block mb-2 text-warning"></i>
                            <h6 class="fw-bold">Nenhuma competência encontrada para este estagiário.</h6>
                            <p class="small text-muted mb-3">Clique no botão abaixo para recarregar a matriz oficial de competências.</p>
                            <a href="/supervisor/competencies/evaluate/<?= $intern['id'] ?>" class="btn btn-primary btn-sm px-3">
                                <i class="bi bi-arrow-clockwise me-1"></i> Carregar Matriz de Competências
                            </a>
                        </div>
                    <?php else: ?>
                        <?php
                            $grouped = [];
                            foreach ($competencies as $c) {
                                $cat = $c['category_name'] ?? 'Gerais';
                                $grouped[$cat][] = $c;
                            }
                        ?>

                        <?php foreach ($grouped as $categoryName => $comps): ?>
                            <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                                <span class="badge bg-primary px-2 py-1"><i class="bi bi-bookmark-check me-1"></i> <?= \App\Helpers\e($categoryName) ?></span>
                                <hr class="flex-grow-1 my-0 text-muted">
                            </div>

                            <div class="row g-3 mb-3">
                                <?php foreach ($comps as $comp): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light h-100 shadow-xs">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong class="text-dark fs-6"><?= \App\Helpers\e($comp['name']) ?></strong>
                                            </div>
                                            <p class="small text-muted mb-3"><?= \App\Helpers\e($comp['description'] ?? '') ?></p>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Nível Demonstrado (1-5): *</label>
                                                <select name="levels[<?= $comp['id'] ?>]" class="form-select form-select-sm" required>
                                                    <option value="1" <?= (int)$comp['current_level'] === 1 ? 'selected' : '' ?>>1 — Iniciante (Aprendeu conceitos iniciais)</option>
                                                    <option value="2" <?= (int)$comp['current_level'] === 2 ? 'selected' : '' ?>>2 — Básico (Executa com auxílio constante)</option>
                                                    <option value="3" <?= (int)$comp['current_level'] === 3 ? 'selected' : '' ?>>3 — Intermediário (Executa tarefas de forma autónoma)</option>
                                                    <option value="4" <?= (int)$comp['current_level'] === 4 ? 'selected' : '' ?>>4 — Avançado (Domina boas práticas e resolve problemas)</option>
                                                    <option value="5" <?= (int)$comp['current_level'] === 5 ? 'selected' : '' ?>>5 — Excelente (Excepcional, padrão sênior/referência)</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label small fw-semibold text-secondary">Evidências / Observações:</label>
                                                <input type="text" name="notes[<?= $comp['id'] ?>]" class="form-control form-control-sm" value="<?= \App\Helpers\e($comp['evidence_notes'] ?? '') ?>" placeholder="ex: Demonstrou nas tarefas práticas e commits">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="/supervisor/competencies" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" <?= empty($competencies) ? 'disabled' : '' ?>>
                            <i class="bi bi-save me-1"></i> Gravar Avaliação de Competências
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
