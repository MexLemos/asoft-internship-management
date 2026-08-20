<!-- Row 1: Primary Metrics Cards -->
<div class="row g-3 mb-4">
    <!-- Total & Active Interns -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Estagiários Ativos</div>
                <div class="fs-4 fw-bold text-dark"><?= $stats['active_interns'] ?> <span class="fs-6 text-muted fw-normal">/ <?= $stats['total_interns'] ?> total</span></div>
                <div class="small text-success mt-1">
                    <i class="bi bi-person-check-fill me-1"></i> Em andamento
                </div>
            </div>
            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <!-- Completed & Nearing Completion -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Concluídos & Término</div>
                <div class="fs-4 fw-bold text-success"><?= $stats['completed_interns'] ?> <span class="fs-6 text-warning text-dark fw-normal">(<?= $stats['nearing_completion'] ?> a findar)</span></div>
                <div class="small text-muted mt-1">
                    Próximos da conclusão
                </div>
            </div>
            <div class="icon-box bg-success bg-opacity-10 text-success">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
        </div>
    </div>

    <!-- Attendance & Overdue Tasks -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Presença Média</div>
                <div class="fs-4 fw-bold text-dark"><?= $stats['avg_attendance_pct'] ?>%</div>
                <div class="small text-muted mt-1">
                    <?= $stats['pending_tasks'] ?> tarefas pendentes (<?= $stats['overdue_tasks'] ?> em atraso)
                </div>
            </div>
            <div class="icon-box bg-info bg-opacity-10 text-info">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
        </div>
    </div>

    <!-- Overall Performance & Institutions -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Desempenho Geral</div>
                <div class="fs-4 fw-bold text-success"><?= number_format((float)$stats['avg_score'], 1) ?><span class="fs-6 text-muted fw-normal">/100</span></div>
                <div class="small text-muted mt-1">
                    <?= $stats['total_institutions'] ?> Instituições Parceiras
                </div>
            </div>
            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Charts & Risk Monitoring -->
<div class="row g-4 mb-4">
    <!-- Risk & Status Chart -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-pie-chart-fill me-2 text-primary"></i> Situação de Risco dos Estagiários
                </span>
                <span class="badge bg-secondary"><?= $stats['active_interns'] ?> ativos</span>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                <div style="width: 220px; height: 220px;">
                    <canvas id="riskChart"></canvas>
                </div>
                <div class="row w-100 text-center g-2 mt-3 pt-3 border-top">
                    <div class="col-4">
                        <span class="badge badge-risk-normal px-2 py-1 d-block">Normal</span>
                        <strong class="fs-5 text-success"><?= $stats['risk_normal'] ?></strong>
                    </div>
                    <div class="col-4">
                        <span class="badge badge-risk-attention px-2 py-1 d-block">Atenção</span>
                        <strong class="fs-5 text-warning text-dark"><?= $stats['risk_attention'] ?></strong>
                    </div>
                    <div class="col-4">
                        <span class="badge badge-risk-risk px-2 py-1 d-block">Risco</span>
                        <strong class="fs-5 text-danger"><?= $stats['risk_high'] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Live Attendance Feed -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-geo-alt-fill me-2 text-danger"></i> Registo de Presenças GPS de Hoje
                </span>
                <span class="badge bg-light text-dark border"><?= date('d/m/Y') ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Estagiário</th>
                                <th>Instituição</th>
                                <th>Entrada</th>
                                <th>Distância</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($todayAttendance)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhum registo de presença efetuado hoje até o momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($todayAttendance as $att): ?>
                                    <tr>
                                        <td>
                                            <strong><?= \App\Helpers\e($att['full_name']) ?></strong><br>
                                            <span class="text-muted"><?= \App\Helpers\e($att['internship_code']) ?></span>
                                        </td>
                                        <td><?= \App\Helpers\e($att['institution_name']) ?></td>
                                        <td><?= substr($att['check_in_time'] ?? '--:--', 0, 5) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= round((float)$att['check_in_distance_meters']) ?>m da sede
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($att['status'] === 'present'): ?>
                                                <span class="badge bg-success">No Horário</span>
                                            <?php elseif ($att['status'] === 'late'): ?>
                                                <span class="badge bg-warning text-dark">Atrasado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Falta</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Pending Tasks Queue -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">
            <i class="bi bi-inbox-fill me-2 text-primary"></i> Fila de Tarefas em Execução e Avaliação
        </span>
        <a href="/admin/tasks" class="btn btn-outline-primary btn-sm">Ver Todas as Tarefas</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Tarefa</th>
                        <th>Estagiário</th>
                        <th>Prazo de Entrega</th>
                        <th>Pontos</th>
                        <th>Estado</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPendingTasks)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Nenhuma tarefa pendente de revisão no momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPendingTasks as $rpt): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($rpt['task_title']) ?></strong>
                                </td>
                                <td><?= \App\Helpers\e($rpt['intern_name']) ?> (<code><?= \App\Helpers\e($rpt['internship_code']) ?></code>)</td>
                                <td class="<?= strtotime($rpt['due_date']) < time() ? 'text-danger fw-bold' : '' ?>">
                                    <?= \App\Helpers\format_date($rpt['due_date']) ?>
                                    <?php if (strtotime($rpt['due_date']) < time()): ?>
                                        <span class="badge bg-danger ms-1">Atrasada</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $rpt['points'] ?? 100 ?> pts</td>
                                <td>
                                    <?php if ($rpt['status'] === 'submitted'): ?>
                                        <span class="badge bg-info text-dark">Submetida / Aguardando</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Em Andamento</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/tasks" class="btn btn-light btn-sm border">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('riskChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Normal', 'Atenção', 'Risco'],
                datasets: [{
                    data: [<?= $stats['risk_normal'] ?>, <?= $stats['risk_attention'] ?>, <?= $stats['risk_high'] ?>],
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
