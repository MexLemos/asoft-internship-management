<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/supervisor/mentorship" class="text-decoration-none">Mentorias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalhes da Sessão</li>
        </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold mb-1"><?= \App\Helpers\e($log['title']) ?></h4>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                <span><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y \à\s H:i', strtotime($log['session_date'])) ?></span>
                <span>•</span>
                <span><i class="bi bi-person-fill me-1"></i>Orientador: <?= \App\Helpers\e($log['supervisor_name']) ?></span>
                <span class="badge bg-light text-dark border ms-1">
                    <?= \App\Helpers\e($types[$log['session_type']] ?? $log['session_type']) ?>
                </span>
                <?php if (!empty($log['is_private'])): ?>
                    <span class="badge bg-secondary ms-1">
                        <i class="bi bi-lock-fill me-1"></i>Confidencial
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/supervisor/mentorship/<?= $log['id'] ?>/edit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <a href="/supervisor/interns/<?= $log['intern_id'] ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-lines-fill me-1"></i> Ver Estagiário
            </a>
            <a href="/supervisor/mentorship" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Log Content -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <!-- Rating Highlight -->
                <?php if ($log['rating']): ?>
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between mb-4 border">
                        <div>
                            <span class="text-muted small d-block">Avaliação atribuída nesta sessão:</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="fs-4 text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= (int)$log['rating'] ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="fw-bold fs-5 text-dark"><?= $log['rating'] ?> / 5</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                Computada no Desempenho
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase small tracking-wide text-muted mb-2">Resumo Geral da Reunião</h6>
                    <div class="p-3 bg-white border rounded-3 text-secondary lh-base" style="white-space: pre-line;">
                        <?= \App\Helpers\e($log['summary']) ?>
                    </div>
                </div>

                <?php if (!empty($log['topics_discussed'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark text-uppercase small tracking-wide text-muted mb-2">Tópicos Abordados & Discussão Técnica</h6>
                        <div class="p-3 bg-white border rounded-3 text-secondary lh-base" style="white-space: pre-line;">
                            <?= \App\Helpers\e($log['topics_discussed']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($log['action_items'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark text-uppercase small tracking-wide text-muted mb-2">Metas Acordadas & Próximos Passos (Action Items)</h6>
                        <div class="p-3 bg-light border rounded-3 text-dark lh-base" style="white-space: pre-line;">
                            <?= \App\Helpers\e($log['action_items']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Intern Overview Card -->
    <div class="col-lg-4">
        <?php if ($intern): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark">Estagiário Orientado</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 52px; height: 52px;">
                            <?= strtoupper(substr($intern['full_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= \App\Helpers\e($intern['full_name']) ?></h6>
                            <code class="small text-muted"><?= \App\Helpers\e($intern['internship_code']) ?></code>
                        </div>
                    </div>

                    <div class="small text-muted mb-2">
                        <i class="bi bi-mortarboard me-1"></i> <?= \App\Helpers\e($intern['course']) ?>
                    </div>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-building me-1"></i> <?= \App\Helpers\e($intern['institution_name'] ?? 'N/D') ?>
                    </div>

                    <hr>

                    <?php if ($scoreData): ?>
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Nota Cumulativa Atual:</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="fs-4 fw-bold text-primary"><?= number_format((float)$scoreData['score_20'], 1) ?></span>
                                <span class="text-muted small">/ 20 valores</span>
                                <span class="badge bg-<?= $scoreData['mention'] === 'Insuficiente' ? 'danger' : 'success' ?> ms-auto">
                                    <?= \App\Helpers\e($scoreData['mention']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $scoreData['overall_score']) ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Percentual Global:</span>
                            <span class="fw-bold"><?= number_format((float)$scoreData['overall_score'], 1) ?>%</span>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="/supervisor/interns/<?= $intern['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-person-badge me-1"></i> Ver Perfil Completo
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Danger Zone: Delete -->
        <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-3">
                <span class="small text-muted d-block mb-2">Ações de Gestão:</span>
                <form action="/supervisor/mentorship/<?= $log['id'] ?>/delete" method="POST" onsubmit="return confirm('Tem certeza que deseja eliminar este registo de mentoria? A nota do estagiário será recalculada.');">
                    <?= \App\Helpers\csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i> Eliminar Registo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
