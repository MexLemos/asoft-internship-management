<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/admin/interns">Estagiários</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($intern['full_name']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-person-badge text-primary me-2"></i> Perfil do Estagiário
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/interns/<?= $intern['id'] ?>/edit" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> Editar Dados do Estagiário
        </a>
        <a href="/admin/interns" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>
</div>

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
                <div class="d-flex justify-content-center gap-2 mb-2">
                    <span class="badge bg-secondary"><?= \App\Helpers\e($intern['internship_code']) ?></span>
                    <span class="badge <?= \App\Models\Intern::getStatusBadge($intern['status']) ?>"><?= \App\Models\Intern::getStatusLabel($intern['status']) ?></span>
                </div>
                <div class="small text-muted mb-3">
                    <i class="bi bi-laptop me-1"></i> Regime: <strong><?= ($intern['work_mode'] ?? '') === 'remote' ? 'Remoto' : (($intern['work_mode'] ?? '') === 'hybrid' ? 'Híbrido' : 'Presencial') ?></strong>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="/admin/interns/<?= $intern['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Editar Dados Cadastrais
                    </a>
                    <button type="button" class="btn btn-outline-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalChangeStatus">
                        <i class="bi bi-arrow-repeat me-1"></i> Alterar Estado do Estágio
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalNewMentorshipLog">
                        <i class="bi bi-chat-heart me-1"></i> Registar Mentoria / 1-on-1
                    </button>
                </div>

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
                                <div class="text-muted small fw-semibold"><?= \App\Helpers\e($comp['label'] ?? ucfirst($name)) ?> (Peso <?= $comp['weight'] ?>%)</div>
                                <div class="fs-4 fw-bold text-dark mt-1"><?= number_format((float)$comp['score'], 1) ?><span class="fs-6 text-muted fw-normal">/100</span></div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <?php
                                        $sc = (float)$comp['score'];
                                        $barCls = $sc >= 80 ? 'bg-success' : ($sc >= 60 ? 'bg-primary' : ($sc > 0 ? 'bg-warning' : 'bg-secondary'));
                                    ?>
                                    <div class="progress-bar <?= $barCls ?>" role="progressbar" style="width: <?= min(100, max(0, $sc)) ?>%"></div>
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
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="mentorship-tab" data-bs-toggle="tab" data-bs-target="#mentorship-pane" type="button">
                    <i class="bi bi-chat-heart me-1"></i> Mentoria & 1-on-1 (<?= count($mentorshipLogs) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button">
                    <i class="bi bi-clock-history me-1"></i> Ciclo de Vida & Estados (<?= count($statusHistory) ?>)
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

            <!-- Mentorship & 1-on-1 Tab -->
            <div class="tab-pane fade" id="mentorship-pane">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-chat-heart text-info me-2"></i> Sessões de Acompanhamento e Mentoria Contínua
                    </h6>
                    <button type="button" class="btn btn-outline-info btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalNewMentorshipLog">
                        <i class="bi bi-plus-circle me-1"></i> Nova Sessão
                    </button>
                </div>
                <?php if (empty($mentorshipLogs)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-journal-x fs-1 text-secondary opacity-50 mb-2"></i>
                        <p class="mb-0 small">Nenhuma sessão de mentoria ou acompanhamento 1-on-1 registada até ao momento.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mentorshipLogs as $m): ?>
                            <div class="list-group-item p-3 border rounded-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark me-2">
                                            <?= \App\Models\MentorshipLog::TYPES[$m['session_type']] ?? ucfirst($m['session_type']) ?>
                                        </span>
                                        <?php if (!empty($m['is_private'])): ?>
                                            <span class="badge bg-danger"><i class="bi bi-lock me-1"></i> Privado</span>
                                        <?php endif; ?>
                                        <h6 class="fw-bold d-inline mb-0 text-dark"><?= \App\Helpers\e($m['title']) ?></h6>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y H:i', strtotime($m['session_date'])) ?>
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
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-person-check me-1"></i> Orientador: <strong><?= \App\Helpers\e($m['supervisor_name']) ?></strong>
                                    </small>
                                    <?php if (!empty($m['rating'])): ?>
                                        <small class="text-warning">
                                            <?= str_repeat('★', (int)$m['rating']) . str_repeat('☆', 5 - (int)$m['rating']) ?>
                                            <span class="text-muted">(<?= $m['rating'] ?>/5)</span>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- State History Tab -->
            <div class="tab-pane fade" id="history-pane">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-secondary me-2"></i> Trilha de Auditoria do Ciclo de Vida
                    </h6>
                    <button type="button" class="btn btn-outline-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalChangeStatus">
                        <i class="bi bi-arrow-repeat me-1"></i> Transitar Estado
                    </button>
                </div>
                <?php if (empty($statusHistory)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-hourglass-split fs-1 text-secondary opacity-50 mb-2"></i>
                        <p class="mb-0 small">Nenhuma transição de estado registada no histórico deste estagiário.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($statusHistory as $sh): ?>
                            <div class="p-3 border rounded-3 mb-2 bg-light">
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
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($sh['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="small text-dark mt-1">
                                    <strong>Justificação:</strong> <?= \App\Helpers\e($sh['reason']) ?>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-person me-1"></i> Alterado por: <strong><?= \App\Helpers\e($sh['changer_name']) ?></strong> (<?= \App\Helpers\e($sh['changer_email']) ?>)
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

<!-- Modal Alterar Estado do Estágio (State Machine) -->
<div class="modal fade" id="modalChangeStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/interns/<?= $intern['id'] ?>/change-status" method="POST" class="modal-content">
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
                    <label for="status" class="form-label small fw-bold">Novo Estado Permitido <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Selecione o novo estado --</option>
                        <?php foreach ($availableTransitions as $targetStatus): ?>
                            <option value="<?= $targetStatus ?>">
                                <?= \App\Models\Intern::getStatusLabel($targetStatus) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small">
                        Apenas são exibidas as transições legalmente autorizadas pela máquina de estados do sistema.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label small fw-bold">Justificação Formal / Motivo <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Indique o motivo detalhado para a alteração deste estado..." required></textarea>
                    <div class="form-text small">
                        Este registo ficará permanentemente gravado na trilha de auditoria e será notificado ao estagiário.
                    </div>
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

<!-- Modal Registar Mentoria / 1-on-1 -->
<div class="modal fade" id="modalNewMentorshipLog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="/admin/interns/<?= $intern['id'] ?>/mentorship/store" method="POST" class="modal-content">
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
                        <input type="text" name="title" class="form-control" placeholder="Ex: Alinhamento semanal de metas e postura" required>
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
                        <label class="form-label small fw-bold">Resumo da Conversa / Parecer <span class="text-danger">*</span></label>
                        <textarea name="summary" rows="3" class="form-control" placeholder="Descreva os pontos principais abordados na reunião..." required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tópicos Discutidos</label>
                        <textarea name="topics_discussed" rows="2" class="form-control" placeholder="Tópicos técnicos, comportamentais ou acadêmicos..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Plano de Ação / Próximos Passos</label>
                        <textarea name="action_items" rows="2" class="form-control" placeholder="Compromissos assumidos pelo estagiário..."></textarea>
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
                            <input class="form-check-input" type="checkbox" name="is_private" value="1" id="is_private_check">
                            <label class="form-check-label small" for="is_private_check">
                                <strong>Registo Privado</strong> (Ocultar do estagiário, visível apenas a supervisores/direção)
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
