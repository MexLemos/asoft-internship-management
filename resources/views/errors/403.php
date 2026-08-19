<div class="text-center py-5">
    <div class="display-1 text-danger fw-bold"><i class="bi bi-shield-x"></i> 403</div>
    <h3 class="fw-bold text-dark mt-3">Acesso Negado</h3>
    <p class="text-muted fs-5"><?= \App\Helpers\e($message ?? 'Não possui permissões suficientes para aceder a esta página.') ?></p>
    <a href="/login" class="btn btn-primary px-4 py-2 mt-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar ao Painel
    </a>
</div>
