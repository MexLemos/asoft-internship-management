<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-sliders me-2"></i> Configurações Globais do Sistema & Regras de Negócio
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/admin/settings/update" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <!-- Seção 1: Geolocalização -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> 1. Geolocalização da Sede da Asoftmedia & Raio de Presença
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Latitude Oficial</label>
                            <input type="text" name="company_latitude" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('company_latitude', -8.83833)) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Longitude Oficial</label>
                            <input type="text" name="company_longitude" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('company_longitude', 13.23444)) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Raio Permitido de Presença (Metros)</label>
                            <input type="number" name="company_radius_meters" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('company_radius_meters', 100)) ?>" required>
                            <div class="form-text small">Distância máxima em metros para autorizar o registo de ponto por GPS.</div>
                        </div>
                    </div>

                    <!-- Seção 2: Pesos do Motor de Avaliação -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-calculator-fill text-primary me-1"></i> 2. Pesos do Motor de Avaliação Ponderada (Total deve ser 100%)
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Presença (%)</label>
                            <input type="number" name="weight_attendance" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_attendance', 20)) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Tarefas (%)</label>
                            <input type="number" name="weight_tasks" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_tasks', 30)) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Testes (%)</label>
                            <input type="number" name="weight_tests" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_tests', 20)) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Competências (%)</label>
                            <input type="number" name="weight_competencies" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_competencies', 15)) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Comportamento (%)</label>
                            <input type="number" name="weight_behavior" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_behavior', 10)) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 col-sm-4">
                            <label class="form-label small fw-semibold">Avaliação Final (%)</label>
                            <input type="number" name="weight_final_eval" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('weight_final_eval', 5)) ?>" min="0" max="100" required>
                        </div>
                    </div>

                    <!-- Seção 3: Regras para Conclusão & Certificado -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-patch-check-fill text-success me-1"></i> 3. Critérios Obrigatórios para Emissão de Certificado
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Presença Mínima Obrigatória (%)</label>
                            <input type="number" name="min_attendance_percentage" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('min_attendance_percentage', 80)) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nota Mínima de Aproveitamento (/100)</label>
                            <input type="number" name="min_passing_grade" class="form-control" value="<?= \App\Helpers\e(\App\Models\SystemSetting::get('min_passing_grade', 60)) ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
