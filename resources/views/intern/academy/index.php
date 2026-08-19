<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Academia Asoftmedia</h4>
        <p class="text-muted small mb-0">Plataforma de capacitação, videoaulas, documentação técnica e avaliações práticas.</p>
    </div>
</div>

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
                        <div class="badge bg-success mb-2">Trilha Obrigatória</div>
                        <p class="small text-muted"><?= \App\Helpers\e($c['description']) ?></p>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><?= $c['total_modules'] ?> Módulos • <?= $c['total_lessons'] ?> Aulas</span>
                        <a href="/intern/academy/course/<?= $c['id'] ?>" class="btn btn-primary btn-sm fw-bold">
                            <i class="bi bi-play-circle me-1"></i> Aceder
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
