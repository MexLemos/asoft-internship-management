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
    <div class="text-end">
        <?php
            $statusColors = ['active' => 'success', 'completed' => 'primary', 'abandoned' => 'danger', 'pending' => 'warning'];
            $statusLabels = ['active' => 'Em Curso', 'completed' => 'Concluído', 'abandoned' => 'Abandonado', 'pending' => 'Pendente'];
            $color = $statusColors[$intern['status']] ?? 'secondary';
            $label = $statusLabels[$intern['status']] ?? ucfirst($intern['status']);
        ?>
        <span class="badge bg-<?= $color ?> fs-6 px-3 py-2"><?= $label ?></span>
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
            $risk = $intern['risk_level'] ?? 'low';
            $riskColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
            $riskLabels = ['low' => 'Baixo Risco', 'medium' => 'Risco Médio', 'high' => 'Alto Risco'];
        ?>
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-<?= $riskColors[$risk] ?? 'secondary' ?>">
                <i class="bi bi-shield-<?= $risk === 'high' ? 'exclamation' : ($risk === 'medium' ? 'half' : 'check') ?>"></i>
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
                        $barColor = $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                    ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= $areaLabels[$area] ?? ucfirst($area) ?></span>
                            <span class="fw-semibold text-<?= $barColor ?>"><?= number_format($pct, 1) ?>%</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= min(100, $pct) ?>%"></div>
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
