<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-person-plus-fill me-2"></i> Cadastro de Novo Estagiário
                </h5>
                <a href="/admin/interns" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                </a>
            </div>
            <div class="card-body p-4">
                <form action="/admin/interns/store" method="POST" id="internCreateForm">
                    <?= \App\Helpers\csrf_field() ?>

                    <!-- Seção 1: Dados Pessoais -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Dados Pessoais & Identificação</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome Completo *</label>
                            <input type="text" name="full_name" class="form-control" placeholder="ex: António Manuel da Silva" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome Social / Tratamento</label>
                            <input type="text" name="social_name" class="form-control" placeholder="ex: António Silva">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Número do Bilhete de Identidade (BI) *</label>
                            <input type="text" name="bi_number" class="form-control" placeholder="ex: 005432187LA042" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Data de Nascimento</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Género</label>
                            <select name="gender" class="form-select">
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Principal (Acesso ao Sistema) *</label>
                            <input type="email" name="email" class="form-control" placeholder="antonio.silva@exemplo.ao" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefone de Contacto</label>
                            <input type="text" name="phone" class="form-control" placeholder="+244 923 000 000">
                        </div>
                    </div>

                    <!-- Seção 2: Dados Académicos & Instituição -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Dados Académicos & Vinculação Institucional</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Instituição de Ensino de Origem *</label>
                            <select name="institution_id" class="form-select" required>
                                <option value="">Selecione a instituição...</option>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?= $inst['id'] ?>"><?= \App\Helpers\e($inst['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Curso de Formação *</label>
                            <select name="course" id="courseSelect" class="form-select" required onchange="toggleCustomCourse(this.value)">
                                <option value="Técnico de Informática" selected>Técnico de Informática</option>
                                <option value="Informática de Gestão">Informática de Gestão</option>
                                <option value="Gestão de Sistemas Informáticos">Gestão de Sistemas Informáticos</option>
                                <option value="Telecomunicações">Telecomunicações</option>
                                <option value="Engenharia Informática">Engenharia Informática</option>
                                <option value="Ciência da Computação">Ciência da Computação</option>
                                <option value="Administração/Gestão">Administração/Gestão</option>
                                <option value="Outro">Outro (Especificar)</option>
                            </select>
                        </div>

                        <!-- Campo condicional para Outro curso -->
                        <div class="col-12 d-none" id="customCourseContainer">
                            <label class="form-label small fw-semibold text-primary">Especifique o Curso *</label>
                            <input type="text" name="custom_course_name" id="customCourseInput" class="form-control" placeholder="ex: Redes e Segurança de Dados">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Classe / Nível de Formação *</label>
                            <select name="formation_level" class="form-select" required>
                                <option value="13ª" selected>13ª Classe</option>
                                <option value="12ª">12ª Classe</option>
                                <option value="10-11ª">10ª - 11ª Classe</option>
                                <option value="Médio Concluído">Ensino Médio Concluído</option>
                                <option value="Bacharel">Bacharelato</option>
                                <option value="Licenciado">Licenciatura</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Número de Processo / Estudante</label>
                            <input type="text" name="student_number" class="form-control" placeholder="ex: 2026/042">
                        </div>
                    </div>

                    <!-- Seção 3: Dados do Estágio na Asoftmedia -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Configuração do Estágio Curricular na Asoftmedia</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Área de Estágio *</label>
                            <select name="internship_area" class="form-select" required>
                                <option value="Geral" selected>1. Geral</option>
                                <option value="Desenvolvimento de Software">2. Desenvolvimento de Software</option>
                                <option value="Redes e Infraestrutura">3. Redes e Infraestrutura</option>
                                <option value="Gestão (RH, Admin, Contabilidade)">4. Gestão (RH, Admin, Contabilidade)</option>
                                <option value="Outro">5. Outro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Supervisor / Orientador Designado</label>
                            <select name="supervisor_id" class="form-select">
                                <option value="">Não atribuir de imediato</option>
                                <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= \App\Helpers\e($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Início do Estágio *</label>
                            <input type="date" name="start_date" id="startDateInput" class="form-control" value="<?= $defaultStartDate ?>" required onchange="recalculateEndDate(this.value)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Conclusão Prevista (Calculada Automaticamente) *</label>
                            <div class="input-group">
                                <input type="date" name="end_date" id="endDateDisplay" class="form-control bg-light fw-bold text-primary" value="<?= $defaultEndDate ?>" readonly>
                                <span class="input-group-text bg-light text-muted small"><i class="bi bi-calendar-check text-success me-1"></i> Sexta-feira</span>
                            </div>
                            <div class="form-text small text-muted">
                                Regra: Data de início + 3 meses, ajustado automaticamente para a próxima sexta-feira.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Horário Previsto de Entrada e Saída</label>
                            <div class="input-group">
                                <input type="time" name="expected_start_time" class="form-control" value="08:00" required>
                                <span class="input-group-text">às</span>
                                <input type="time" name="expected_end_time" class="form-control" value="12:00" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Carga Horária Total Prevista (Horas)</label>
                            <input type="number" name="total_required_hours" class="form-control" value="300" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold d-block">Dias da Semana com Presença Obrigatória</label>
                            <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="1" id="d1" checked>
                                    <label class="form-check-label small" for="d1">Segunda</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="2" id="d2" checked>
                                    <label class="form-check-label small" for="d2">Terça</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="3" id="d3">
                                    <label class="form-check-label small text-muted" for="d3">Quarta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="4" id="d4" checked>
                                    <label class="form-check-label small" for="d4">Quinta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="5" id="d5" checked>
                                    <label class="form-check-label small" for="d5">Sexta</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 border-top">
                        <a href="/admin/interns" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar & Criar Conta
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
    // Add 3 months
    d.setMonth(d.getMonth() + 3);

    // If not Friday (5 in JS: 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat)
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
