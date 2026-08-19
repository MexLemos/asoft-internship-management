<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/intern/academy">Academia</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($course['title']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark"><?= \App\Helpers\e($course['title']) ?></h4>
    </div>
</div>

<div class="row g-4">
    <!-- Main Player / Reader Column (Left) -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-body p-0">
                <?php if ($activeContent && $activeContent['content_type'] === 'youtube_video'): ?>
                    <?php
                    // Extract video ID safely
                    $videoUrl = $activeContent['content_url_or_path'];
                    $videoId = 'DuB6UjEsBQk'; // fallback default
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match)) {
                        $videoId = $match[1];
                    }
                    ?>
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>" allowfullscreen></iframe>
                    </div>
                <?php elseif ($activeContent && $activeContent['content_type'] === 'pdf_document'): ?>
                    <div class="p-5 text-center bg-light">
                        <i class="bi bi-file-earmark-pdf-fill text-danger display-2 mb-3"></i>
                        <h5 class="fw-bold"><?= \App\Helpers\e($activeContent['title']) ?></h5>
                        <p class="text-muted small">Documento PDF oficial de estudo e consulta técnica.</p>
                        <a href="<?= \App\Helpers\e($activeContent['content_url_or_path']) ?>" target="_blank" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-download me-1"></i> Abrir / Descarregar PDF
                        </a>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center bg-light">
                        <i class="bi bi-book display-3 text-primary mb-3"></i>
                        <h5>Selecione um conteúdo no menu ao lado para iniciar.</h5>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($activeContent): ?>
                <div class="card-footer bg-white p-4 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0"><?= \App\Helpers\e($activeContent['title']) ?></h5>
                        <button class="btn btn-success btn-sm" onclick="markCompleted(<?= $activeContent['id'] ?>)">
                            <i class="bi bi-check-lg me-1"></i> Marcar como Concluído
                        </button>
                    </div>
                    <p class="text-muted small mb-0"><?= \App\Helpers\e($activeContent['article_body'] ?? 'Material de estudo fornecido pela Asoftmedia.') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Curriculum Sidebar (Right) -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-bookmark-fill me-2 text-primary"></i> Módulos & Aulas
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="accordionModules">
                    <?php foreach ($course['modules'] as $idx => $mod): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $mod['id'] ?>">
                                <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> py-3 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $mod['id'] ?>">
                                    <?= \App\Helpers\e($mod['title']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $mod['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#accordionModules">
                                <div class="accordion-body p-0">
                                    <div class="list-group list-group-flush small">
                                        <?php foreach ($mod['lessons'] as $les): ?>
                                            <div class="bg-light px-3 py-2 fw-semibold text-muted" style="font-size: 11px;">
                                                <?= \App\Helpers\e($les['title']) ?>
                                            </div>
                                            <?php foreach ($les['contents'] as $cnt): ?>
                                                <a href="/intern/academy/course/<?= $course['id'] ?>?content=<?= $cnt['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 <?= ($activeContent && (int)$activeContent['id'] === (int)$cnt['id']) ? 'active' : '' ?>">
                                                    <div>
                                                        <?php if ($cnt['content_type'] === 'youtube_video'): ?>
                                                            <i class="bi bi-play-circle me-1"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                                        <?php endif; ?>
                                                        <?= \App\Helpers\e($cnt['title']) ?>
                                                    </div>
                                                    <?php if (($cnt['progress_status'] ?? '') === 'completed'): ?>
                                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i></span>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>

                                        <?php if (!empty($mod['test_id'])): ?>
                                            <a href="/intern/tests/<?= $mod['test_id'] ?>" class="list-group-item list-group-item-action bg-warning bg-opacity-10 py-3 text-center fw-bold text-dark">
                                                <i class="bi bi-patch-question-fill text-warning me-1"></i> Realizar Teste de Avaliação do Módulo
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function markCompleted(contentId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`/intern/academy/content/${contentId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ _csrf_token: csrfToken })
        });
        if (res.ok) {
            window.location.reload();
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
