<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1"><?= \App\Helpers\e($intern['full_name']) ?></h4>
                <div class="text-muted small mb-2"><?= \App\Helpers\e($intern['course']) ?></div>
                <div class="badge bg-secondary mb-3"><?= \App\Helpers\e($intern['internship_code']) ?></div>

                <ul class="list-group list-group-flush text-start small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Área de Estágio:</span>
                        <strong><?= \App\Helpers\e($intern['internship_area']) ?></strong>
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
                        <span class="text-muted">Classificação:</span>
                        <strong class="text-success fs-6"><?= number_format((float)$intern['overall_score'], 1) ?> / 100</strong>
                    </li>
                </ul>

                <div class="mt-3">
                    <a href="/institution/dashboard" class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Voltar à Lista de Alunos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Attendance History (Read-only) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-check me-2 text-primary"></i> Histórico de Frequência do Aluno (Presenças GPS)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Horas</th>
                                <th>Distância</th>
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
                                    <td><?= round((float)$att['check_in_distance_meters']) ?>m</td>
                                    <td><span class="badge bg-success"><?= \App\Helpers\e($att['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Competencies Validated -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-award me-2 text-warning"></i> Competências Práticas Demonstradas
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($competencies as $comp): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><?= \App\Helpers\e($comp['name']) ?></strong>
                                    <span class="badge bg-primary">Nível <?= $comp['current_level'] ?> / 5</span>
                                </div>
                                <div class="text-muted small"><?= \App\Helpers\e($comp['category_name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
