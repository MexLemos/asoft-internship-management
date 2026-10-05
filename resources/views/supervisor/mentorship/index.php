<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Mentorias & Acompanhamento 1-on-1</h4>
        <p class="text-muted small mb-0">
            Orientação contínua, reuniões de alinhamento e avaliações para todos os estagiários da empresa.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="/supervisor/mentorship/create<?= $selectedInternId ? '?intern_id=' . $selectedInternId : '' ?>" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Nova Sessão 1-on-1
        </a>
    </div>
</div>

<!-- Info banner explaining universal access & cumulative grading -->
<div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4 rounded-3">
    <i class="bi bi-info-circle-fill fs-4 text-info mt-1"></i>
    <div class="small">
        <strong class="d-block mb-1">Acompanhamento Universal & Média Cumulativa de Avaliação</strong>
        Todos os supervisores têm acesso à mentoria de todos os estagiários. As notas atribuídas em cada sessão (escala de 1 a 5) somam-se automaticamente para o cálculo da nota final do estagiário no componente de Comportamento & Acompanhamento, convertida para a escala oficial angolana (0 a 20 valores).
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-chat-heart-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total de Sessões</span>
                    <h5 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_sessions']) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Estagiários Atendidos</span>
                    <h5 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_interns_mentored']) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-star-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Média de Avaliação</span>
                    <h5 class="fw-bold mb-0 text-dark">
                        <?= $stats['avg_rating'] !== null ? number_format($stats['avg_rating'], 1) . ' / 5.0' : 'Sem notas' ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-calendar-check-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Últimos 30 Dias</span>
                    <h5 class="fw-bold mb-0 text-dark"><?= number_format($stats['sessions_last_30_days']) ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-body p-3">
        <form action="/supervisor/mentorship" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label for="intern_id" class="form-label small fw-semibold mb-1">Filtrar por Estagiário:</label>
                <select name="intern_id" id="intern_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos os Estagiários (<?= count($interns) ?> estagiários)</option>
                    <?php foreach ($interns as $intern): ?>
                        <option value="<?= $intern['id'] ?>" <?= $selectedInternId === (int)$intern['id'] ? 'selected' : '' ?>>
                            <?= \App\Helpers\e($intern['full_name']) ?> (<?= \App\Helpers\e($intern['internship_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selectedInternId): ?>
                <div class="col-md-3 pt-md-3">
                    <a href="/supervisor/mentorship" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Limpar Filtro
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Sessions List -->
<?php if (empty($logs)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-5 text-center text-muted">
            <i class="bi bi-chat-square-text display-4 text-primary opacity-50 mb-3 d-block"></i>
            <h5>Nenhum registo de mentoria encontrado.</h5>
            <p class="small text-muted mb-3">
                <?= $selectedInternId ? 'Este estagiário ainda não possui sessões de mentoria gravadas.' : 'Ainda não foram registadas sessões de mentoria 1-on-1.' ?>
            </p>
            <a href="/supervisor/mentorship/create<?= $selectedInternId ? '?intern_id=' . $selectedInternId : '' ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Registar Primeira Sessão
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th style="min-width: 140px;">Data & Hora</th>
                        <th style="min-width: 180px;">Estagiário</th>
                        <th style="min-width: 160px;">Supervisor</th>
                        <th style="min-width: 160px;">Tipo</th>
                        <th>Assunto / Título</th>
                        <th class="text-center" style="min-width: 110px;">Avaliação</th>
                        <th class="text-end" style="min-width: 130px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold small text-dark">
                                    <?= date('d/m/Y', strtotime($log['session_date'])) ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?= date('H:i', strtotime($log['session_date'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($log['intern_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <a href="/supervisor/interns/<?= $log['intern_id'] ?>" class="text-decoration-none fw-semibold text-dark small d-block">
                                            <?= \App\Helpers\e($log['intern_name']) ?>
                                        </a>
                                        <code class="text-muted" style="font-size: 0.75rem;"><?= \App\Helpers\e($log['internship_code'] ?? '') ?></code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="small text-secondary fw-semibold">
                                    <i class="bi bi-person me-1"></i><?= \App\Helpers\e($log['supervisor_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= \App\Helpers\e($types[$log['session_type']] ?? $log['session_type']) ?>
                                </span>
                                <?php if (!empty($log['is_private'])): ?>
                                    <span class="badge bg-secondary ms-1" title="Visível apenas para supervisores">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small"><?= \App\Helpers\e($log['title']) ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 280px;">
                                    <?= \App\Helpers\e($log['summary']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($log['rating']): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-star-fill text-warning-emphasis me-1"></i><?= $log['rating'] ?> / 5
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Sem nota</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/supervisor/mentorship/<?= $log['id'] ?>" class="btn btn-outline-primary btn-sm py-1 px-2" title="Ver Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/supervisor/mentorship/<?= $log['id'] ?>/edit" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
