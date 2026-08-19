<div class="row g-3 mb-4">
    <!-- Stat 1 -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Estagiários Ativos</div>
                <div class="fs-3 fw-bold text-dark"><?= $stats['active_interns'] ?> <span class="text-muted fs-6 fw-normal">/ <?= $stats['total_interns'] ?></span></div>
            </div>
            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Instituições Parceiras</div>
                <div class="fs-3 fw-bold text-dark"><?= $stats['total_institutions'] ?></div>
            </div>
            <div class="icon-box bg-success bg-opacity-10 text-success">
                <i class="bi bi-building"></i>
            </div>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Média Global de Desempenho</div>
                <div class="fs-3 fw-bold text-dark"><?= $stats['avg_score'] ?> <span class="text-muted fs-6 fw-normal">/ 100</span></div>
            </div>
            <div class="icon-box bg-info bg-opacity-10 text-info">
                <i class="bi bi-award-fill"></i>
            </div>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Tarefas em Andamento</div>
                <div class="fs-3 fw-bold text-dark"><?= $stats['pending_tasks'] ?></div>
            </div>
            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-list-task"></i>
            </div>
        </div>
    </div>
</div>

<!-- Indicator Matrix -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt-fill text-danger me-2"></i> Presenças de Hoje na Asoftmedia</span>
                <span class="badge bg-light text-dark border"><?= date('d/m/Y') ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estagiário</th>
                                <th>Instituição</th>
                                <th>Entrada</th>
                                <th>Distância GPS</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($todayAttendance)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhuma marcação de ponto registrada hoje até o momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($todayAttendance as $att): ?>
                                    <tr>
                                        <td>
                                            <strong><?= \App\Helpers\e($att['full_name']) ?></strong><br>
                                            <span class="small text-muted"><?= \App\Helpers\e($att['internship_code']) ?></span>
                                        </td>
                                        <td class="small"><?= \App\Helpers\e($att['institution_name']) ?></td>
                                        <td><?= substr($att['check_in_time'], 0, 5) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= round((float)$att['check_in_distance_meters']) ?>m da empresa
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($att['check_in_status'] === 'on_time'): ?>
                                                <span class="badge bg-success">Pontual</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Atrasado</span>
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

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i> Distribuição de Risco dos Alunos
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-success bg-opacity-10 border-success">
                        <div>
                            <strong class="text-success">🟢 Normal</strong>
                            <div class="small text-muted">Presença & Notas excelentes</div>
                        </div>
                        <span class="fs-4 fw-bold text-success"><?= $stats['risk_normal'] ?></span>
                    </div>

                    <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-warning bg-opacity-10 border-warning">
                        <div>
                            <strong class="text-warning text-dark">🟡 Em Atenção</strong>
                            <div class="small text-muted">Presença 70-80% ou tarefas atrasadas</div>
                        </div>
                        <span class="fs-4 fw-bold text-warning text-dark"><?= $stats['risk_attention'] ?></span>
                    </div>

                    <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-danger bg-opacity-10 border-danger">
                        <div>
                            <strong class="text-danger">🔴 Em Risco</strong>
                            <div class="small text-muted">Faltas excessivas ou baixo desempenho</div>
                        </div>
                        <span class="fs-4 fw-bold text-danger"><?= $stats['risk_high'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interns Overview Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i> Todos os Estagiários Registados</span>
        <a href="/admin/interns/create" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Novo Estagiário
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cód / Nome</th>
                        <th>Instituição / Curso</th>
                        <th>Supervisor</th>
                        <th>Presenças</th>
                        <th>Nota Geral</th>
                        <th>Risco</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interns as $i): ?>
                        <tr>
                            <td>
                                <strong><?= \App\Helpers\e($i['full_name']) ?></strong><br>
                                <span class="badge bg-secondary"><?= \App\Helpers\e($i['internship_code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= \App\Helpers\e($i['institution_name']) ?></div>
                                <div class="text-muted small"><?= \App\Helpers\e($i['course']) ?></div>
                            </td>
                            <td class="small"><?= \App\Helpers\e($i['supervisor_name'] ?? 'Não atribuído') ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $i['days_present'] ?> dias</span>
                            </td>
                            <td>
                                <span class="fw-bold fs-6 <?= (float)$i['overall_score'] >= 80 ? 'text-success' : ((float)$i['overall_score'] >= 60 ? 'text-primary' : 'text-danger') ?>">
                                    <?= number_format((float)$i['overall_score'], 1) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($i['risk_level'] === 'normal'): ?>
                                    <span class="badge badge-risk-normal">Normal</span>
                                <?php elseif ($i['risk_level'] === 'attention'): ?>
                                    <span class="badge badge-risk-attention">Atenção</span>
                                <?php else: ?>
                                    <span class="badge badge-risk-risk">Risco</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/interns/<?= $i['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye-fill"></i> Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
