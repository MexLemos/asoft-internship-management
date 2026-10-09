<?php $basePath = !empty($supervisorContext) ? '/supervisor' : '/admin'; ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= $basePath ?>/courses">Gestão de Cursos</a></li>
                <li class="breadcrumb-item active"><?= \App\Helpers\e($course['title']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-journal-code text-primary me-2"></i> Estrutura & Conteúdos da Zona de Estudo
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $basePath ?>/courses/<?= $course['id'] ?>/preview" target="_blank" class="btn btn-outline-info text-dark btn-sm fw-semibold">
            <i class="bi bi-eye-fill me-1"></i> Visualizar como Aluno
        </a>
        <a href="<?= $basePath ?>/courses" class="btn btn-outline-secondary btn-sm">
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
                <form action="<?= $basePath ?>/courses/<?= $course['id'] ?>/update" method="POST">
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
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1" onclick="openEditModuleModal(<?= $mod['id'] ?>, '<?= \App\Helpers\e(addslashes($mod['title'])) ?>', '<?= \App\Helpers\e(addslashes($mod['description'] ?? '')) ?>', <?= (int)($mod['order_index'] ?? 1) ?>)" title="Editar Módulo">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-1" onclick="openImportPlaylistModal(<?= $mod['id'] ?>, '<?= \App\Helpers\e(addslashes($mod['title'])) ?>')" title="Importar Playlist ou Vídeos em Lote">
                                            <i class="bi bi-youtube me-1"></i> Playlist / Lote
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm py-1" onclick="openAddLessonModal(<?= $mod['id'] ?>, '<?= \App\Helpers\e(addslashes($mod['title'])) ?>')">
                                            <i class="bi bi-plus-circle me-1"></i> Nova Aula + Conteúdo
                                        </button>
                                        <form action="<?= $basePath ?>/courses/modules/<?= $mod['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover este módulo e todas as suas aulas?')">
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
                                        <p class="small text-muted py-2 mb-0 text-center">Nenhuma aula neste módulo. Clique em "+ Nova Aula + Conteúdo" ou "Playlist / Lote" para adicionar.</p>
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
                                                            <a href="<?= $basePath ?>/courses/<?= $course['id'] ?>/preview<?= !empty($les['contents'][0]['id']) ? '?content=' . $les['contents'][0]['id'] : '' ?>" target="_blank" class="btn btn-outline-info btn-sm py-0 px-2 small" title="Visualizar aula como Aluno">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="openEditLessonModal(<?= $les['id'] ?>, '<?= \App\Helpers\e(addslashes($les['title'])) ?>', <?= (int)($les['order_index'] ?? 1) ?>)" title="Editar Aula">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm py-0 px-2 small" onclick="openAddContentModal(<?= $les['id'] ?>, '<?= \App\Helpers\e(addslashes($les['title'])) ?>')" title="Adicionar Conteúdo Adicional">
                                                                <i class="bi bi-plus-lg"></i> Conteúdo
                                                            </button>
                                                            <form action="<?= $basePath ?>/courses/lessons/<?= $les['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover esta aula?')">
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
                                                                    <form action="<?= $basePath ?>/courses/contents/<?= $cnt['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Remover este conteúdo?')">
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
            <form action="<?= $basePath ?>/courses/<?= $course['id'] ?>/modules/add" method="POST">
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

<!-- Modal 2: Adicionar Aula + Conteúdo (Formulário Unificado) -->
<div class="modal fade" id="modalAddLesson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAddLesson" action="" method="POST" enctype="multipart/form-data">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-plus-square me-2"></i> Nova Aula com Conteúdo Pedagógico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Módulo Selecionado:</label>
                        <input type="text" id="lessonModuleName" class="form-control bg-light" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-9">
                            <label class="form-label small fw-semibold">Título da Aula *</label>
                            <input type="text" name="title" id="unifiedLessonTitle" class="form-control" placeholder="ex: Aula 1: Instalação e Estrutura de Pastas" required oninput="syncContentTitle(this.value)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Ordem de Exibição</label>
                            <input type="number" name="order_index" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <!-- Bloco de Conteúdo Integrado -->
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-collection-play me-1 text-primary"></i> Material / Conteúdo Inicial da Aula
                        </h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tipo de Material *</label>
                                <select name="content_type" id="unifiedContentType" class="form-select" onchange="handleUnifiedContentType(this.value)">
                                    <option value="youtube_video" selected>🎥 Vídeo do YouTube</option>
                                    <option value="pdf_document">📄 Documento / Manual PDF</option>
                                    <option value="text_document">📝 Artigo / Texto Formatado</option>
                                    <option value="none">⚪ Nenhum (Criar apenas tópico por enquanto)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Título do Conteúdo</label>
                                <input type="text" name="content_title" id="unifiedContentTitle" class="form-control" placeholder="Deixe em branco para usar o nome da aula">
                            </div>
                        </div>

                        <!-- YouTube Video Input -->
                        <div class="mb-3" id="unifiedGroupYoutube">
                            <label class="form-label small fw-semibold">URL do Vídeo no YouTube *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white"><i class="bi bi-youtube"></i></span>
                                <input type="url" name="content_url_or_path" id="unifiedYoutubeUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=DuB6UjEsBQk">
                            </div>
                        </div>

                        <!-- PDF Upload Input -->
                        <div class="mb-3 d-none" id="unifiedGroupPdf">
                            <label class="form-label small fw-semibold">Carregar Ficheiro PDF *</label>
                            <input type="file" name="pdf_file" id="unifiedPdfFile" class="form-control" accept="application/pdf">
                            <div class="form-text small">Envie o documento PDF para ser embutido na leitura do aluno.</div>
                        </div>

                        <!-- Text Article Input -->
                        <div class="mb-3 d-none" id="unifiedGroupText">
                            <label class="form-label small fw-semibold">Corpo do Artigo / Texto Explicativo *</label>
                            <textarea name="article_body" id="unifiedTextBody" class="form-control font-monospace" rows="5" placeholder="Texto com orientações, comandos e código..."></textarea>
                        </div>

                        <div class="row g-3" id="unifiedDurationRow">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Duração Estimada (Minutos)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="15" min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Criar Aula & Conteúdo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Importar Playlist / Lote do YouTube -->
<div class="modal fade" id="modalImportPlaylist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formImportPlaylist" action="" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-youtube me-2"></i> Importar Playlist / Vídeos em Lote
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Módulo de Destino:</label>
                        <input type="text" id="playlistModuleName" class="form-control bg-light" readonly>
                    </div>

                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i>
                        Você pode colar a <strong>URL da playlist</strong> do YouTube (ex: <code>https://www.youtube.com/playlist?list=PL...</code>) ou colar <strong>múltiplos links de vídeos</strong> (um por linha). Se colar links, o sistema buscará os títulos oficiais no YouTube automaticamente!
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">URL da Playlist ou Links dos Vídeos (um por linha) *</label>
                        <textarea name="playlist_input" class="form-control font-monospace small" rows="7" placeholder="Exemplos:&#10;https://www.youtube.com/playlist?list=PLxxxxx&#10;&#10;OU múltiplos vídeos:&#10;https://www.youtube.com/watch?v=video1&#10;https://www.youtube.com/watch?v=video2 | Aula 2: Título Customizado&#10;https://youtu.be/video3" required></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Duração Padrão por Aula (Minutos)</label>
                            <input type="number" name="default_duration" class="form-control" value="15" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-lightning-charge me-1"></i> Gerar Aulas Automaticamente
                    </button>
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

<!-- Modal 4: Editar Módulo -->
<div class="modal fade" id="modalEditModule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditModule" action="" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i> Editar Módulo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título do Módulo *</label>
                        <input type="text" name="title" id="editModuleTitle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Descrição / Tópicos</label>
                        <textarea name="description" id="editModuleDescription" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ordem de Exibição</label>
                        <input type="number" name="order_index" id="editModuleOrder" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 5: Editar Aula -->
<div class="modal fade" id="modalEditLesson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditLesson" action="" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i> Editar Aula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título da Aula *</label>
                        <input type="text" name="title" id="editLessonTitle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ordem de Exibição</label>
                        <input type="number" name="order_index" id="editLessonOrder" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const _basePath = '<?= $basePath ?>';
function openAddLessonModal(moduleId, moduleTitle) {
    document.getElementById('formAddLesson').action = `${_basePath}/courses/modules/${moduleId}/lessons/add`;
    document.getElementById('lessonModuleName').value = moduleTitle;
    document.getElementById('unifiedLessonTitle').value = '';
    document.getElementById('unifiedContentTitle').value = '';
    document.getElementById('unifiedYoutubeUrl').value = '';
    document.getElementById('unifiedContentType').value = 'youtube_video';
    handleUnifiedContentType('youtube_video');
    new bootstrap.Modal(document.getElementById('modalAddLesson')).show();
}

function openImportPlaylistModal(moduleId, moduleTitle) {
    document.getElementById('formImportPlaylist').action = `${_basePath}/courses/modules/${moduleId}/playlist/import`;
    document.getElementById('playlistModuleName').value = moduleTitle;
    new bootstrap.Modal(document.getElementById('modalImportPlaylist')).show();
}

function syncContentTitle(val) {
    const contentTitleInput = document.getElementById('unifiedContentTitle');
    if (!contentTitleInput.dataset.userEdited) {
        contentTitleInput.placeholder = val ? val : 'Deixe em branco para usar o nome da aula';
    }
}

document.getElementById('unifiedContentTitle')?.addEventListener('input', function() {
    this.dataset.userEdited = this.value.trim() !== '' ? '1' : '';
});

function handleUnifiedContentType(type) {
    const groupYt = document.getElementById('unifiedGroupYoutube');
    const groupPdf = document.getElementById('unifiedGroupPdf');
    const groupTxt = document.getElementById('unifiedGroupText');
    const durRow = document.getElementById('unifiedDurationRow');

    groupYt.classList.add('d-none');
    groupPdf.classList.add('d-none');
    groupTxt.classList.add('d-none');
    durRow.classList.remove('d-none');

    if (type === 'youtube_video') {
        groupYt.classList.remove('d-none');
    } else if (type === 'pdf_document') {
        groupPdf.classList.remove('d-none');
    } else if (type === 'text_document') {
        groupTxt.classList.remove('d-none');
    } else if (type === 'none') {
        durRow.classList.add('d-none');
    }
}

function openEditModuleModal(moduleId, title, description, order) {
    document.getElementById('formEditModule').action = `${_basePath}/courses/modules/${moduleId}/update`;
    document.getElementById('editModuleTitle').value = title;
    document.getElementById('editModuleDescription').value = description;
    document.getElementById('editModuleOrder').value = order;
    new bootstrap.Modal(document.getElementById('modalEditModule')).show();
}

function openAddContentModal(lessonId, lessonTitle) {
    document.getElementById('formAddContent').action = `${_basePath}/courses/lessons/${lessonId}/contents/add`;
    document.getElementById('contentLessonName').value = lessonTitle;
    new bootstrap.Modal(document.getElementById('modalAddContent')).show();
}

function openEditLessonModal(lessonId, title, order) {
    document.getElementById('formEditLesson').action = `${_basePath}/courses/lessons/${lessonId}/update`;
    document.getElementById('editLessonTitle').value = title;
    document.getElementById('editLessonOrder').value = order;
    new bootstrap.Modal(document.getElementById('modalEditLesson')).show();
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
