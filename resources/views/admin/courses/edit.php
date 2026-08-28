<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/admin/courses">Gestão de Cursos</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($course['title']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-journal-code text-primary me-2"></i> Estrutura & Conteúdos da Zona de Estudo
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="/intern/academy/course/<?= $course['id'] ?>" target="_blank" class="btn btn-outline-info text-dark btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Ver como Aluno
        </a>
        <a href="/admin/courses" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar aos Cursos
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Course Metadata Settings -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 80px;">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">Configurações Gerais do Curso</h6>
            </div>
            <div class="card-body p-4">
                <form action="/admin/courses/<?= $course['id'] ?>/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título do Curso *</label>
                        <input type="text" name="title" class="form-control" value="<?= \App\Helpers\e($course['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Descrição do Curso</label>
                        <textarea name="description" class="form-control" rows="3"><?= \App\Helpers\e($course['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipo de Curso *</label>
                        <select name="is_mandatory" class="form-select">
                            <option value="1" <?= !empty($course['is_mandatory']) ? 'selected' : '' ?>>🔵 Obrigatório</option>
                            <option value="0" <?= empty($course['is_mandatory']) ? 'selected' : '' ?>>⚪ Opcional / Eletivo</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Estado</label>
                            <select name="status" class="form-select">
                                <option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                                <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Ordem</label>
                            <input type="number" name="order_index" class="form-control" value="<?= $course['order_index'] ?>" min="1">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold btn-sm">
                        <i class="bi bi-save me-1"></i> Gravar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Curriculum Tree (Modules -> Lessons -> Contents) -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-collection-play-fill text-primary me-2"></i> Conteúdos Pedagógicos do Curso
                </h5>
                <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddModule">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Módulo
                </button>
            </div>
            <div class="card-body p-4">
                <?php if (empty($course['modules'])): ?>
                    <div class="p-5 text-center bg-light rounded-3 text-muted">
                        <i class="bi bi-diagram-3 display-4 text-primary mb-3"></i>
                        <h5>Nenhum módulo criado para este curso.</h5>
                        <p class="small text-muted mb-3">Organize o curso em módulos para depois adicionar videoaulas e manuais em PDF.</p>
                        <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddModule">
                            <i class="bi bi-plus-lg me-1"></i> Criar Primeiro Módulo
                        </button>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="modulesAccordion">
                        <?php foreach ($course['modules'] as $mIdx => $mod): ?>
                            <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-xs">
                                <div class="accordion-header bg-light p-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary">Módulo <?= $mIdx + 1 ?></span>
                                        <strong class="text-dark fs-6"><?= \App\Helpers\e($mod['title']) ?></strong>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-success btn-sm py-1" onclick="openAddLessonModal(<?= $mod['id'] ?>, '<?= \App\Helpers\e($mod['title']) ?>')">
                                            <i class="bi bi-plus-circle me-1"></i> Nova Aula
                                        </button>
                                        <form action="/admin/courses/modules/<?= $mod['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover este módulo e todas as suas aulas?')">
                                            <?= \App\Helpers\csrf_field() ?>
                                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1" title="Excluir Módulo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="p-3 bg-white">
                                    <?php if (empty($mod['lessons'])): ?>
                                        <p class="small text-muted py-2 mb-0 text-center">Nenhuma aula neste módulo. Clique em "+ Nova Aula" para adicionar.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($mod['lessons'] as $les): ?>
                                                <div class="list-group-item p-3 border rounded-2 mb-2 bg-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <i class="bi bi-play-btn-fill text-primary me-2"></i>
                                                            <strong class="text-dark small"><?= \App\Helpers\e($les['title']) ?></strong>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            <button type="button" class="btn btn-primary btn-sm py-0 px-2 small" onclick="openAddContentModal(<?= $les['id'] ?>, '<?= \App\Helpers\e($les['title']) ?>')">
                                                                <i class="bi bi-plus-lg"></i> Conteúdo
                                                            </button>
                                                            <form action="/admin/courses/lessons/<?= $les['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover esta aula?')">
                                                                <?= \App\Helpers\csrf_field() ?>
                                                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Excluir Aula">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <!-- Learning Contents List under this Lesson -->
                                                    <?php if (empty($les['contents'])): ?>
                                                        <div class="small text-muted py-1 fst-italic">Sem materiais configurados. Clique em "+ Conteúdo" para adicionar vídeo ou PDF.</div>
                                                    <?php else: ?>
                                                        <div class="list-group list-group-flush ms-3">
                                                            <?php foreach ($les['contents'] as $cnt): ?>
                                                                <div class="list-group-item p-2 d-flex justify-content-between align-items-center bg-white rounded-2 border mb-1 small">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <?php if ($cnt['content_type'] === 'youtube_video'): ?>
                                                                            <i class="bi bi-youtube text-danger fs-5"></i>
                                                                            <span><strong>Vídeo YouTube:</strong> <?= \App\Helpers\e($cnt['title']) ?></span>
                                                                        <?php elseif ($cnt['content_type'] === 'pdf_document'): ?>
                                                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                                                            <span><strong>Manual PDF:</strong> <?= \App\Helpers\e($cnt['title']) ?></span>
                                                                        <?php else: ?>
                                                                            <i class="bi bi-file-text-fill text-info fs-5"></i>
                                                                            <span><strong>Texto/Artigo:</strong> <?= \App\Helpers\e($cnt['title']) ?></span>
                                                                        <?php endif; ?>
                                                                        <span class="text-muted">(<?= $cnt['duration_minutes'] ?> min)</span>
                                                                    </div>
                                                                    <form action="/admin/courses/contents/<?= $cnt['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover este conteúdo?')">
                                                                        <?= \App\Helpers\csrf_field() ?>
                                                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                                        <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none" title="Remover Conteúdo">
                                                                            <i class="bi bi-x-circle"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Adicionar Módulo -->
<div class="modal fade" id="modalAddModule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/courses/<?= $course['id'] ?>/modules/add" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">Adicionar Novo Módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título do Módulo *</label>
                        <input type="text" name="title" class="form-control" placeholder="ex: Módulo 1: Fundamentos de Arquitetura e Roteamento" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Descrição / Tópicos do Módulo</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Resumo do conteúdo programático..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ordem de Exibição</label>
                        <input type="number" name="order_index" class="form-control" value="<?= count($course['modules']) + 1 ?>" min="1">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Módulo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Adicionar Aula -->
<div class="modal fade" id="modalAddLesson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAddLesson" action="" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">Nova Aula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Módulo Selecionado:</label>
                        <input type="text" id="lessonModuleName" class="form-control bg-light" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título da Aula *</label>
                        <input type="text" name="title" class="form-control" placeholder="ex: Aula 1: Instalação e Estrutura de Pastas" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ordem de Exibição</label>
                        <input type="number" name="order_index" class="form-control" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Criar Aula</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Adicionar Conteúdo (Vídeo / PDF / Texto) -->
<div class="modal fade" id="modalAddContent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAddContent" action="" method="POST" enctype="multipart/form-data">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">Adicionar Conteúdo à Zona de Estudo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Aula Selecionada:</label>
                        <input type="text" id="contentLessonName" class="form-control bg-light" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Título do Conteúdo *</label>
                            <input type="text" name="title" class="form-control" placeholder="ex: Videoaula: Princípios SOLID em PHP" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tipo de Material *</label>
                            <select name="content_type" id="selectContentType" class="form-select" required onchange="handleContentTypeChange(this.value)">
                                <option value="youtube_video" selected>🎥 Vídeo do YouTube</option>
                                <option value="pdf_document">📄 Documento / Manual PDF</option>
                                <option value="text_document">📝 Artigo / Texto Formatado</option>
                            </select>
                        </div>
                    </div>

                    <!-- YouTube Video Input -->
                    <div class="mb-3" id="groupYoutubeUrl">
                        <label class="form-label small fw-semibold">URL do Vídeo no YouTube *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger text-white"><i class="bi bi-youtube"></i></span>
                            <input type="url" name="content_url_or_path" id="inputYoutubeUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=DuB6UjEsBQk">
                        </div>
                    </div>

                    <!-- PDF Upload Input -->
                    <div class="mb-3 d-none" id="groupPdfUpload">
                        <label class="form-label small fw-semibold">Carregar Ficheiro PDF *</label>
                        <input type="file" name="pdf_file" id="inputPdfFile" class="form-control" accept="application/pdf">
                        <div class="form-text small">Envie o documento em formato .PDF para ser embutido no leitor do aluno.</div>
                    </div>

                    <!-- Text Article Input -->
                    <div class="mb-3 d-none" id="groupTextBody">
                        <label class="form-label small fw-semibold">Corpo do Artigo / Conteúdo Textual *</label>
                        <textarea name="article_body" id="inputTextBody" class="form-control font-monospace" rows="6" placeholder="Escreva aqui o texto explicativo, comandos e código da aula..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Duração Estimada (Minutos)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="15" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ordem de Exibição</label>
                            <input type="number" name="order_index" class="form-control" value="1" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-cloud-upload me-1"></i> Publicar na Zona de Estudo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddLessonModal(moduleId, moduleTitle) {
    document.getElementById('formAddLesson').action = `/admin/courses/modules/${moduleId}/lessons/add`;
    document.getElementById('lessonModuleName').value = moduleTitle;
    new bootstrap.Modal(document.getElementById('modalAddLesson')).show();
}

function openAddContentModal(lessonId, lessonTitle) {
    document.getElementById('formAddContent').action = `/admin/courses/lessons/${lessonId}/contents/add`;
    document.getElementById('contentLessonName').value = lessonTitle;
    new bootstrap.Modal(document.getElementById('modalAddContent')).show();
}

function handleContentTypeChange(type) {
    const groupYoutube = document.getElementById('groupYoutubeUrl');
    const groupPdf = document.getElementById('groupPdfUpload');
    const groupText = document.getElementById('groupTextBody');

    groupYoutube.classList.add('d-none');
    groupPdf.classList.add('d-none');
    groupText.classList.add('d-none');

    if (type === 'youtube_video') {
        groupYoutube.classList.remove('d-none');
    } else if (type === 'pdf_document') {
        groupPdf.classList.remove('d-none');
    } else if (type === 'text_document' || type === 'article_html') {
        groupText.classList.remove('d-none');
    }
}
</script>
