<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestão de Cursos & Zona de Estudo</h4>
        <p class="text-muted small mb-0">Gerencie todos os cursos, módulos, videoaulas e documentos que aparecem na Zona de Estudo dos estagiários.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/doubts" class="btn btn-outline-warning text-dark">
            <i class="bi bi-question-circle-fill me-1"></i> Dúvidas dos Alunos
        </a>
        <a href="/admin/courses/create" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill me-1"></i> Novo Curso
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ordem</th>
                        <th>Título do Curso</th>
                        <th>Tipo / Trilha</th>
                        <th>Estrutura Pedagógica</th>
                        <th>Estado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum curso cadastrado no momento. Clique em "Novo Curso" para criar o primeiro.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border">#<?= $c['order_index'] ?></span>
                                </td>
                                <td>
                                    <strong class="text-dark fs-6"><?= \App\Helpers\e($c['title']) ?></strong><br>
                                    <span class="small text-muted"><?= \App\Helpers\truncate_text($c['description'] ?? '', 80) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($c['is_mandatory'])): ?>
                                        <span class="badge bg-primary px-2 py-1">🔵 Obrigatório</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">⚪ Opcional</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border">
                                        <?= $c['total_modules'] ?> Módulos
                                    </span>
                                    <span class="badge bg-light text-success border">
                                        <?= $c['total_lessons'] ?> Aulas
                                    </span>
                                    <span class="badge bg-light text-info border">
                                        <?= $c['total_contents'] ?> Conteúdos (Vídeos/PDF)
                                    </span>
                                </td>
                                <td>
                                    <?php if ($c['status'] === 'published'): ?>
                                        <span class="badge bg-success">Publicado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Rascunho</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="/admin/courses/<?= $c['id'] ?>/edit" class="btn btn-outline-primary btn-sm" title="Gerir Aulas e Conteúdos">
                                            <i class="bi bi-diagram-3-fill me-1"></i> Estrutura & Aulas
                                        </a>
                                        <form action="/admin/courses/<?= $c['id'] ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Tem a certeza de que deseja remover este curso e todos os seus módulos e aulas?')">
                                            <?= \App\Helpers\csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir Curso">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
