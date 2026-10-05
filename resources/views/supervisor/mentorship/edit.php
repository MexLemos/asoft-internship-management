<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/supervisor/mentorship" class="text-decoration-none">Mentorias</a></li>
            <li class="breadcrumb-item"><a href="/supervisor/mentorship/<?= $log['id'] ?>" class="text-decoration-none">Sessão #<?= $log['id'] ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
    <h4 class="fw-bold mb-1">Editar Sessão de Mentoria</h4>
    <p class="text-muted small mb-0">Atualize anotações, metas acordadas ou a nota atribuída na sessão.</p>
</div>

<div class="row">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="/supervisor/mentorship/<?= $log['id'] ?>/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Estagiário</label>
                        <input type="text" class="form-control bg-light" value="<?= \App\Helpers\e($log['intern_name']) ?> (<?= \App\Helpers\e($log['internship_code'] ?? '') ?>)" readonly>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="session_date" class="form-label fw-semibold">
                                Data e Hora da Sessão <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" id="session_date" name="session_date" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($log['session_date'])) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="session_type" class="form-label fw-semibold">
                                Tipo de Acompanhamento <span class="text-danger">*</span>
                            </label>
                            <select name="session_type" id="session_type" class="form-select" required>
                                <?php foreach ($types as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $log['session_type'] === $key ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($lbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold">
                            Título / Assunto da Reunião <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= \App\Helpers\e($log['title']) ?>" required>
                    </div>

                    <!-- Rating Card / Selector -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-semibold d-block mb-1">
                            Avaliação de Desempenho desta Sessão (1 a 5)
                        </label>
                        <p class="text-muted small mb-2">
                            A nota dada soma-se automaticamente à média cumulativa do estagiário:
                        </p>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_none" value="" <?= empty($log['rating']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rating_none">Sem Nota</label>
                            </div>
                            <?php for ($r = 1; $r <= 5; $r++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_<?= $r ?>" value="<?= $r ?>" <?= (int)($log['rating'] ?? 0) === $r ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-semibold" for="rating_<?= $r ?>">★ <?= $r ?></label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="summary" class="form-label fw-semibold">
                            Resumo Geral da Sessão & Observações do Orientador <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="summary" name="summary" rows="4" required><?= \App\Helpers\e($log['summary']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="topics_discussed" class="form-label fw-semibold">
                            Tópicos Técnicos ou Comportamentais Abordados <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <textarea class="form-control" id="topics_discussed" name="topics_discussed" rows="3"><?= \App\Helpers\e($log['topics_discussed'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="action_items" class="form-label fw-semibold">
                            Metas Acordadas & Próximos Passos (Action Items) <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <textarea class="form-control" id="action_items" name="action_items" rows="3"><?= \App\Helpers\e($log['action_items'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="is_private" name="is_private" value="1" <?= !empty($log['is_private']) ? 'checked' : '' ?>>
                        <label class="form-check-label small text-muted" for="is_private">
                            <i class="bi bi-lock-fill me-1"></i> <strong>Sessão Confidencial:</strong> Visível apenas para supervisores e administração.
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="/supervisor/mentorship/<?= $log['id'] ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Atualizar Sessão
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
