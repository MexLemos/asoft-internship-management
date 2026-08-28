<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-plus-circle me-2"></i> Criar Novo Curso para a Zona de Estudo
                </h5>
                <a href="/admin/courses" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                </a>
            </div>
            <div class="card-body p-4">
                <form action="/admin/courses/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título do Curso *</label>
                        <input type="text" name="title" class="form-control form-control-lg" placeholder="ex: Desenvolvimento Web Fullstack Moderno com PHP & MySQL" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Descrição do Curso & Objetivos Pedagógicos</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Descreva os tópicos abordados, competências a adquirir e pré-requisitos..."></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo / Trilha de Aprendizagem *</label>
                            <select name="is_mandatory" class="form-select" required>
                                <option value="1" selected>🔵 Curso Obrigatório (Necessário para Certificação)</option>
                                <option value="0">⚪ Curso Opcional / Eletivo</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Estado de Publicação *</label>
                            <select name="status" class="form-select" required>
                                <option value="published" selected>Publicado (Visível aos Alunos)</option>
                                <option value="draft">Rascunho (Oculto)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Ordem de Exibição</label>
                            <input type="number" name="order_index" class="form-control" value="1" min="1" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="/admin/courses" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-arrow-right-circle me-1"></i> Criar & Adicionar Aulas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
