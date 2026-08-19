<div class="row g-4 mb-4">
    <!-- Intern Profile Header Card -->
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1"><?= \App\Helpers\e($intern['full_name']) ?></h4>
                <div class="text-muted small mb-2"><?= \App\Helpers\e($intern['course']) ?></div>
                <div class="badge bg-secondary mb-3"><?= \App\Helpers\e($intern['internship_code']) ?></div>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <?php if ($intern['risk_level'] === 'normal'): ?>
                        <span class="badge badge-risk-normal px-3 py-2 fs-6">🟢 Risco Normal</span>
                    <?php elseif ($intern['risk_level'] === 'attention'): ?>
                        <span class="badge badge-risk-attention px-3 py-2 fs-6">🟡 Em Atenção</span>
                    <?php else: ?>
                        <span class="badge badge-risk-risk px-3 py-2 fs-6">🔴 Em Risco</span>
                    <?php endif; ?>
                </div>

                <ul class="list-group list-group-flush text-start small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">BI:</span>
                        <strong><?= \App\Helpers\e($intern['bi_number']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Instituição:</span>
                        <strong class="text-end"><?= \App\Helpers\e($intern['institution_name']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Supervisor:</span>
                        <strong><?= \App\Helpers\e($intern['supervisor_name'] ?? 'Não atribuído') ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Período:</span>
                        <strong><?= \App\Helpers\format_date($intern['start_date']) ?> a <?= \App\Helpers\format_date($intern['end_date']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Horário Previsto:</span>
                        <strong><?= substr($intern['expected_start_time'] ?? '08:00', 0, 5) ?> - <?= substr($intern['expected_end_time'] ?? '12:00', 0, 5) ?></strong>
                    </li>
                </ul>

                <!-- Certificate Generation Action -->
                <div class="mt-4 pt-3 border-top">
                    <?php if ($eligibility['eligible']): ?>
                        <form action="/admin/interns/<?= $intern['id'] ?>/generate-certificate" method="POST">
                            <?= \App\Helpers\csrf_field() ?>
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="bi bi-patch-check-fill me-1"></i> Emitir Declaração / Certificado Oficial
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 py-2" disabled>
                            <i class="bi bi-lock-fill me-1"></i> Certificado Indisponível (Requisitos Pendentes)
                        </button>
                        <div class="small text-danger text-start mt-2">
                            <?php foreach ($eligibility['reasons'] as $r): ?>
                                <div>• <?= \App\Helpers\e($r) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Scoring Breakdown -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-graph-up me-2 text-primary"></i> Motor de Desempenho Ponderado (Nota Global: <?= number_format((float)$intern['overall_score'], 1) ?> / 100)
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($scoreData['components'] as $name => $comp): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small fw-semibold text-capitalize"><?= $name ?> (Peso <?= $comp['weight'] ?>%)</div>
                                <div class="fs-4 fw-bold text-dark mt-1"><?= $comp['score'] ?><span class="fs-6 text-muted fw-normal">/100</span></div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $comp['score']) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs for Detailed Logs -->
        <ul class="nav nav-tabs" id="internTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button">
                    <i class="bi bi-geo-alt me-1"></i> Histórico de Presenças (<?= count($attendance) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks-pane" type="button">
                    <i class="bi bi-list-task me-1"></i> Tarefas Práticas (<?= count($tasks) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="competencies-tab" data-bs-toggle="tab" data-bs-target="#competencies-pane" type="button">
                    <i class="bi bi-award me-1"></i> Matriz de Competências (<?= count($competencies) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 bg-white rounded-bottom p-4" id="internTabContent">
            <!-- Attendance Tab -->
            <div class="tab-pane fade show active" id="attendance-pane">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Horas</th>
                                <th>Distância GPS</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance as $att): ?>
                                <tr>
                                    <td><strong><?= \App\Helpers\format_date($att['date']) ?></strong></td>
                                    <td><?= substr($att['check_in_time'] ?? '--:--', 0, 5) ?></td>
                                    <td><?= substr($att['check_out_time'] ?? '--:--', 0, 5) ?></td>
                                    <td><?= $att['hours_worked'] ?>h</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= round((float)$att['check_in_distance_meters']) ?>m da Asoftmedia
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= \App\Helpers\e($att['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tasks Tab -->
            <div class="tab-pane fade" id="tasks-pane">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tarefa</th>
                                <th>Categoria</th>
                                <th>Prazo</th>
                                <th>Estado</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><strong><?= \App\Helpers\e($t['title']) ?></strong></td>
                                    <td><span class="badge bg-<?= $t['color_badge'] ?>"><?= \App\Helpers\e($t['category_name']) ?></span></td>
                                    <td class="small"><?= \App\Helpers\format_date($t['due_date']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= \App\Helpers\e($t['status']) ?></span></td>
                                    <td><?= $t['score'] ? number_format((float)$t['score'], 1) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Competencies Tab -->
            <div class="tab-pane fade" id="competencies-pane">
                <div class="row g-3">
                    <?php foreach ($competencies as $c): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><?= \App\Helpers\e($c['name']) ?></strong>
                                    <span class="badge bg-primary">Nível <?= $c['current_level'] ?> / 5</span>
                                </div>
                                <div class="text-muted small"><?= \App\Helpers\e($c['description']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
