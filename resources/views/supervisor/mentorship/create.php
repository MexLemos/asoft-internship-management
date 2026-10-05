<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/supervisor/mentorship" class="text-decoration-none">Mentorias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nova Sessão 1-on-1</li>
        </ol>
    </nav>
    <h4 class="fw-bold mb-1">Registar Sessão de Mentoria & Orientação</h4>
    <p class="text-muted small mb-0">
        Registe uma reunião 1-on-1, acompanhamento de desenvolvimento ou orientação técnica com qualquer estagiário.
    </p>
</div>

<div class="row">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="/supervisor/mentorship/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="mb-4">
                        <label for="intern_id" class="form-label fw-semibold">
                            Estagiário <span class="text-danger">*</span>
                        </label>
                        <select name="intern_id" id="intern_id" class="form-select form-select-lg" required>
                            <option value="">-- Selecione o Estagiário --</option>
                            <?php foreach ($interns as $intern): ?>
                                <option value="<?= $intern['id'] ?>" <?= $preselectedInternId === (int)$intern['id'] ? 'selected' : '' ?>>
                                    <?= \App\Helpers\e($intern['full_name']) ?> — <?= \App\Helpers\e($intern['internship_code']) ?> (<?= \App\Helpers\e($intern['course']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">
                            Acesso universal: Você pode orientar e avaliar qualquer estagiário da organização.
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="session_date" class="form-label fw-semibold">
                                Data e Hora da Sessão <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" id="session_date" name="session_date" 
                                   value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="session_type" class="form-label fw-semibold">
                                Tipo de Acompanhamento <span class="text-danger">*</span>
                            </label>
                            <select name="session_type" id="session_type" class="form-select" required>
                                <?php foreach ($types as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $key === '1_on_1' ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($lbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold">
                            Título / Assunto da Reunião <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="ex: Alinhamento de Metas de Sprint e Feedback de Comunicação" required>
                    </div>

                    <!-- Rating Card / Selector -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-semibold d-block mb-1">
                            Avaliação de Desempenho desta Sessão (1 a 5)
                        </label>
                        <p class="text-muted small mb-2">
                            A nota dada soma-se automaticamente à média cumulativa do estagiário no componente de Comportamento & Acompanhamento:
                        </p>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_none" value="" checked>
                                <label class="form-check-label small" for="rating_none">Sem Nota (informativo)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_1" value="1">
                                <label class="form-check-label small text-danger fw-semibold" for="rating_1">★ 1 - Insuficiente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_2" value="2">
                                <label class="form-check-label small text-warning fw-semibold" for="rating_2">★ 2 - A Melhorar</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_3" value="3">
                                <label class="form-check-label small text-primary fw-semibold" for="rating_3">★ 3 - Bom</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_4" value="4">
                                <label class="form-check-label small text-info fw-semibold" for="rating_4">★ 4 - Muito Bom</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" id="rating_5" value="5">
                                <label class="form-check-label small text-success fw-semibold" for="rating_5">★ 5 - Excelente</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="summary" class="form-label fw-semibold">
                            Resumo Geral da Sessão & Observações do Orientador <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="summary" name="summary" rows="4" 
                                  placeholder="Descreva os pontos principais abordados, comportamento, engajamento e percepção geral..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="topics_discussed" class="form-label fw-semibold">
                            Tópicos Técnicos ou Comportamentais Abordados <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <textarea class="form-control" id="topics_discussed" name="topics_discussed" rows="3" 
                                  placeholder="ex: Arquitetura MVC, testes unitários, gestão de tempo, proatividade nas reuniões diárias..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="action_items" class="form-label fw-semibold">
                            Metas Acordadas & Próximos Passos (Action Items) <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <textarea class="form-control" id="action_items" name="action_items" rows="3" 
                                  placeholder="ex: 1. Concluir o módulo de Git até sexta-feira&#10;2. Solicitar code review antes do merge&#10;3. Chegar com 10 min de antecedência..."></textarea>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="is_private" name="is_private" value="1">
                        <label class="form-check-label small text-muted" for="is_private">
                            <i class="bi bi-lock-fill me-1"></i> <strong>Sessão Confidencial:</strong> Visível apenas para supervisores e administração (não exibida no portal do estagiário).
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="/supervisor/mentorship" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Gravar Sessão de Mentoria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card / Sidebar -->
    <div class="col-lg-3 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 bg-light mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-lightbulb-fill text-warning me-1"></i> Como funcionam as notas de mentoria?
                </h6>
                <p class="small text-muted mb-3">
                    Cada supervisor pode realizar sessões 1-on-1 com qualquer estagiário. Quando uma nota de 1 a 5 é atribuída:
                </p>
                <ul class="small text-muted ps-3 mb-3">
                    <li class="mb-2">A nota é computada na média aritmética ponderada das sessões de mentoria.</li>
                    <li class="mb-2">Combina-se com a avaliação de competências comportamentais.</li>
                    <li class="mb-2">Atualiza em tempo real a nota geral do estagiário na escala angolana de 0 a 20 valores.</li>
                </ul>
                <div class="alert alert-white bg-white border small text-secondary mb-0">
                    <i class="bi bi-shield-check text-success me-1"></i> Registos confidenciais mantêm anotações internas protegidas do estagiário.
                </div>
            </div>
        </div>
    </div>
</div>
