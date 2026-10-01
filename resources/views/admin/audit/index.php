<div class="row g-4">
    <!-- Database Schema Health & Auto-Sync Status -->
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle <?= !empty($isSchemaSynced) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                        <i class="bi <?= !empty($isSchemaSynced) ? 'bi-database-check' : 'bi-database-exclamation' ?> fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Esquema da Base de Dados MySQL (Hostinger)</h6>
                        <small class="text-muted">
                            <?php if (!empty($isSchemaSynced)): ?>
                                <span class="badge bg-success me-1">Sincronizado</span> Todas as tabelas das Fases 1 a 5 (Ciclo de Vida, Dispositivos e Dynamic QR) estão ativas e conformes.
                            <?php else: ?>
                                <span class="badge bg-warning text-dark me-1">Atualização Pendente</span> Existem tabelas ou colunas da nova versão que ainda requerem sincronização.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div>
                    <form action="/admin/audit/sync-schema" method="POST" class="d-inline">
                        <?= \App\Helpers\csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Sincronizar Esquema Agora
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                        <td>
                                            <div class="text-dark small"><?= \App\Helpers\e($att['ip_address'] ?? 'N/A') ?></div>
                                            <?php if (!empty($att['device_uuid'])): ?>
                                                <div class="font-monospace text-muted" style="font-size: 10px;" title="<?= \App\Helpers\e($att['device_uuid']) ?>">
                                                    <i class="bi bi-phone me-1"></i><?= substr($att['device_uuid'], 0, 12) ?>...
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($att['verification_method'])): ?>
                                                <span class="badge bg-light text-secondary border mt-1" style="font-size: 10px;"><?= \App\Helpers\e($att['verification_method']) ?></span>
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

    <!-- System PHP Error Logs (storage/logs/error.log) -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-secondary">
                    <i class="bi bi-bug-fill me-2 text-danger"></i> Registo de Erros de Execução do Sistema (storage/logs/error.log)
                </span>
                <span class="badge bg-secondary"><?= count($errorLogs ?? []) ?> linhas recentes</span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($errorLogs)): ?>
                    <div class="text-success small py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Nenhum erro recente registado nos ficheiros de logs do servidor.
                    </div>
                <?php else: ?>
                    <div class="bg-dark text-light p-3 rounded font-monospace small overflow-auto" style="max-height: 350px; font-size: 11px;">
                        <?php foreach ($errorLogs as $errLine): ?>
                            <div class="py-1 border-bottom border-secondary text-break"><?= htmlspecialchars($errLine) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
