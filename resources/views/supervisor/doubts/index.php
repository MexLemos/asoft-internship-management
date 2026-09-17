<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dúvidas dos Meus Alunos</h4>
        <p class="text-muted small mb-0">Responda às dúvidas enviadas pelos estagiários que você supervisiona durante as aulas na Zona de Estudo.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/supervisor/doubts?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm">
            Todas (<?= count($doubts) ?>)
        </a>
        <a href="/supervisor/doubts?filter=pending" class="btn <?= $filter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' ?> btn-sm">
            <i class="bi bi-hourglass-split me-1"></i> Pendentes de Resposta
        </a>
        <a href="/supervisor/doubts?filter=answered" class="btn <?= $filter === 'answered' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">
            <i class="bi bi-check2-circle me-1"></i> Respondidas
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($doubts)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-chat-heart display-4 text-primary mb-3"></i>
                <h5>Nenhuma dúvida encontrada para o filtro selecionado.</h5>
                <p class="small">Quando os seus alunos enviarem dúvidas nas aulas, elas aparecerão aqui.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($doubts as $d): ?>
                    <?php $isAnswered = !empty($d['answer']); ?>
                    <div class="list-group-item p-4 <?= !$isAnswered ? 'bg-light border-start border-4 border-warning' : '' ?>">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                            <div>
                                <span class="badge bg-primary me-1"><?= \App\Helpers\e($d['course_title']) ?></span>
                                <span class="badge bg-light text-dark border"><?= \App\Helpers\e($d['content_title']) ?></span>
                            </div>
                            <div class="text-muted small">
                                Enviada em <?= \App\Helpers\format_date($d['created_at'], true) ?>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-person-circle text-primary fs-5"></i>
                            <strong><?= \App\Helpers\e($d['intern_name']) ?></strong>
                            <code class="small text-muted"><?= \App\Helpers\e($d['internship_code']) ?></code>
                        </div>

                        <div class="p-3 bg-white border rounded-3 text-dark small mb-3">
                            <strong>Pergunta do Estagiário:</strong><br>
                            <?= \App\Helpers\e($d['question']) ?>
                        </div>

                        <?php if ($isAnswered): ?>
                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 text-dark small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Resposta Oficial (por <?= \App\Helpers\e($d['answered_by_name'] ?? 'Orientador') ?>):</strong>
                                    <span class="text-muted" style="font-size: 11px;"><?= \App\Helpers\format_date($d['answered_at'], true) ?></span>
                                </div>
                                <?= \App\Helpers\e($d['answer']) ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-warning text-dark btn-sm fw-bold" onclick="openReplyModal(<?= $d['id'] ?>, '<?= \App\Helpers\e(addslashes($d['intern_name'])) ?>', '<?= \App\Helpers\e(addslashes($d['question'])) ?>')">
                                    <i class="bi bi-reply-fill me-1"></i> Responder ao Estagiário
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Responder Dúvida -->
<div class="modal fade" id="modalReplyDoubt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formReplyDoubt" action="" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">Responder Dúvida do Estagiário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Aluno:</label>
                        <input type="text" id="doubtInternName" class="form-control bg-light" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pergunta:</label>
                        <textarea id="doubtQuestionText" class="form-control bg-light small" rows="3" readonly></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sua Resposta *</label>
                        <textarea name="answer" class="form-control" rows="5" placeholder="Escreva a resposta e orientações técnicas..." required autofocus></textarea>
                        <div class="form-text small">O aluno receberá uma notificação em tempo real com esta resposta.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Enviar Resposta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReplyModal(doubtId, internName, questionText) {
    document.getElementById('formReplyDoubt').action = `/supervisor/doubts/${doubtId}/answer`;
    document.getElementById('doubtInternName').value = internName;
    document.getElementById('doubtQuestionText').value = questionText;
    new bootstrap.Modal(document.getElementById('modalReplyDoubt')).show();
}
</script>
