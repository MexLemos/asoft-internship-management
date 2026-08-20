<div class="row g-4 justify-content-center">
    <div class="col-lg-10">
        <!-- Banner Conformidade -->
        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-white text-primary px-3 py-1 mb-2 fw-bold">Lei n.º 22/11 de Angola</span>
                    <h4 class="fw-bold mb-1">Privacidade & Protecção de Dados Pessoais</h4>
                    <p class="small text-white-50 mb-0">Consulte os seus consentimentos e exerça os seus direitos de titular dos dados.</p>
                </div>
                <a href="/politica-privacidade" target="_blank" class="btn btn-warning btn-sm text-dark fw-bold">
                    <i class="bi bi-file-earmark-text me-1"></i> Ler Política Completa
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Consents History & Exercise Rights -->
            <div class="col-lg-6">
                <!-- Consent Status Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-journal-check me-2 text-success"></i> Meus Consentimentos Registados
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($consents)): ?>
                            <div class="p-3 bg-light rounded-3 text-center small text-muted">
                                <i class="bi bi-info-circle fs-4 text-primary d-block mb-2"></i>
                                Nenhum consentimento formal registado até o momento.
                            </div>
                            <form action="/profile/privacy/consent" method="POST" class="mt-3">
                                <?= \App\Helpers\csrf_field() ?>
                                <input type="hidden" name="policy_version" value="1.0">
                                <button type="submit" class="btn btn-success w-100 fw-bold btn-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Confirmar Aceitação da Política v1.0
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($consents as $c): ?>
                                    <div class="list-group-item p-3 border rounded-3 bg-light mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-dark small">Política de Privacidade (v<?= \App\Helpers\e($c['policy_version']) ?>)</strong>
                                            <span class="badge bg-success">Aceite Registado</span>
                                        </div>
                                        <div class="text-muted small">
                                            Data: <?= \App\Helpers\format_date($c['accepted_at'], true) ?><br>
                                            IP: <code><?= \App\Helpers\e($c['ip_address']) ?></code>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- History of Data Rights Requests -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-clock-history me-2 text-primary"></i> Minhas Solicitações de Direitos
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($requests)): ?>
                            <p class="text-muted small mb-0 text-center py-2">Você não possui solicitações de direitos pendentes.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($requests as $r): ?>
                                    <div class="list-group-item p-3 border rounded-3 bg-white mb-2 shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-secondary text-capitalize"><?= \App\Helpers\e($r['request_type']) ?></span>
                                            <span class="badge <?= $r['status'] === 'fulfilled' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                <?= $r['status'] === 'fulfilled' ? 'Atendido' : 'Em Análise' ?>
                                            </span>
                                        </div>
                                        <p class="small text-dark mb-1"><strong>Pedido:</strong> <?= \App\Helpers\e($r['details']) ?></p>
                                        <div class="text-muted" style="font-size: 11px;"><?= \App\Helpers\format_date($r['created_at'], true) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form: Request Data Rights -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-send-plus-fill me-2"></i> Exercer Direitos do Titular (Art. 18.º Lei 22/11)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="/profile/privacy/request" method="POST">
                            <?= \App\Helpers\csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tipo de Solicitação *</label>
                                <select name="request_type" class="form-select" required>
                                    <option value="access">Direito de Acesso / Cópia dos Meus Dados</option>
                                    <option value="rectification">Direito de Rectificação / Correção de Informação</option>
                                    <option value="deletion">Direito de Eliminação / Cancelamento de Dados</option>
                                    <option value="opposition">Direito de Oposição a Determinado Tratamento</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Detalhes & Justificativa da Solicitação *</label>
                                <textarea name="details" class="form-control" rows="5" placeholder="Especifique detalhadamente quais dados pretende aceder, corrigir ou eliminar..." required></textarea>
                                <div class="form-text small text-muted">
                                    O Encarregado de Proteção de Dados da Asoftmedia analisará o pedido em conformidade com os prazos regulamentares da Lei 22/11.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Submeter Solicitação
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
