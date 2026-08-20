<div class="row g-4 mb-4">
    <!-- Intern Profile Header Card -->
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1"><?= \App\Helpers\e($intern['full_name']) ?></h4>
                <div class="text-muted small mb-2"><?= \App\Helpers\e($intern['course']) ?> (<?= \App\Helpers\e($intern['formation_level'] ?? '13ª') ?>)</div>
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
                        <span class="text-muted">Área de Estágio:</span>
                        <strong><?= \App\Helpers\e($intern['internship_area'] ?? 'Geral') ?></strong>
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

                <!-- Certificate Verification & Emission Trigger -->
                <div class="mt-4 pt-3 border-top">
                    <button type="button" class="btn <?= $eligibility['eligible'] ? 'btn-success' : 'btn-outline-primary' ?> w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCertificateChecklist">
                        <i class="bi bi-patch-check-fill me-1"></i> Emitir Declaração / Certificado
                    </button>
                    <?php if (!$eligibility['eligible']): ?>
                        <div class="small text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i> Clique para verificar os requisitos pendentes.
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
                                    <td>
                                        <?php if ($t['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Aprovada</span>
                                        <?php elseif ($t['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger">Reprovada</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?= \App\Helpers\e($t['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
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

<!-- Modal Checklist Requisitos para Emissão de Declaração -->
<div class="modal fade" id="modalCertificateChecklist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-shield-check me-2"></i> Requisitos para Emissão de Declaração Oficial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-4">
                    A emissão da Declaração e Certificado de Estágio com QR Code exige a validação formal de todos os critérios pedagógicos e operacionais definidos pela Asoftmedia.
                </p>

                <div class="list-group mb-4">
                    <?php foreach ($eligibility['checklist'] as $key => $item): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($item['status']): ?>
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-check-lg fs-5"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-x-lg fs-5"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong class="text-dark small d-block"><?= \App\Helpers\e($item['label']) ?></strong>
                                    <span class="text-muted small"><?= \App\Helpers\e($item['details']) ?></span>
                                </div>
                            </div>
                            <span class="badge <?= $item['status'] ? 'bg-success' : 'bg-danger' ?> px-3 py-2">
                                <?= $item['status'] ? 'Cumprido' : 'Pendente' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($eligibility['eligible']): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-patch-check-fill fs-3 text-success"></i>
                        <div>
                            <strong>Todos os requisitos foram satisfeitos!</strong><br>
                            <span class="small">O estágio está concluído e pronto para emissão da declaração oficial com validação por QR Code.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-exclamation-octagon-fill fs-3 text-danger"></i>
                        <div>
                            <strong>Declaração não pode ser emitida no momento.</strong><br>
                            <span class="small">Existem requisitos pendentes listados acima que devem ser cumpridos pelo estagiário ou orientador.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <?php if ($eligibility['eligible']): ?>
                    <form action="/admin/interns/<?= $intern['id'] ?>/generate-certificate" method="POST" class="mb-0">
                        <?= \App\Helpers\csrf_field() ?>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-patch-check-fill me-1"></i> Emitir Agora
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
