<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-patch-question-fill me-2"></i> <?= \App\Helpers\e($test['title']) ?>
                    </h5>
                    <span class="small text-muted"><?= \App\Helpers\e($test['course_title']) ?> • <?= \App\Helpers\e($test['module_title']) ?></span>
                </div>
                <span class="badge bg-warning text-dark fs-6">Nota Mínima: <?= number_format((float)$test['passing_score'], 0) ?>%</span>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($attempts)): ?>
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <h6 class="fw-bold text-dark mb-2">Tentativas Anteriores:</h6>
                        <?php foreach ($attempts as $att): ?>
                            <div class="d-flex justify-content-between align-items-center small py-1 border-bottom">
                                <span>Tentativa #<?= $att['attempt_number'] ?> (<?= \App\Helpers\format_date($att['started_at'], true) ?>)</span>
                                <div>
                                    <strong class="me-2"><?= number_format((float)$att['percentage'], 1) ?>%</strong>
                                    <?php if ($att['status'] === 'passed'): ?>
                                        <span class="badge bg-success">Aprovado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Reprovado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($canAttempt): ?>
                    <form action="/intern/tests/<?= $test['id'] ?>/submit" method="POST">
                        <?= \App\Helpers\csrf_field() ?>

                        <?php foreach ($test['questions'] as $idx => $q): ?>
                            <div class="p-3 border rounded-3 bg-white mb-4 shadow-xs">
                                <div class="fw-bold text-dark mb-3">
                                    <span class="badge bg-primary me-2">Questão <?= $idx + 1 ?></span>
                                    <?= \App\Helpers\e($q['statement']) ?>
                                    <span class="text-muted small fw-normal float-end">(<?= number_format((float)$q['score_points'], 0) ?> pts)</span>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <div class="form-check p-2 border rounded-2 bg-light">
                                            <input class="form-check-input ms-1" type="radio" name="answers[<?= $q['id'] ?>]" id="opt<?= $opt['id'] ?>" value="<?= $opt['id'] ?>" required>
                                            <label class="form-check-label ms-2 w-100 cursor-pointer" for="opt<?= $opt['id'] ?>">
                                                <?= \App\Helpers\e($opt['option_text']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Submeter Teste para Avaliação
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center py-4">
                        <i class="bi bi-exclamation-octagon fs-3 d-block mb-2"></i>
                        <h5>Limite de Tentativas Atingido</h5>
                        <p class="small mb-0">Você atingiu o limite máximo de <?= $test['max_attempts'] ?> tentativas para este teste.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
