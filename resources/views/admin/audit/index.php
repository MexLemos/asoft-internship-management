<div class="row g-4">
    <!-- Suspicious Attendance Attempts -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger">
                    <i class="bi bi-shield-exclamation me-2"></i> Tentativas Suspeitas / Bloqueadas de Presença
                </span>
                <span class="badge bg-danger"><?= count($suspiciousAttempts) ?> registos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Estagiário</th>
                                <th>Tipo</th>
                                <th>Distância</th>
                                <th>Motivo do Bloqueio</th>
                                <th>IP & Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suspiciousAttempts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Nenhuma tentativa suspeita registrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suspiciousAttempts as $att): ?>
                                    <tr>
                                        <td><?= \App\Helpers\format_date($att['attempt_time'], true) ?></td>
                                        <td>
                                            <strong><?= \App\Helpers\e($att['full_name']) ?></strong> (<?= \App\Helpers\e($att['internship_code']) ?>)
                                        </td>
                                        <td><span class="badge bg-secondary"><?= \App\Helpers\e($att['type']) ?></span></td>
                                        <td>
                                            <span class="badge bg-danger"><?= round((float)$att['distance_meters']) ?>m da Asoftmedia</span>
                                        </td>
                                        <td class="text-danger fw-semibold"><?= \App\Helpers\e($att['failure_reason']) ?></td>
                                        <td class="text-muted"><?= \App\Helpers\e($att['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- General System Audit Logs -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark">
                    <i class="bi bi-journal-text me-2 text-primary"></i> Registo Cronológico de Ações do Sistema (Audit Logs)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Utilizador</th>
                                <th>Módulo</th>
                                <th>Ação</th>
                                <th>Resultado</th>
                                <th>Endereço IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td><?= \App\Helpers\format_date($l['created_at'], true) ?></td>
                                    <td>
                                        <strong><?= \App\Helpers\e($l['user_name'] ?? 'Sistema / Visitante') ?></strong>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= \App\Helpers\e($l['module']) ?></span></td>
                                    <td><code><?= \App\Helpers\e($l['action']) ?></code></td>
                                    <td>
                                        <?php if ($l['result'] === 'success'): ?>
                                            <span class="badge bg-success">Sucesso</span>
                                        <?php elseif ($l['result'] === 'suspicious'): ?>
                                            <span class="badge bg-warning text-dark">Suspeito</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Falha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?= \App\Helpers\e($l['ip_address']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
