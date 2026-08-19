<div class="text-center py-5">
    <div class="display-1 text-primary fw-bold"><i class="bi bi-compass"></i> 404</div>
    <h3 class="fw-bold text-dark mt-3">Página Não Encontrada</h3>
    <p class="text-muted fs-5">O endereço <code><?= \App\Helpers\e($path ?? '') ?></code> não existe ou foi movido.</p>
    <a href="/login" class="btn btn-primary px-4 py-2 mt-3">
        <i class="bi bi-house-door me-1"></i> Ir para a Página Inicial
    </a>
</div>
