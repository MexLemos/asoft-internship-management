<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/intern/academy">Academia</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($course['title']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark"><?= \App\Helpers\e($course['title']) ?></h4>
            <span class="badge bg-primary"><?= $course['progress_percentage'] ?>% Concluído</span>
        </div>
    </div>
    <a href="/intern/academy" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar aos Cursos
    </a>
</div>

<div class="row g-4">
    <!-- Main Player / Reader Column (Left) -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-body p-0">
                <?php if ($activeContent && $activeContent['content_type'] === 'youtube_video'): ?>
                    <?php
                    $videoUrl = $activeContent['content_url_or_path'];
                    $videoId = 'DuB6UjEsBQk';
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match)) {
                        $videoId = $match[1];
                    }
                    ?>
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>" allowfullscreen></iframe>
                    </div>

                <?php elseif ($activeContent && $activeContent['content_type'] === 'pdf_document'): ?>
                    <!-- Embedded PDF Viewer (Section 22) -->
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-5 me-1"></i>
                            <strong><?= \App\Helpers\e($activeContent['title']) ?></strong>
                        </div>
                        <a href="<?= \App\Helpers\e($activeContent['content_url_or_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir em Nova Aba
                        </a>
                    </div>
                    <iframe src="<?= \App\Helpers\e($activeContent['content_url_or_path']) ?>" style="width: 100%; height: 550px; border: none;"></iframe>

                <?php elseif ($activeContent && $activeContent['content_type'] === 'text_document'): ?>
                    <!-- Embedded TXT / Markdown Reader (Section 22) -->
                    <div class="p-4 bg-white">
                        <h5 class="fw-bold mb-3"><?= \App\Helpers\e($activeContent['title']) ?></h5>
                        <pre class="p-3 bg-light rounded-3 text-dark font-monospace small" style="white-space: pre-wrap; max-height: 500px; overflow-y: auto;"><?= htmlspecialchars($activeContent['article_body'] ?? 'Conteúdo em formato texto.') ?></pre>
                    </div>

                <?php else: ?>
                    <div class="p-5 text-center bg-light">
                        <i class="bi bi-book display-3 text-primary mb-3"></i>
                        <h5>Selecione uma aula no menu ao lado para iniciar.</h5>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($activeContent): ?>
                <div class="card-footer bg-white p-4 border-top">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                        <h5 class="fw-bold text-dark mb-0"><?= \App\Helpers\e($activeContent['title']) ?></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDoubt">
                                <i class="bi bi-question-circle-fill text-warning me-1"></i> Tenho uma dúvida
                            </button>
                            <button class="btn btn-success btn-sm fw-bold" onclick="markCompleted(<?= $activeContent['id'] ?>)">
                                <i class="bi bi-check-lg me-1"></i> Concluir Aula
                            </button>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><?= \App\Helpers\e($activeContent['article_body'] ?? 'Material de estudo oficial fornecido pela Asoftmedia.') ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Doubts & Q&A Thread (Section 23) -->
        <?php if ($activeContent): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-chat-quote-fill me-2 text-warning"></i> Dúvidas & Perguntas desta Aula (<?= count($doubts) ?>)
                    </h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDoubt">
                        <i class="bi bi-plus-lg me-1"></i> Perguntar
                    </button>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($doubts)): ?>
                        <p class="text-muted small text-center py-2 mb-0">Nenhuma dúvida enviada para esta aula. Seja o primeiro a perguntar!</p>
                    <?php else: ?>
                        <?php foreach ($doubts as $d): ?>
                            <div class="p-3 border rounded-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><i class="bi bi-person-circle me-1"></i> <?= \App\Helpers\e($d['intern_name']) ?></strong>
                                    <span class="text-muted small" style="font-size: 11px;"><?= \App\Helpers\format_date($d['created_at'], true) ?></span>
                                </div>
                                <p class="small text-dark mb-2"><?= \App\Helpers\e($d['question']) ?></p>

                                <?php if (!empty($d['answer'])): ?>
                                    <div class="p-3 bg-white border-start border-4 border-success rounded-end small mt-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Resposta do Orientador (<?= \App\Helpers\e($d['answerer_name'] ?? 'Supervisor') ?>):</strong>
                                            <span class="text-muted" style="font-size: 10px;"><?= \App\Helpers\format_date($d['answered_at'], true) ?></span>
                                        </div>
                                        <p class="mb-0 text-dark"><?= \App\Helpers\e($d['answer']) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted fst-italic">
                                        <i class="bi bi-hourglass me-1"></i> Aguardando resposta do orientador...
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
                                                        <?php elseif ($cnt['content_type'] === 'pdf_document'): ?>
                                                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-file-earmark-text me-1 text-info"></i>
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

<!-- Modal Enviar Dúvida (Section 23) -->
<?php if ($activeContent): ?>
<div class="modal fade" id="modalDoubt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/intern/academy/doubt/<?= $activeContent['id'] ?>" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-question-circle-fill me-1 text-warning"></i> Enviar Dúvida ao Orientador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Conteúdo / Aula Relacionada:</label>
                        <input type="text" class="form-control bg-light" value="<?= \App\Helpers\e($activeContent['title']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sua Dúvida ou Pergunta *</label>
                        <textarea name="question" class="form-control" rows="4" placeholder="Descreva detalhadamente sua dúvida ou dificuldade neste tópico..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-send me-1"></i> Enviar Dúvida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
