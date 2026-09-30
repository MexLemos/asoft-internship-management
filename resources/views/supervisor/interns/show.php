<!-- Back + Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <a href="/supervisor/interns" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Voltar aos Estagiários
        </a>
        <h4 class="fw-bold mb-1"><?= \App\Helpers\e($intern['full_name']) ?></h4>
        <p class="text-muted small mb-0">
            <code><?= \App\Helpers\e($intern['internship_code']) ?></code> &bull;
            <?= \App\Helpers\e($intern['course']) ?> &bull;
            <?= \App\Helpers\e($intern['institution_name'] ?? 'N/D') ?>
        </p>
    </div>
    <div class="text-end d-flex flex-wrap justify-content-end align-items-center gap-2">
        <span class="badge <?= \App\Models\Intern::getStatusBadge($intern['status']) ?> fs-6 px-3 py-2">
            <?= \App\Models\Intern::getStatusLabel($intern['status']) ?>
        </span>
        <button type="button" class="btn btn-info btn-sm text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNewMentorshipLog">
            <i class="bi bi-chat-heart me-1"></i> Registar Mentoria / 1-on-1
        </button>
        <?php if (!empty($availableTransitions)): ?>
            <button type="button" class="btn btn-outline-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalChangeStatus">
                <i class="bi bi-arrow-repeat me-1"></i> Transitar Estado
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Score & Risk Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary"><?= number_format($intern['overall_score'] ?? 0, 1) ?>%</div>
            <div class="small text-muted">Pontuação Global</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <?php
            $risk = $intern['risk_level'] ?? 'normal';
            $riskColors = ['normal' => 'success', 'low' => 'success', 'attention' => 'warning', 'medium' => 'warning', 'risk' => 'danger', 'high' => 'danger'];
            $riskLabels = ['normal' => 'Regular', 'low' => 'Baixo Risco', 'attention' => 'Atenção Necessária', 'medium' => 'Risco Médio', 'risk' => 'Alto Risco', 'high' => 'Alto Risco'];
        ?>
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-<?= $riskColors[$risk] ?? 'secondary' ?>">
                <i class="bi bi-shield-<?= in_array($risk, ['risk', 'high']) ? 'exclamation' : (in_array($risk, ['attention', 'medium']) ? 'half' : 'check') ?>"></i>
            </div>
            <div class="small text-muted"><?= $riskLabels[$risk] ?? ucfirst($risk) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-<?= $attendanceSummary['absent'] > 3 ? 'danger' : 'success' ?>"><?= $attendanceSummary['absent'] ?></div>
            <div class="small text-muted">Faltas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-<?= $attendanceSummary['late'] > 2 ? 'warning' : 'success' ?>"><?= $attendanceSummary['late'] ?></div>
            <div class="small text-muted">Atrasos</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- LEFT COLUMN: Attendance -->
    <div class="col-lg-6">

        <!-- Attendance Summary Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-calendar-check text-success me-2"></i> Resumo de Frequência
            </div>
            <div class="card-body">
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-success bg-opacity-10 rounded-3">
                            <div class="fw-bold fs-5 text-success"><?= $attendanceSummary['present'] ?></div>
                            <div class="small text-muted">Presentes</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-danger bg-opacity-10 rounded-3">
                            <div class="fw-bold fs-5 text-danger"><?= $attendanceSummary['absent'] ?></div>
                            <div class="small text-muted">Faltas</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-warning bg-opacity-10 rounded-3">
                            <div class="fw-bold fs-5 text-warning"><?= $attendanceSummary['late'] ?></div>
                            <div class="small text-muted">Atrasos</div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($attendanceSummary['late_records'])): ?>
                    <h6 class="fw-semibold small text-uppercase text-muted mb-2 mt-3">
                        <i class="bi bi-clock-history me-1 text-warning"></i> Últimos Atrasos
                    </h6>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($attendanceSummary['late_records'] as $rec): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-calendar3 text-muted me-1"></i>
                                    <?= \App\Helpers\format_date($rec['date']) ?>
                                </span>
                                <?php if (!empty($rec['check_in_time'])): ?>
                                    <span class="badge bg-warning text-dark">
                                        Entrada: <?= \App\Helpers\e(substr($rec['check_in_time'], 0, 5)) ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($attendanceSummary['absent_records'])): ?>
                    <h6 class="fw-semibold small text-uppercase text-muted mb-2 mt-3">
                        <i class="bi bi-x-circle me-1 text-danger"></i> Últimas Faltas
                    </h6>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($attendanceSummary['absent_records'] as $rec): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-calendar3 text-muted me-1"></i>
                                    <?= \App\Helpers\format_date($rec['date']) ?>
                                </span>
                                <?php if (!empty($rec['notes'])): ?>
                                    <span class="text-muted small"><?= \App\Helpers\e(\App\Helpers\truncate_text($rec['notes'], 40)) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (empty($attendanceSummary['late_records']) && empty($attendanceSummary['absent_records'])): ?>
                    <p class="text-muted small text-center mb-0 mt-2">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Sem atrasos ou faltas registadas.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Competencies -->
        <?php if (!empty($competencies)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-award text-info me-2"></i> Competências Avaliadas
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($competencies as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <span><?= \App\Helpers\e($c['competency_name'] ?? $c['name'] ?? 'Competência') ?></span>
                            <?php $score = (int)($c['score'] ?? $c['grade'] ?? 0); ?>
                            <span class="badge bg-<?= $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') ?>"><?= $score ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN: Tasks & Score Breakdown -->
    <div class="col-lg-6">

        <!-- Score Breakdown -->
        <?php if (!empty($scoreData['breakdown'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bar-chart text-primary me-2"></i> Desempenho por Área
            </div>
            <div class="card-body">
                <?php foreach ($scoreData['breakdown'] as $area => $data): ?>
                    <?php
                        $areaLabels = [
                            'attendance'   => 'Frequência',
                            'tasks'        => 'Tarefas',
                            'tests'        => 'Testes',
                            'competencies' => 'Competências',
                            'behavior'     => 'Comportamento',
                            'final_eval'   => 'Avaliação Final',
                        ];
                        $pct = (float)($data['weighted_score'] ?? $data['score'] ?? 0);
                        $barColor = $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : ($pct > 0 ? 'danger' : 'secondary'));
                    ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= $areaLabels[$area] ?? ucfirst($area) ?></span>
                            <span class="fw-semibold text-<?= $barColor ?>"><?= number_format($pct, 1) ?>%</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= min(100, max(0, $pct)) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tasks -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-list-task text-primary me-2"></i> Tarefas Atribuídas
            </div>
            <div class="card-body p-0">
                <?php if (empty($tasks)): ?>
                    <div class="p-4 text-center text-muted small">Nenhuma tarefa atribuída ainda.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush small">
                        <?php foreach (array_slice($tasks, 0, 10) as $t): ?>
                            <?php
                                $tColors = ['pending' => 'secondary', 'in_progress' => 'primary', 'submitted' => 'info', 'in_review' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                $tLabels = ['pending' => 'Pendente', 'in_progress' => 'Em Progresso', 'submitted' => 'Submetida', 'in_review' => 'Em Revisão', 'approved' => 'Aprovada', 'rejected' => 'Rejeitada'];
                                $tc = $tColors[$t['status']] ?? 'secondary';
                                $tl = $tLabels[$t['status']] ?? ucfirst($t['status']);
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                <span class="text-truncate" style="max-width:65%;"><?= \App\Helpers\e($t['title'] ?? $t['task_title'] ?? 'Tarefa') ?></span>
                                <span class="badge bg-<?= $tc ?>"><?= $tl ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($tasks) > 10): ?>
                        <div class="text-center py-2 text-muted small">
                            + <?= count($tasks) - 10 ?> outras tarefas
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ROW 2: Mentorship & Lifecycle History -->
<div class="row g-4 mt-1">
    <!-- Mentorship Logs Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-chat-heart text-info me-2"></i> Mentoria & Acompanhamento Contínuo (<?= count($mentorshipLogs) ?>)
                </span>
                <button type="button" class="btn btn-outline-info btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalNewMentorshipLog">
                    <i class="bi bi-plus-circle me-1"></i> Nova Sessão
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($mentorshipLogs)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-journal-x fs-1 text-secondary opacity-50 mb-2"></i>
                        <p class="mb-0">Nenhuma sessão de tutoria ou 1-on-1 registada para este estagiário.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mentorshipLogs as $m): ?>
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark me-2">
                                            <?= \App\Models\MentorshipLog::TYPES[$m['session_type']] ?? ucfirst($m['session_type']) ?>
                                        </span>
                                        <?php if (!empty($m['is_private'])): ?>
                                            <span class="badge bg-danger"><i class="bi bi-lock me-1"></i> Privado</span>
                                        <?php endif; ?>
                                        <strong class="text-dark"><?= \App\Helpers\e($m['title']) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($m['session_date'])) ?>
                                    </small>
                                </div>
                                <p class="small text-dark mb-2"><?= nl2br(\App\Helpers\e($m['summary'])) ?></p>
                                <?php if (!empty($m['topics_discussed'])): ?>
                                    <div class="small text-muted mb-1">
                                        <strong>Tópicos:</strong> <?= \App\Helpers\e($m['topics_discussed']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($m['action_items'])): ?>
                                    <div class="small text-primary mb-1">
                                        <strong>Plano de Ação:</strong> <?= \App\Helpers\e($m['action_items']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        Orientador: <strong><?= \App\Helpers\e($m['supervisor_name']) ?></strong>
                                    </small>
                                    <?php if (!empty($m['rating'])): ?>
                                        <small class="text-warning">
                                            <?= str_repeat('★', (int)$m['rating']) . str_repeat('☆', 5 - (int)$m['rating']) ?>
                                            (<?= $m['rating'] ?>/5)
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- State History Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-clock-history text-secondary me-2"></i> Histórico do Ciclo de Vida (<?= count($statusHistory) ?>)
            </div>
            <div class="card-body">
                <?php if (empty($statusHistory)): ?>
                    <div class="text-center py-4 text-muted small">
                        <p class="mb-0">Sem alterações de estado gravadas.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($statusHistory as $sh): ?>
                            <div class="p-3 border rounded-3 mb-2 bg-light small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <span class="badge <?= \App\Models\Intern::getStatusBadge($sh['from_status']) ?>">
                                            <?= \App\Models\Intern::getStatusLabel($sh['from_status']) ?>
                                        </span>
                                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                        <span class="badge <?= \App\Models\Intern::getStatusBadge($sh['to_status']) ?>">
                                            <?= \App\Models\Intern::getStatusLabel($sh['to_status']) ?>
                                        </span>
                                    </div>
                                    <span class="text-muted" style="font-size: 11px;">
                                        <?= date('d/m/Y H:i', strtotime($sh['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="text-dark mt-1">
                                    <strong>Justificação:</strong> <?= \App\Helpers\e($sh['reason']) ?>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    Por: <?= \App\Helpers\e($sh['changer_name']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Estado (Supervisor) -->
<?php if (!empty($availableTransitions)): ?>
<div class="modal fade" id="modalChangeStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/supervisor/interns/<?= $intern['id'] ?>/change-status" method="POST" class="modal-content">
            <?= \App\Helpers\csrf_field() ?>
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-arrow-repeat text-warning me-2"></i> Transitar Estado do Estágio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small text-muted">Estado Atual:</label>
                    <div>
                        <span class="badge <?= \App\Models\Intern::getStatusBadge($intern['status']) ?> fs-6">
                            <?= \App\Models\Intern::getStatusLabel($intern['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label small fw-bold">Novo Estado Autorizado <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Selecione o novo estado --</option>
                        <?php foreach ($availableTransitions as $targetStatus): ?>
                            <option value="<?= $targetStatus ?>">
                                <?= \App\Models\Intern::getStatusLabel($targetStatus) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label small fw-bold">Justificação / Parecer do Supervisor <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Indique a justificação formal..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark px-4">
                    <i class="bi bi-check2-circle me-1"></i> Confirmar Transição
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Nova Sessão de Mentoria (Supervisor) -->
<div class="modal fade" id="modalNewMentorshipLog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="/supervisor/interns/<?= $intern['id'] ?>/mentorship/store" method="POST" class="modal-content">
            <?= \App\Helpers\csrf_field() ?>
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-chat-heart text-info me-2"></i> Registar Sessão de Mentoria & Orientação
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Título da Sessão <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Ex: Acompanhamento de progresso e alinhamento" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tipo de Sessão <span class="text-danger">*</span></label>
                        <select name="session_type" class="form-select" required>
                            <?php foreach (\App\Models\MentorshipLog::TYPES as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Data & Hora <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="session_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Resumo da Sessão / Parecer do Orientador <span class="text-danger">*</span></label>
                        <textarea name="summary" rows="3" class="form-control" placeholder="Descreva os temas debatidos, avanços e eventuais dificuldades..." required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tópicos Discutidos</label>
                        <textarea name="topics_discussed" rows="2" class="form-control" placeholder="Tópicos abordados durante a reunião..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Plano de Ação / Metas Estabelecidas</label>
                        <textarea name="action_items" rows="2" class="form-control" placeholder="Ações que o estagiário deve executar..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Avaliação da Sessão (1 a 5 estrelas)</label>
                        <select name="rating" class="form-select">
                            <option value="">Sem nota quantitativa</option>
                            <option value="5">★★★★★ - Excelente (5)</option>
                            <option value="4">★★★★☆ - Muito Bom (4)</option>
                            <option value="3">★★★☆☆ - Regular / Satisfatório (3)</option>
                            <option value="2">★★☆☆☆ - Abaixo do Esperado (2)</option>
                            <option value="1">★☆☆☆☆ - Crítico / Insatisfatório (1)</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-center mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_private" value="1" id="is_private_check_sup">
                            <label class="form-check-label small" for="is_private_check_sup">
                                <strong>Registo Privado</strong> (Visível apenas aos supervisores e administração)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info fw-bold text-dark px-4">
                    <i class="bi bi-save me-1"></i> Gravar Registo de Mentoria
                </button>
            </div>
        </form>
    </div>
</div>
