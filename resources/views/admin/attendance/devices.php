<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Dispositivos Vinculados</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-phone text-primary me-2"></i> Gestão de Dispositivos & Prevenção de Fraude
        </h4>
        <p class="text-muted small mb-0">Controlo de aparelhos autorizados para marcação de presença e prevenção de partilha de credenciais.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/attendance/terminal" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-qr-code-scan me-1"></i> Abrir Terminal de Presença
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark">Aparelhos Registados por Estagiário (<?= count($devices) ?>)</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($devices)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-phone-vibrate fs-1 text-secondary opacity-50 mb-2"></i>
                <p class="mb-0">Nenhum dispositivo registado até ao momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Estagiário</th>
                            <th>Identificador (UUID)</th>
                            <th>Navegador / Sistema</th>
                            <th>Primeiro Acesso</th>
                            <th>Último Uso</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $dev): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($dev['intern_name']) ?></strong>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <code><?= \App\Helpers\e($dev['internship_code']) ?></code> &bull; <?= \App\Helpers\e($dev['course']) ?>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-primary"><?= substr(\App\Helpers\e($dev['device_uuid']), 0, 16) ?>...</code>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block text-muted" style="max-width: 250px;" title="<?= \App\Helpers\e($dev['user_agent']) ?>">
                                        <?= \App\Helpers\e($dev['device_name'] ?: 'Navegador Web') ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($dev['registered_at'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($dev['last_used_at'])) ?></td>
                                <td>
                                    <?php if ($dev['is_trusted']): ?>
                                        <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i> Autorizado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-shield-slash me-1"></i> Bloqueado / Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$dev['is_trusted']): ?>
                                            <form action="/admin/attendance/devices/<?= $dev['id'] ?>/trust" method="POST" class="d-inline">
                                                <?= \App\Helpers\csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-success" title="Autorizar Dispositivo">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="/admin/attendance/devices/<?= $dev['id'] ?>/block" method="POST" class="d-inline">
                                                <?= \App\Helpers\csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-warning text-dark" title="Bloquear Dispositivo">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form action="/admin/attendance/devices/<?= $dev['id'] ?>/remove" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja desvincular este dispositivo? O estagiário poderá registar um novo aparelho no próximo acesso.');">
                                            <?= \App\Helpers\csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Remover Vínculo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
