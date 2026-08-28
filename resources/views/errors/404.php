<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <div class="display-1 fw-bold text-primary mb-2">404</div>
                    <div class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-6 rounded-pill">
                        <i class="bi bi-compass me-1"></i> Página Não Encontrada
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-2">Oops! Página Inexistente</h4>
                <p class="text-muted small mb-4">
                    O recurso ou página que procura no endereço <code><?= \App\Helpers\e($path ?? $_SERVER['REQUEST_URI'] ?? '') ?></code> não existe, foi alterado ou está temporariamente indisponível.
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-left me-1"></i> Voltar à Página Anterior
                    </a>
                    <a href="/login" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-house-door me-1"></i> Página Inicial
                    </a>
                </div>
            </div>
            <div class="text-center mt-3 text-muted small">
                &copy; <?= date('Y') ?> Asoftmedia • Sistema Integrado de Gestão de Estágios
            </div>
        </div>
    </div>
</div>
