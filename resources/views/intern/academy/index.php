<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Academia Asoftmedia</h4>
        <p class="text-muted small mb-0">Plataforma integrada de capacitação técnica, videoaulas, manuais em PDF e testes práticos.</p>
    </div>
</div>

<!-- Mandatory Courses Global Progress Bar (Section 24) -->
<div class="card shadow-sm border-0 mb-4 bg-primary text-white">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-primary px-3 py-1 mb-2 fw-bold">Trilha Obrigatória do Estágio</span>
            <h4 class="fw-bold mb-1">Progresso dos Cursos Obrigatórios: <?= $mandatoryStats['percentage'] ?>%</h4>
            <p class="mb-0 text-white-50 small">
                <?= $mandatoryStats['completed'] ?> de <?= $mandatoryStats['total'] ?> cursos obrigatórios totalmente concluídos.
            </p>
        </div>
        <div style="min-width: 250px;">
            <div class="progress bg-white bg-opacity-25" style="height: 12px; border-radius: 6px;">
                <div class="progress-bar bg-warning text-dark fw-bold" role="progressbar" style="width: <?= $mandatoryStats['percentage'] ?>%"></div>
            </div>
            <div class="text-end text-white-50 small mt-1"><?= $mandatoryStats['percentage'] ?>% concluído</div>
        </div>
    </div>
</div>

<!-- Courses Grid -->
<div class="row g-4">
    <?php foreach ($courses as $c): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden">
                <div class="bg-primary text-white p-4 text-center">
                    <i class="bi bi-code-slash display-4 mb-2"></i>
                    <h5 class="fw-bold mb-0"><?= \App\Helpers\e($c['title']) ?></h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <?php if ($c['is_mandatory']): ?>
                                <span class="badge bg-primary">🔵 Obrigatório</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white">⚪ Opcional</span>
                            <?php endif; ?>
                            <span class="small text-muted fw-semibold">Nível: <?= ucfirst($c['level']) ?></span>
                        </div>

                        <p class="small text-muted mb-3"><?= \App\Helpers\e($c['description']) ?></p>

                        <!-- Course Individual Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="text-muted fw-semibold">Progresso</span>
                                <strong class="text-primary"><?= $c['progress_percentage'] ?? 0 ?>%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar <?= ($c['progress_percentage'] >= 100) ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $c['progress_percentage'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><?= $c['total_modules'] ?> Módulos • <?= $c['total_lessons'] ?> Aulas</span>
                        <a href="/intern/academy/course/<?= $c['id'] ?>" class="btn btn-primary btn-sm fw-bold">
                            <i class="bi bi-play-circle me-1"></i> Aceder Zona de Estudo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
