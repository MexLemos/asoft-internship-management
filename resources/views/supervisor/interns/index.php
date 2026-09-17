<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Meus Estagiários</h4>
        <p class="text-muted small mb-0">Acompanhe o desempenho, frequência e evolução dos estagiários sob a sua orientação.</p>
    </div>
</div>

<?php if (empty($interns)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-5 text-center text-muted">
            <i class="bi bi-people display-4 text-primary mb-3"></i>
            <h5>Nenhum estagiário atribuído a si.</h5>
            <p class="small">Quando forem atribuídos estagiários, aparecerão aqui.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($interns as $intern): ?>
            <?php
                $statusColors = [
                    'active'    => 'success',
                    'completed' => 'primary',
                    'abandoned' => 'danger',
                    'pending'   => 'warning',
                ];
                $statusLabels = [
                    'active'    => 'Em Curso',
                    'completed' => 'Concluído',
                    'abandoned' => 'Abandonado',
                    'pending'   => 'Pendente',
                ];
                $color = $statusColors[$intern['status']] ?? 'secondary';
                $label = $statusLabels[$intern['status']] ?? ucfirst($intern['status']);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-person-fill text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold"><?= \App\Helpers\e($intern['full_name']) ?></h6>
                                <code class="small text-muted"><?= \App\Helpers\e($intern['internship_code']) ?></code>
                            </div>
                        </div>

                        <div class="mb-2 small">
                            <i class="bi bi-mortarboard text-muted me-1"></i>
                            <?= \App\Helpers\e($intern['course']) ?>
                        </div>
                        <div class="mb-2 small text-muted">
                            <i class="bi bi-building me-1"></i>
                            <?= \App\Helpers\e($intern['institution_name'] ?? 'N/D') ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                            <a href="/supervisor/interns/<?= $intern['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i> Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
