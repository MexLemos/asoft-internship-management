<div class="row g-4">
    <!-- Left Column: Skills & Completed Tasks -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-person-badge-fill me-2"></i> Meu Portfólio Profissional Asoftmedia
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Competencies Developed -->
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Competências Práticas Validadas</h6>
                <div class="row g-3 mb-4">
                    <?php foreach ($competencies as $c): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><?= \App\Helpers\e($c['name']) ?></strong>
                                    <span class="badge bg-primary">Nível <?= $c['current_level'] ?> / 5</span>
                                </div>
                                <div class="text-muted small"><?= \App\Helpers\e($c['category_name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Projects & Tasks Completed -->
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Tarefas & Projetos Concluídos (<?= count($approvedTasks) ?>)</h6>
                <div class="list-group list-group-flush mb-3">
                    <?php foreach ($approvedTasks as $t): ?>
                        <div class="list-group-item p-3 border rounded-3 bg-white mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark"><?= \App\Helpers\e($t['title']) ?></strong>
                                <span class="badge bg-success">Nota: <?= number_format((float)$t['score'], 1) ?></span>
                            </div>
                            <p class="small text-muted mb-2"><?= \App\Helpers\e($t['description']) ?></p>
                            <?php if (!empty($t['supervisor_feedback'])): ?>
                                <div class="small bg-light p-2 rounded text-dark">
                                    <strong>Parecer:</strong> <?= \App\Helpers\e($t['supervisor_feedback']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: LinkedIn Sharing -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-linkedin text-primary me-2"></i> Partilhar no LinkedIn
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">Divulgue suas conquistas e competências desenvolvidas durante o seu estágio na Asoftmedia.</p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Mensagem Sugerida:</label>
                    <textarea id="linkedin-text" class="form-control form-control-sm" rows="6"><?= \App\Helpers\e($linkedInText) ?></textarea>
                </div>

                <button class="btn btn-outline-primary w-100 mb-2 btn-sm fw-bold" onclick="copyLinkedInText()">
                    <i class="bi bi-clipboard me-1"></i> Copiar Texto
                </button>
                <a href="https://www.linkedin.com/feed/" target="_blank" class="btn btn-primary w-100 btn-sm fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir LinkedIn
                </a>
            </div>
        </div>

        <!-- Badges Earned -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-award-fill text-warning me-2"></i> Conquistas Obtidas
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($badges as $b): ?>
                        <span class="badge bg-warning text-dark p-2 fs-6">
                            <i class="bi <?= $b['icon'] ?> me-1"></i> <?= \App\Helpers\e($b['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLinkedInText() {
    const copyText = document.getElementById("linkedin-text");
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    alert("Texto copiado para a área de transferência!");
}
</script>
