<!-- Welcome Banner -->
<div class="card bg-primary text-white border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-primary px-3 py-1 mb-2 fw-semibold">Área do Estagiário</span>
            <h2 class="fw-bold mb-1">Olá, <?= \App\Helpers\e($intern['full_name']) ?>! 👋</h2>
            <p class="mb-0 text-white-50">Estágio em <?= \App\Helpers\e($intern['internship_area']) ?> • <?= \App\Helpers\e($intern['institution_name']) ?></p>
        </div>
        <div class="text-md-end">
            <a href="/intern/attendance" class="btn btn-warning btn-lg fw-bold shadow">
                <i class="bi bi-geo-alt-fill me-1 text-danger"></i> Marcar Presença GPS
            </a>
        </div>
    </div>
</div>

<!-- Progress & Stats Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Internship Hours Progress -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Horas Cumpridas</div>
                <div class="fs-4 fw-bold text-dark"><?= $hoursCompleted ?>h <span class="fs-6 text-muted fw-normal">/ <?= $totalHoursExpected ?>h</span></div>
                <div class="progress mt-2" style="height: 6px; width: 140px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progressPct ?>%"></div>
                </div>
            </div>
            <div class="icon-box bg-success bg-opacity-10 text-success">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <!-- Card 2: Attendance Days -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Presenças / Faltas</div>
                <div class="fs-4 fw-bold text-dark"><?= $attStats['present_count'] ?> <span class="fs-6 text-danger fw-normal">(<?= $attStats['absent_count'] ?> faltas)</span></div>
                <div class="small text-muted mt-1"><?= $attStats['late_count'] ?> atrasos</div>
            </div>
            <div class="icon-box bg-info bg-opacity-10 text-info">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
        </div>
    </div>

    <!-- Card 3: Tasks Done -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Tarefas Concluídas</div>
                <div class="fs-4 fw-bold text-dark"><?= count($completedTasks) ?> <span class="fs-6 text-muted fw-normal">/ <?= count($completedTasks) + count($pendingTasks) ?></span></div>
                <div class="small text-muted mt-1"><?= count($pendingTasks) ?> pendentes</div>
            </div>
            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-check2-all"></i>
            </div>
        </div>
    </div>

    <!-- Card 4: Overall Grade -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Nota Ponderada</div>
                <div class="fs-4 fw-bold text-success"><?= number_format((float)$intern['overall_score'], 1) ?> <span class="fs-6 text-muted fw-normal">/ 100</span></div>
                <div class="small text-success mt-1 fw-semibold">Aproveitamento Excelente</div>
            </div>
            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-trophy-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Tasks & Learning Tracks -->
<div class="row g-4 mb-4">
    <!-- Tasks Column -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-list-task me-2 text-primary"></i> Minhas Tarefas em Andamento
                </span>
                <a href="/intern/tasks" class="btn btn-outline-primary btn-sm">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($pendingTasks)): ?>
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            Todas as tarefas atribuídas foram concluídas com sucesso!
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($pendingTasks, 0, 4) as $t): ?>
                            <a href="/intern/tasks/<?= $t['id'] ?>" class="list-group-item list-group-item-action p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><?= \App\Helpers\e($t['title']) ?></strong>
                                    <span class="badge bg-<?= $t['color_badge'] ?>"><?= \App\Helpers\e($t['category_name']) ?></span>
                                </div>
                                <div class="text-muted small mb-2"><?= \App\Helpers\truncate_text($t['description'], 80) ?></div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-danger"><i class="bi bi-calendar-event me-1"></i> Prazo: <?= \App\Helpers\format_date($t['due_date']) ?></span>
                                    <span class="badge bg-light text-dark border"><?= $t['points'] ?> Pontos</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Academy Cursos -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-mortarboard-fill me-2 text-success"></i> Academia Asoftmedia (Zona de Estudo)
                </span>
                <a href="/intern/academy" class="btn btn-outline-success btn-sm">Aceder Cursos</a>
            </div>
            <div class="card-body p-3">
                <?php foreach ($courses as $c): ?>
                    <div class="p-3 border rounded-3 bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark"><?= \App\Helpers\e($c['title']) ?></strong>
                            <span class="badge bg-primary">Obrigatório</span>
                        </div>
                        <p class="small text-muted mb-2"><?= \App\Helpers\e($c['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><?= $c['total_modules'] ?> Módulos • <?= $c['total_lessons'] ?> Aulas</span>
                            <a href="/intern/academy/course/<?= $c['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-play-circle me-1"></i> Estudar Agora
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gamification Badges -->
<?php if (!empty($earnedBadges)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-warning text-dark">
                <i class="bi bi-star-fill text-warning me-2"></i> Minhas Conquistas & Medalhas Desbloqueadas (Gamificação)
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <?php foreach ($earnedBadges as $b): ?>
                    <div class="col-6 col-md-3">
                        <div class="badge-card">
                            <i class="bi <?= $b['icon'] ?> badge-icon"></i>
                            <div class="fw-bold text-dark small"><?= \App\Helpers\e($b['name']) ?></div>
                            <div class="text-muted" style="font-size: 11px;"><?= \App\Helpers\e($b['description']) ?></div>
                            <span class="badge bg-warning text-dark mt-2">+<?= $b['points_reward'] ?> pts</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
