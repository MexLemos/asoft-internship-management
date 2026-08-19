<div class="text-center py-5">
    <div class="display-1 text-warning fw-bold"><i class="bi bi-exclamation-octagon"></i> 500</div>
    <h3 class="fw-bold text-dark mt-3">Erro Interno do Servidor</h3>
    <p class="text-muted fs-5"><?= \App\Helpers\e($message ?? 'Ocorreu um erro inesperado ao processar o seu pedido.') ?></p>
    <a href="/login" class="btn btn-primary px-4 py-2 mt-3">
        <i class="bi bi-arrow-clockwise me-1"></i> Tentar Novamente
    </a>
</div>
