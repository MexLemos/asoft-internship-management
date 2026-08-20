<div class="row g-4 mb-4">
    <!-- Left Column: Official Standard Layout & Sandbox Preview -->
    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-3" id="portfolioTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard-pane" type="button">
                    <i class="bi bi-file-earmark-person-fill me-1"></i> Portfólio Oficial Asoftmedia
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom-pane" type="button">
                    <i class="bi bi-code-slash me-1"></i> Personalização (HTML/CSS/JS Sandbox)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="portfolioTabContent">
            <!-- Standard Template Tab -->
            <div class="tab-pane fade show active" id="standard-pane">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-person-badge-fill me-2"></i> Portfólio Profissional
                        </h5>
                        <span class="badge bg-success">Modelo Oficial Asoftmedia</span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Profile Summary -->
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 p-3 bg-light rounded-3 mb-4">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 64px; height: 64px;">
                                <i class="bi bi-person-fill fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1"><?= \App\Helpers\e($intern['full_name']) ?></h4>
                                <div class="text-muted small"><?= \App\Helpers\e($intern['course']) ?> • <?= \App\Helpers\e($intern['institution_name']) ?></div>
                                <div class="badge bg-primary mt-1">Área: <?= \App\Helpers\e($intern['internship_area'] ?? 'Geral') ?></div>
                            </div>
                        </div>

                        <!-- Competencies Developed -->
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Competências Técnicas & Comportamentais Validadas</h6>
                        <div class="row g-3 mb-4">
                            <?php foreach ($competencies as $c): ?>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-white shadow-xs">
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
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Projetos & Tarefas Práticas Entregues (<?= count($approvedTasks) ?>)</h6>
                        <div class="list-group list-group-flush mb-3">
                            <?php if (empty($approvedTasks)): ?>
                                <p class="text-muted small py-2">Nenhuma tarefa aprovada até o momento.</p>
                            <?php else: ?>
                                <?php foreach ($approvedTasks as $t): ?>
                                    <div class="list-group-item p-3 border rounded-3 bg-white mb-2 shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-dark"><?= \App\Helpers\e($t['title']) ?></strong>
                                            <span class="badge bg-success">Nota: <?= number_format((float)$t['score'], 1) ?></span>
                                        </div>
                                        <p class="small text-muted mb-2"><?= \App\Helpers\e($t['description']) ?></p>
                                        <?php if (!empty($t['supervisor_feedback'])): ?>
                                            <div class="small bg-light p-2 rounded text-dark">
                                                <strong>Parecer do Orientador:</strong> <?= \App\Helpers\e($t['supervisor_feedback']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Sandbox Editor Tab (Section 26) -->
            <div class="tab-pane fade" id="custom-pane">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-code-square me-2 text-warning"></i> Personalização em Sandbox
                        </h5>
                        <?php if ($isFrozen): ?>
                            <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i> Edição Congelada (Estágio Concluído)</span>
                        <?php else: ?>
                            <span class="badge bg-success">Edição Ativa</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-light border small mb-3">
                            <i class="bi bi-shield-check text-success me-1"></i> <strong>Ambiente Sandbox Seguro:</strong> O seu código HTML/CSS/JS personalizado é renderizado dentro de um container isolado sem acesso à sessão, cookies ou banco de dados.
                        </div>

                        <form action="/intern/portfolio/save-custom" method="POST">
                            <?= \App\Helpers\csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">HTML Personalizado</label>
                                <textarea name="portfolio_html" id="customHtml" class="form-control font-monospace small" rows="5" <?= $isFrozen ? 'readonly' : '' ?> placeholder="<div class='custom-header'><h1>Meu Portfólio</h1></div>"><?= htmlspecialchars($intern['portfolio_html'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">CSS Personalizado</label>
                                <textarea name="portfolio_css" id="customCss" class="form-control font-monospace small" rows="4" <?= $isFrozen ? 'readonly' : '' ?> placeholder=".custom-header { color: #0d6efd; }"><?= htmlspecialchars($intern['portfolio_css'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">JavaScript Personalizado</label>
                                <textarea name="portfolio_js" id="customJs" class="form-control font-monospace small" rows="3" <?= $isFrozen ? 'readonly' : '' ?> placeholder="console.log('Portfólio carregado');"><?= htmlspecialchars($intern['portfolio_js'] ?? '') ?></textarea>
                            </div>

                            <?php if (!$isFrozen): ?>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top mb-4">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateSandboxPreview()">
                                        <i class="bi bi-play-fill me-1"></i> Atualizar Pré-visualização
                                    </button>
                                    <button type="submit" class="btn btn-primary fw-bold">
                                        <i class="bi bi-save me-1"></i> Gravar Alterações
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Pré-visualização do Sandbox</h6>
                        <div class="border rounded-3 overflow-hidden" style="height: 350px;">
                            <iframe id="sandboxPreviewFrame" sandbox="allow-scripts" style="width: 100%; height: 100%; border: none;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: LinkedIn Sharing & Badges -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-linkedin text-primary me-2"></i> Divulgação no LinkedIn
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">Partilhe as suas competências e projetos desenvolvidos no estágio na sua rede profissional.</p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Texto Pré-formatado:</label>
                    <textarea id="linkedin-text" class="form-control form-control-sm" rows="6"><?= \App\Helpers\e($linkedInText) ?></textarea>
                </div>

                <button class="btn btn-outline-primary w-100 mb-2 btn-sm fw-bold" onclick="copyLinkedInText()">
                    <i class="bi bi-clipboard me-1"></i> Copiar Texto
                </button>
                <button class="btn btn-primary w-100 btn-sm fw-bold" onclick="openShareModal('linkedin')">
                    <i class="bi bi-share-fill me-1"></i> Partilhar Conquistas
                </button>
            </div>
        </div>

        <!-- Badges Earned -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-award-fill text-warning me-2"></i> Medalhas & Conquistas
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php if (empty($badges)): ?>
                        <p class="text-muted small p-2 mb-0">Complete tarefas e avaliações para desbloquear medalhas!</p>
                    <?php else: ?>
                        <?php foreach ($badges as $b): ?>
                            <span class="badge bg-warning text-dark p-2 fs-6">
                                <i class="bi <?= $b['icon'] ?> me-1"></i> <?= \App\Helpers\e($b['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Share Modal (Section 28) -->
<div class="modal fade" id="modalShare" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Partilhar Conquista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-trophy-fill display-3 text-warning mb-3"></i>
                <h5>Divulgue o seu sucesso nas redes sociais!</h5>
                <p class="small text-muted mb-4">Escolha a plataforma para abrir a publicação com a mensagem preparada.</p>

                <div class="d-grid gap-2">
                    <a href="https://www.linkedin.com/feed/" target="_blank" class="btn btn-primary py-2 fw-bold" onclick="registerShareEvent('linkedin')">
                        <i class="bi bi-linkedin me-2"></i> Partilhar no LinkedIn
                    </a>
                    <a href="https://www.facebook.com/" target="_blank" class="btn btn-outline-primary py-2 fw-bold" onclick="registerShareEvent('facebook')">
                        <i class="bi bi-facebook me-2"></i> Partilhar no Facebook
                    </a>
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
    alert("Texto copiado com sucesso para a área de transferência!");
}

function openShareModal() {
    new bootstrap.Modal(document.getElementById('modalShare')).show();
}

async function registerShareEvent(network) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        await fetch('/intern/portfolio/share', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ network: network, _csrf_token: csrfToken })
        });
    } catch(e) {}
}

function updateSandboxPreview() {
    const html = document.getElementById('customHtml')?.value || '';
    const css = document.getElementById('customCss')?.value || '';
    const js = document.getElementById('customJs')?.value || '';

    const frame = document.getElementById('sandboxPreviewFrame');
    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: sans-serif; padding: 20px; color: #333; }
                ${css}
            </style>
        </head>
        <body>
            ${html}
            <script>${js}<\/script>
        </body>
        </html>
    `);
    doc.close();
}

document.addEventListener('DOMContentLoaded', () => {
    updateSandboxPreview();
});
</script>
