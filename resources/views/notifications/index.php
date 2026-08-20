<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Centro de Notificações</h4>
                <p class="text-muted small mb-0">Avisos importantes, atualizações de tarefas, avaliações e mensagens do sistema.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <form action="/notifications/mark-all-read" method="POST">
                    <?= \App\Helpers\csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-check2-all me-1"></i> Marcar todas como lidas
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-bell-slash display-4 text-muted mb-3"></i>
                        <h5>Nenhuma notificação no momento.</h5>
                        <p class="small text-muted mb-0">Você está em dia com todas as novidades e tarefas do estágio.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <?php $isUnread = empty($n['read_at']); ?>
                            <div class="list-group-item p-3 <?= $isUnread ? 'bg-light border-start border-4 border-primary' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded-circle <?= $isUnread ? 'bg-primary text-white' : 'bg-secondary text-white' ?>" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-bell-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 <?= $isUnread ? 'text-dark' : 'text-muted' ?>">
                                                <?= \App\Helpers\e($n['title']) ?>
                                                <?php if ($isUnread): ?>
                                                    <span class="badge bg-primary ms-1">Nova</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="small text-dark mb-1"><?= \App\Helpers\e($n['message']) ?></p>
                                            <span class="text-muted" style="font-size: 11px;"><?= \App\Helpers\format_date($n['created_at'], true) ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <?php if (!empty($n['action_url'])): ?>
                                            <a href="<?= \App\Helpers\e($n['action_url']) ?>" class="btn btn-outline-primary btn-sm">
                                                Abrir <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($isUnread): ?>
                                            <form action="/notifications/<?= $n['id'] ?>/read" method="POST" class="mb-0">
                                                <?= \App\Helpers\csrf_field() ?>
                                                <button type="submit" class="btn btn-link text-muted btn-sm p-0 text-decoration-none" style="font-size: 11px;">
                                                    Marcar como lida
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
