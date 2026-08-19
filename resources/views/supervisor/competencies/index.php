<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Avaliação de Competências</h4>
        <p class="text-muted small mb-0">Avalie o domínio técnico e as habilidades comportamentais (soft skills) dos seus estagiários.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Estagiário</th>
                        <th>Curso</th>
                        <th>Desempenho Geral</th>
                        <th>Nível de Risco</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interns as $i): ?>
                        <tr>
                            <td><code><?= \App\Helpers\e($i['internship_code']) ?></code></td>
                            <td><strong><?= \App\Helpers\e($i['full_name']) ?></strong></td>
                            <td><?= \App\Helpers\e($i['course']) ?></td>
                            <td><strong class="fs-6"><?= number_format((float)$i['overall_score'], 1) ?></strong> / 100</td>
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
                                <a href="/supervisor/competencies/evaluate/<?= $i['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Preencher Matriz
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
