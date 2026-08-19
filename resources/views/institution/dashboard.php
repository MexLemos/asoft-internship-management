<div class="row g-3 mb-4">
    <!-- Institution Banner -->
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-secondary px-3 py-1 mb-2">Painel de Observador Académico</span>
                    <h3 class="fw-bold mb-1 text-dark"><?= \App\Helpers\e($institution['name'] ?? 'Instituição de Ensino') ?></h3>
                    <p class="text-muted small mb-0">NIF: <?= \App\Helpers\e($institution['nif'] ?? 'N/A') ?> • Acompanhamento em tempo real dos seus alunos estagiários na Asoftmedia</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-5 px-3 py-2"><?= $stats['total'] ?> Alunos Enrolados</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Summary Cards -->
    <div class="col-md-4">
        <div class="stat-card border-success">
            <div>
                <div class="text-success small fw-bold">🟢 Alunos com Desempenho Normal</div>
                <div class="fs-3 fw-bold text-success"><?= $stats['normal'] ?></div>
            </div>
            <div class="icon-box bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card border-warning">
            <div>
                <div class="text-warning small fw-bold">🟡 Alunos em Atenção</div>
                <div class="fs-3 fw-bold text-warning text-dark"><?= $stats['attention'] ?></div>
            </div>
            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card border-danger">
            <div>
                <div class="text-danger small fw-bold">🔴 Alunos em Risco</div>
                <div class="fs-3 fw-bold text-danger"><?= $stats['risk'] ?></div>
            </div>
            <div class="icon-box bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-shield-slash-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Enrolled Interns Table (Observer Read-Only Mode) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-people-fill me-2 text-primary"></i> Relação de Alunos em Estágio Curricular
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código / Aluno</th>
                        <th>Curso</th>
                        <th>Presenças</th>
                        <th>Tarefas Feitas</th>
                        <th>Média Geral</th>
                        <th>Situação de Risco</th>
                        <th class="text-end">Acompanhar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($interns)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum estagiário vinculado a esta instituição no momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($interns as $i): ?>
                            <tr>
                                <td>
                                    <strong><?= \App\Helpers\e($i['full_name']) ?></strong><br>
                                    <code><?= \App\Helpers\e($i['internship_code']) ?></code>
                                </td>
                                <td><?= \App\Helpers\e($i['course']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $i['days_present'] ?> dias presentes</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $i['tasks_completed'] ?> concluídas</span>
                                </td>
                                <td>
                                    <strong class="fs-6 text-success"><?= number_format((float)$i['overall_score'], 1) ?></strong> / 100
                                </td>
                                <td>
                                    <?php if ($i['risk_level'] === 'normal'): ?>
                                        <span class="badge badge-risk-normal">Normal</span>
                                    <?php elseif ($i['risk_level'] === 'attention'): ?>
                                        <span class="badge badge-risk-attention">Atenção</span>
                                    <?php else: ?>
                                        <span class="badge badge-risk-risk">Risco</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/institution/interns/<?= $i['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye-fill me-1"></i> Ver Evolução
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
