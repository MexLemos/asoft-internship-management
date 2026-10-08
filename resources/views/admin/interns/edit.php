<?php
$activeDaysMap = [];
if (!empty($intern['schedule_days'])) {
    foreach ($intern['schedule_days'] as $sd) {
        if (!empty($sd['is_active'])) {
            $activeDaysMap[(int)$sd['day_of_week']] = true;
        }
    }
} else {
    $activeDaysMap = [1 => true, 2 => true, 4 => true, 5 => true];
}

$standardCourses = [
    'Técnico de Informática',
    'Informática de Gestão',
    'Gestão de Sistemas Informáticos',
    'Telecomunicações',
    'Engenharia Informática',
    'Ciência da Computação',
    'Administração/Gestão'
];
$isCustomCourse = !in_array($intern['course'], $standardCourses, true) && $intern['course'] === 'Outro';
if (!in_array($intern['course'], $standardCourses, true) && !empty($intern['custom_course_name'])) {
    $isCustomCourse = true;
}
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-pencil-square me-2"></i> Editar Estagiário: <?= \App\Helpers\e($intern['full_name']) ?>
                    </h5>
                    <span class="small text-muted">Código de Estágio: <code><?= \App\Helpers\e($intern['internship_code']) ?></code></span>
                </div>
                <div class="d-flex gap-2">
                    <a href="/admin/interns/<?= $intern['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-eye me-1"></i> Ver Perfil
                    </a>
                    <a href="/admin/interns" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="/admin/interns/<?= $intern['id'] ?>/update" method="POST" id="internEditForm">
                    <?= \App\Helpers\csrf_field() ?>

                    <!-- Seção 1: Dados Pessoais -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Dados Pessoais & Identificação</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome Completo *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= \App\Helpers\e($intern['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome Social / Tratamento</label>
                            <input type="text" name="social_name" class="form-control" value="<?= \App\Helpers\e($intern['social_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Número do Bilhete de Identidade (BI) *</label>
                            <input type="text" name="bi_number" class="form-control" value="<?= \App\Helpers\e($intern['bi_number']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Data de Nascimento</label>
                            <input type="date" name="birth_date" class="form-control" value="<?= \App\Helpers\e($intern['birth_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Género</label>
                            <select name="gender" class="form-select">
                                <option value="M" <?= ($intern['gender'] ?? 'M') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= ($intern['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Principal (Acesso ao Sistema) *</label>
                            <input type="email" name="email" class="form-control" value="<?= \App\Helpers\e($intern['user_email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefone de Contacto</label>
                            <input type="text" name="phone" class="form-control" value="<?= \App\Helpers\e($intern['phone'] ?? '') ?>" placeholder="+244 923 000 000">
                        </div>
                    </div>

                    <!-- Seção 2: Dados Académicos & Instituição -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Dados Académicos & Vinculação Institucional</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Instituição de Ensino de Origem / Vínculo</label>
                            <select name="institution_id" class="form-select">
                                <option value="singular" class="fw-bold text-primary" <?= (empty($intern['institution_id']) || ($intern['institution_name'] ?? '') === 'Singular') ? 'selected' : '' ?>>
                                    👤 Singular (Candidatura Particular / Sem Instituição)
                                </option>
                                <optgroup label="Instituições de Ensino Registadas">
                                    <?php foreach ($institutions as $inst): ?>
                                        <option value="<?= $inst['id'] ?>" <?= (!empty($intern['institution_id']) && (int)$intern['institution_id'] === (int)$inst['id']) ? 'selected' : '' ?>>
                                            <?= \App\Helpers\e($inst['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <div class="form-text small">
                                Selecione "Singular" se o estagiário for independente ou se desejar desvincular da instituição anterior.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Curso de Formação *</label>
                            <select name="course" id="courseSelect" class="form-select" required onchange="toggleCustomCourse(this.value)">
                                <?php foreach ($standardCourses as $c): ?>
                                    <option value="<?= $c ?>" <?= ($intern['course'] === $c && !$isCustomCourse) ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                                <option value="Outro" <?= ($intern['course'] === 'Outro' || $isCustomCourse) ? 'selected' : '' ?>>Outro (Especificar)</option>
                            </select>
                        </div>

                        <!-- Campo condicional para Outro curso -->
                        <div class="col-12 <?= ($intern['course'] === 'Outro' || $isCustomCourse) ? '' : 'd-none' ?>" id="customCourseContainer">
                            <label class="form-label small fw-semibold text-primary">Especifique o Curso *</label>
                            <input type="text" name="custom_course_name" id="customCourseInput" class="form-control" value="<?= \App\Helpers\e($intern['custom_course_name'] ?? '') ?>" placeholder="ex: Redes e Segurança de Dados">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Classe / Nível de Formação *</label>
                            <select name="formation_level" class="form-select" required>
                                <option value="13ª" <?= ($intern['formation_level'] ?? '13ª') === '13ª' ? 'selected' : '' ?>>13ª Classe</option>
                                <option value="12ª" <?= ($intern['formation_level'] ?? '') === '12ª' ? 'selected' : '' ?>>12ª Classe</option>
                                <option value="10-11ª" <?= ($intern['formation_level'] ?? '') === '10-11ª' ? 'selected' : '' ?>>10ª - 11ª Classe</option>
                                <option value="Médio Concluído" <?= ($intern['formation_level'] ?? '') === 'Médio Concluído' ? 'selected' : '' ?>>Ensino Médio Concluído</option>
                                <option value="Bacharel" <?= ($intern['formation_level'] ?? '') === 'Bacharel' ? 'selected' : '' ?>>Bacharelato</option>
                                <option value="Licenciado" <?= ($intern['formation_level'] ?? '') === 'Licenciado' ? 'selected' : '' ?>>Licenciatura</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Número de Processo / Estudante</label>
                            <input type="text" name="student_number" class="form-control" value="<?= \App\Helpers\e($intern['student_number'] ?? '') ?>" placeholder="ex: 2026/042">
                        </div>
                    </div>

                    <!-- Seção 3: Dados do Estágio na Asoftmedia -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Configuração do Estágio Curricular na Asoftmedia</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Área de Estágio *</label>
                            <select name="internship_area" class="form-select" required>
                                <option value="Geral" <?= ($intern['internship_area'] ?? 'Geral') === 'Geral' ? 'selected' : '' ?>>1. Geral</option>
                                <option value="Desenvolvimento de Software" <?= ($intern['internship_area'] ?? '') === 'Desenvolvimento de Software' ? 'selected' : '' ?>>2. Desenvolvimento de Software</option>
                                <option value="Redes e Infraestrutura" <?= ($intern['internship_area'] ?? '') === 'Redes e Infraestrutura' ? 'selected' : '' ?>>3. Redes e Infraestrutura</option>
                                <option value="Gestão (RH, Admin, Contabilidade)" <?= ($intern['internship_area'] ?? '') === 'Gestão (RH, Admin, Contabilidade)' ? 'selected' : '' ?>>4. Gestão (RH, Admin, Contabilidade)</option>
                                <option value="Outro" <?= ($intern['internship_area'] ?? '') === 'Outro' ? 'selected' : '' ?>>5. Outro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Supervisor / Orientador Designado</label>
                            <select name="supervisor_id" class="form-select">
                                <option value="">Não atribuir de imediato</option>
                                <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>" <?= (int)($intern['supervisor_id'] ?? 0) === (int)$sup['id'] ? 'selected' : '' ?>>
                                        <?= \App\Helpers\e($sup['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Início do Estágio *</label>
                            <input type="date" name="start_date" id="startDateInput" class="form-control" value="<?= \App\Helpers\e($intern['start_date']) ?>" required onchange="recalculateEndDate(this.value)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Conclusão Prevista *</label>
                            <div class="input-group">
                                <input type="date" name="end_date" id="endDateDisplay" class="form-control bg-light fw-bold text-primary" value="<?= \App\Helpers\e($intern['end_date']) ?>" readonly>
                                <span class="input-group-text bg-light text-muted small"><i class="bi bi-calendar-check text-success me-1"></i> Sexta-feira</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Horário Previsto de Entrada e Saída</label>
                            <div class="input-group">
                                <input type="time" name="expected_start_time" class="form-control" value="<?= substr($intern['expected_start_time'] ?? '08:00', 0, 5) ?>" required>
                                <span class="input-group-text">às</span>
                                <input type="time" name="expected_end_time" class="form-control" value="<?= substr($intern['expected_end_time'] ?? '12:00', 0, 5) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Carga Horária Total Prevista (Horas)</label>
                            <input type="number" name="total_required_hours" class="form-control" value="<?= (int)($intern['total_required_hours'] ?? 300) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Estado do Estágio</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($intern['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                                <option value="completed" <?= ($intern['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluído</option>
                                <option value="suspended" <?= ($intern['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspenso</option>
                                <option value="cancelled" <?= ($intern['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold d-block">Dias da Semana com Presença Obrigatória</label>
                            <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="1" id="d1" <?= !empty($activeDaysMap[1]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="d1">Segunda</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="2" id="d2" <?= !empty($activeDaysMap[2]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="d2">Terça</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="3" id="d3" <?= !empty($activeDaysMap[3]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small text-muted" for="d3">Quarta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="4" id="d4" <?= !empty($activeDaysMap[4]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="d4">Quinta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="5" id="d5" <?= !empty($activeDaysMap[5]) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="d5">Sexta</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 border-top">
                        <a href="/admin/interns/<?= $intern['id'] ?>" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomCourse(val) {
    const container = document.getElementById('customCourseContainer');
    const input = document.getElementById('customCourseInput');
    if (val === 'Outro') {
        container.classList.remove('d-none');
        input.setAttribute('required', 'required');
    } else {
        container.classList.add('d-none');
        input.removeAttribute('required');
        input.value = '';
    }
}

function recalculateEndDate(startDateStr) {
    if (!startDateStr) return;
    const parts = startDateStr.split('-');
    if (parts.length !== 3) return;

    let d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    d.setMonth(d.getMonth() + 3);

    let currentDay = d.getDay();
    if (currentDay !== 5) {
        let diff = (5 - currentDay + 7) % 7;
        if (diff === 0) diff = 7;
        d.setDate(d.getDate() + diff);
    }

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    document.getElementById('endDateDisplay').value = `${year}-${month}-${day}`;
}
</script>
