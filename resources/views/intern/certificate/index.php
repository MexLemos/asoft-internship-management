<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-patch-check-fill me-2"></i> Minha Declaração / Certificado de Estágio
                </h5>
            </div>
            <div class="card-body p-4 text-center">
                <?php if ($cert && $cert['status'] === 'valid'): ?>
                    <div class="p-4 border rounded-4 bg-white shadow-sm mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 72px; height: 72px;">
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Declaração de Estágio Emitida</h3>
                        <p class="text-muted small mb-4">Parabéns! O seu estágio curricular na Asoftmedia foi concluído com sucesso e aprovado pela Direcção.</p>

                        <div class="row g-3 text-start small mb-4 bg-light p-3 rounded-3">
                            <div class="col-md-6">
                                <span class="text-muted">Código do Documento:</span><br>
                                <strong><?= \App\Helpers\e($cert['certificate_code']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Carga Horária Cumprida:</span><br>
                                <strong><?= number_format((float)$cert['total_hours_completed'], 0) ?> Horas</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Classificação Final:</span><br>
                                <strong class="text-success fs-6"><?= number_format((float)$cert['final_score'], 1) ?> Valores</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Data de Emissão:</span><br>
                                <strong><?= \App\Helpers\format_date($cert['issue_date']) ?></strong>
                            </div>
                        </div>

                        <!-- QR Code Container -->
                        <div class="p-3 border rounded-3 bg-white d-inline-block mb-4">
                            <img src="<?= $qrCodeBase64 ?>" class="img-fluid" style="width: 150px; height: 150px;" alt="QR Code de Validação">
                            <div class="small text-muted mt-2">QR Code Oficial de Validação Pública</div>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?= $validationUrl ?>" target="_blank" class="btn btn-outline-primary px-4">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Abrir Link de Validação
                            </a>
                            <button class="btn btn-primary px-4 fw-bold" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Imprimir Declaração
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-circle mb-3" style="width: 72px; height: 72px;">
                            <i class="bi bi-hourglass-split fs-2"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Certificado em Processamento</h4>
                        <p class="text-muted small mb-4">A sua declaração oficial de estágio será gerada automaticamente assim que todos os requisitos de presença, tarefas e avaliações forem cumpridos e aprovados pela Asoftmedia.</p>

                        <div class="card bg-light border text-start p-3 mx-auto" style="max-width: 500px;">
                            <h6 class="fw-bold text-dark small mb-2">Requisitos de Conclusão:</h6>
                            <ul class="mb-0 small text-muted">
                                <li>Presença mínima de 80% (Atual: <?= $eligibility['attendance_percentage'] ?>%)</li>
                                <li>Conclusão das tarefas obrigatórias</li>
                                <li>Aprovação nos testes da Academia</li>
                                <li>Parecer e avaliação final pelo Supervisor</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
