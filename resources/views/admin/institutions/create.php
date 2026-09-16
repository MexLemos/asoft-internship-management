<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-building-add me-2"></i> Cadastro de Nova Instituição Parceira
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/admin/institutions/store" method="POST">
                    <?= \App\Helpers\csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nome Oficial da Instituição *</label>
                            <input type="text" name="name" class="form-control" placeholder="ex: ISUTIC - Instituto Superior de Tecnologias..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo de Instituição *</label>
                            <select name="type" class="form-select" required>
                                <option value="universidade">Universidade / Ensino Superior</option>
                                <option value="instituto_medio" selected>Instituto Médio Técnico</option>
                                <option value="colegio">Colégio</option>
                                <option value="centro_formacao">Centro de Formação Profissional</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIF da Instituição</label>
                            <input type="text" name="nif" class="form-control" placeholder="ex: 5000123456">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Institucional *</label>
                            <input type="email" name="email" class="form-control" placeholder="contacto@instituicao.ao" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefone de Contacto</label>
                            <input type="text" name="phone" class="form-control" placeholder="+244 923 000 000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pessoa Responsável / Contacto</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="ex: Dr. Manuel Domingos">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cargo do Responsável</label>
                            <input type="text" name="contact_role" class="form-control" placeholder="ex: Coordenador de Estágios">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Endereço Completo</label>
                            <input type="text" name="address" class="form-control" placeholder="Rua, Bairro, Município">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info border-info border-opacity-25 bg-info bg-opacity-10 d-flex align-items-center mb-0">
                                <i class="bi bi-info-circle-fill fs-5 text-info me-3"></i>
                                <div class="small">
                                    <strong>Conta de Acesso Automática (Role 5 - Instituição):</strong><br>
                                    Ao gravar, será gerada automaticamente uma conta institucional com o <strong>Utilizador</strong> igual ao e-mail informado e a <strong>palavra-passe padrão</strong> <code>123EstagioAsoft</code>.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="/admin/institutions" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Gravar Instituição
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
