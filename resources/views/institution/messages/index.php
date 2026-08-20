<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Mensagens com a Administração</h4>
        <p class="text-muted small mb-0">Canal direto e seguro de comunicação institucional com a Direcção da Asoftmedia.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNewConversation">
        <i class="bi bi-chat-plus-fill me-1"></i> Nova Mensagem
    </button>
</div>

<div class="row g-4">
    <!-- Conversations List (Left) -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">Minhas Conversas</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($conversations)): ?>
                        <div class="p-4 text-center text-muted small">
                            Nenhuma conversa iniciada. Clique no botão acima para enviar uma mensagem à administração.
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $c): ?>
                            <a href="/institution/messages?conversation=<?= $c['id'] ?>" class="list-group-item list-group-item-action p-3 <?= ($activeConversation && (int)$activeConversation['id'] === (int)$c['id']) ? 'active' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-truncate" style="max-width: 200px;"><?= \App\Helpers\e($c['subject']) ?></strong>
                                    <?php if ($c['unread_count'] > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= $c['unread_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-truncate <?= ($activeConversation && (int)$activeConversation['id'] === (int)$c['id']) ? 'text-white-50' : 'text-muted' ?>">
                                    <?= \App\Helpers\e($c['last_message'] ?? 'Conversa iniciada.') ?>
                                </div>
                                <div class="text-end" style="font-size: 10px;">
                                    <?= \App\Helpers\format_date($c['last_message_at'], true) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Chat Box (Right) -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 d-flex flex-column" style="min-height: 500px;">
            <?php if ($activeConversation): ?>
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark"><?= \App\Helpers\e($activeConversation['subject']) ?></h5>
                    <span class="small text-muted">Iniciada em <?= \App\Helpers\format_date($activeConversation['created_at'], true) ?></span>
                </div>

                <div class="card-body p-4 flex-grow-1 overflow-y-auto" style="max-height: 400px;">
                    <?php foreach ($messages as $msg): ?>
                        <?php $isMe = ((int)$msg['sender_id'] === (int)\App\Helpers\auth_user()['id']); ?>
                        <div class="d-flex <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                            <div class="p-3 rounded-3 shadow-xs <?= $isMe ? 'bg-primary text-white' : 'bg-light text-dark' ?>" style="max-width: 75%;">
                                <div class="fw-semibold small mb-1 <?= $isMe ? 'text-white-50' : 'text-primary' ?>">
                                    <?= \App\Helpers\e($msg['sender_name']) ?>
                                </div>
                                <div class="small" style="white-space: pre-wrap;"><?= \App\Helpers\e($msg['message']) ?></div>
                                <div class="text-end mt-1 <?= $isMe ? 'text-white-50' : 'text-muted' ?>" style="font-size: 10px;">
                                    <?= \App\Helpers\format_date($msg['created_at'], true) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-footer bg-white p-3 border-top">
                    <form action="/institution/messages/<?= $activeConversation['id'] ?>/send" method="POST">
                        <?= \App\Helpers\csrf_field() ?>
                        <div class="input-group">
                            <input type="text" name="message" class="form-control" placeholder="Escreva a sua mensagem para a administração..." required>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-send-fill me-1"></i> Enviar
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="card-body p-5 text-center d-flex flex-column align-items-center justify-content-center text-muted">
                    <i class="bi bi-chat-dots display-3 text-primary mb-3"></i>
                    <h5>Selecione uma conversa ou inicie uma nova mensagem.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal New Conversation -->
<div class="modal fade" id="modalNewConversation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/institution/messages/create" method="POST">
                <?= \App\Helpers\csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">Nova Mensagem para a Administração</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assunto da Mensagem *</label>
                        <input type="text" name="subject" class="form-control" placeholder="ex: Solicitação de relatório de desempenho dos alunos" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mensagem *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Escreva detalhadamente a sua solicitação..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Iniciar Conversa</button>
                </div>
            </form>
        </div>
    </div>
</div>
