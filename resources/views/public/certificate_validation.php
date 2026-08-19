<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <?php if ($cert && $cert['status'] === 'valid'): ?>
                <div class="bg-success text-white p-4 text-center">
                    <i class="bi bi-patch-check-fill display-3"></i>
                    <h3 class="fw-bold mt-2 mb-1">Documento Oficial Válido</h3>
                    <p class="mb-0 text-white-50">Autenticidade confirmada pela base de dados oficial da Asoftmedia</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Estagiário(a)</label>
                            <h5 class="fw-bold text-dark"><?= \App\Helpers\e($cert['intern_name']) ?></h5>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Instituição de Ensino</label>
                            <h5 class="fw-bold text-dark"><?= \App\Helpers\e($cert['institution_name']) ?></h5>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Curso</label>
                            <div class="fw-semibold text-dark"><?= \App\Helpers\e($cert['course']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Área do Estágio</label>
                            <div class="fw-semibold text-dark"><?= \App\Helpers\e($cert['internship_area']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Período de Realização</label>
                            <div class="text-dark">
                                <?= \App\Helpers\format_date($cert['start_date']) ?> até <?= \App\Helpers\format_date($cert['end_date']) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Carga Horária / Nota Final</label>
                            <div class="text-dark">
                                <span class="badge bg-primary fs-6"><?= number_format((float)$cert['total_hours_completed'], 0) ?> Horas</span>
                                <span class="badge bg-success fs-6"><?= number_format((float)$cert['final_score'], 1) ?> Valores</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="bg-light p-3 rounded-3 small">
                        <div class="text-muted">Código do Certificado: <strong><?= \App\Helpers\e($cert['certificate_code']) ?></strong></div>
                        <div class="text-muted text-break">Hash Criptográfico SHA-256: <code><?= \App\Helpers\e($cert['validation_hash']) ?></code></div>
                        <div class="text-muted mt-1">Data de Emissão: <?= \App\Helpers\format_date($cert['issue_date']) ?> por <?= \App\Helpers\e($cert['signatory_name']) ?> (<?= \App\Helpers\e($cert['signatory_role']) ?>)</div>
                    </div>
                </div>

            <?php elseif ($cert && $cert['status'] === 'revoked'): ?>
                <div class="bg-danger text-white p-4 text-center">
                    <i class="bi bi-shield-slash-fill display-3"></i>
                    <h3 class="fw-bold mt-2 mb-1">Documento Revogado</h3>
                    <p class="mb-0 text-white-50">Este certificado foi cancelado e não possui mais validade oficial.</p>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="text-muted">Motivo: <?= \App\Helpers\e($cert['revocation_reason'] ?? 'Revogação administrativa') ?></p>
                </div>

            <?php else: ?>
                <div class="bg-secondary text-white p-4 text-center">
                    <i class="bi bi-question-circle-fill display-3"></i>
                    <h3 class="fw-bold mt-2 mb-1">Certificado Não Encontrado</h3>
                    <p class="mb-0 text-white-50">Não foi localizado nenhum documento oficial com o código fornecido.</p>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="text-muted">Código pesquisado: <code><?= \App\Helpers\e($searchedHash ?? '') ?></code></p>
                    <p class="small text-muted">Certifique-se de que escaneou o QR Code original ou contacte a Asoftmedia para esclarecimentos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
