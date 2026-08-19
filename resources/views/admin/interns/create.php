<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-person-plus-fill me-2"></i> Cadastro de Novo Estagiário
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/admin/interns/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <!-- Seção 1: Dados Pessoais -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Dados Pessoais</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome Completo *</label>
                            <input type="text" name="full_name" class="form-control" placeholder="ex: António da Silva" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email de Contacto *</label>
                            <input type="email" name="email" class="form-control" placeholder="ex: antonio.silva@exemplo.ao" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nº do Bilhete de Identidade (BI) *</label>
                            <input type="text" name="bi_number" class="form-control" placeholder="ex: 005423189LA042" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telefone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+244 923 000 000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Género</label>
                            <select name="gender" class="form-select">
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                                <option value="O">Outro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Seção 2: Dados Académicos & Estágio -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Dados Académicos e de Estágio</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Instituição de Ensino *</label>
                            <select name="institution_id" class="form-select" required>
                                <option value="">Selecione a Instituição...</option>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?= $inst['id'] ?>"><?= \App\Helpers\e($inst['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Curso *</label>
                            <input type="text" name="course" class="form-control" placeholder="ex: Engenharia Informática" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Área do Estágio *</label>
                            <input type="text" name="internship_area" class="form-control" value="Desenvolvimento de Software" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Supervisor da Asoftmedia</label>
                            <select name="supervisor_id" class="form-select">
                                <option value="">Atribuir posteriormente...</option>
                                <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= \App\Helpers\e($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Início *</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data de Conclusão Prevista *</label>
                            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 months')) ?>" required>
                        </div>
                    </div>

                    <!-- Seção 3: Horários e Dias de Estágio -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Dias e Horários Previstos de Presença</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold d-block mb-2">Dias de Estágio na Semana:</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="1" id="day1" checked>
                                    <label class="form-check-label" for="day1">Segunda-feira</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="2" id="day2" checked>
                                    <label class="form-check-label" for="day2">Terça-feira</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="3" id="day3">
                                    <label class="form-check-label" for="day3">Quarta-feira</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="4" id="day4" checked>
                                    <label class="form-check-label" for="day4">Quinta-feira</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="5" id="day5" checked>
                                    <label class="form-check-label" for="day5">Sexta-feira</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="6" id="day6">
                                    <label class="form-check-label" for="day6">Sábado</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Horário Entrada</label>
                            <input type="time" name="expected_start_time" class="form-control" value="08:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Horário Saída</label>
                            <input type="time" name="expected_end_time" class="form-control" value="12:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tolerância (Minutos)</label>
                            <input type="number" name="tolerance_minutes" class="form-control" value="15">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Carga Horária Total</label>
                            <input type="number" name="total_required_hours" class="form-control" value="300">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="/admin/interns" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Criar Estagiário e Ativar Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
