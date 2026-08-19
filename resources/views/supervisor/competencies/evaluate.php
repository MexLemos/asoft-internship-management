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

                    <div class="row g-4 mb-4">
                        <?php foreach ($competencies as $comp): ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong class="text-dark"><?= \App\Helpers\e($comp['name']) ?></strong>
                                            <span class="badge bg-secondary ms-1 small"><?= \App\Helpers\e($comp['category_name']) ?></span>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-3"><?= \App\Helpers\e($comp['description']) ?></p>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Nível Demonstrado (1-5):</label>
                                        <select name="levels[<?= $comp['id'] ?>]" class="form-select form-select-sm">
                                            <option value="1" <?= (int)$comp['current_level'] === 1 ? 'selected' : '' ?>>1 — Iniciante (Aprendeu conceitos iniciais)</option>
                                            <option value="2" <?= (int)$comp['current_level'] === 2 ? 'selected' : '' ?>>2 — Básico (Executa com auxílio constante)</option>
                                            <option value="3" <?= (int)$comp['current_level'] === 3 ? 'selected' : '' ?>>3 — Intermediário (Executa tarefas comuns de forma autônoma)</option>
                                            <option value="4" <?= (int)$comp['current_level'] === 4 ? 'selected' : '' ?>>4 — Avançado (Domina boas práticas e resolve problemas complexos)</option>
                                            <option value="5" <?= (int)$comp['current_level'] === 5 ? 'selected' : '' ?>>5 — Excelente (Excepcional, padrão sênior/referência)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-semibold">Evidências / Observações:</label>
                                        <input type="text" name="notes[<?= $comp['id'] ?>]" class="form-control form-control-sm" value="<?= \App\Helpers\e($comp['evidence_notes'] ?? '') ?>" placeholder="ex: Demonstrou nas tarefas de CRUD e Git">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="/supervisor/competencies" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Avaliação de Competências
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
