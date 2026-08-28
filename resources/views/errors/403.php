<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <div class="display-1 fw-bold text-danger mb-2">403</div>
                    <div class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6 rounded-pill">
                        <i class="bi bi-shield-lock-fill me-1"></i> Acesso Não Autorizado
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-2">Acesso Negado</h4>
                <p class="text-muted small mb-4">
                    <?= \App\Helpers\e($message ?? 'A sua conta de utilizador não possui privilégios suficientes para aceder a esta funcionalidade do sistema.') ?>
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-left me-1"></i> Voltar
                    </a>
                    <a href="/login" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i> Ir para o Meu Painel
                    </a>
                </div>
            </div>
            <div class="text-center mt-3 text-muted small">
                &copy; <?= date('Y') ?> Asoftmedia • Sistema Integrado de Gestão de Estágios
            </div>
        </div>
    </div>
</div>
