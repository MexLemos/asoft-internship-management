<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7 text-center">
            <div class="card shadow border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <div class="display-1 fw-bold text-warning mb-2">500</div>
                    <div class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 fs-6 rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Erro Interno do Sistema
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-2">Ocorreu um Erro Inesperado</h4>
                <p class="text-muted small mb-4">
                    <?= \App\Helpers\e($message ?? 'A equipa técnica foi notificada e o incidente foi registado nos ficheiros de auditoria do sistema. Por favor, tente novamente.') ?>
                </p>

                <?php if (!empty($isDebug) && !empty($exception)): ?>
                    <div class="text-start bg-light border rounded-3 p-3 mb-4 small font-monospace overflow-auto" style="max-height: 250px;">
                        <strong class="text-danger">DEBUG INFO:</strong> <?= \App\Helpers\e($exception->getMessage()) ?><br>
                        <span class="text-muted">Ficheiro:</span> <?= \App\Helpers\e($exception->getFile()) ?> (linha <?= $exception->getLine() ?>)<br>
                        <details class="mt-2">
                            <summary class="cursor-pointer text-primary">Ver Stack Trace Completo</summary>
                            <pre class="mt-2 text-dark" style="font-size: 11px; white-space: pre-wrap;"><?= \App\Helpers\e($exception->getTraceAsString()) ?></pre>
                        </details>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a href="javascript:location.reload()" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-clockwise me-1"></i> Atualizar Página
                    </a>
                    <a href="/login" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-house-door me-1"></i> Voltar à Página Inicial
                    </a>
                </div>
            </div>
            <div class="text-center mt-3 text-muted small">
                &copy; <?= date('Y') ?> Asoftmedia • Sistema Integrado de Gestão de Estágios
            </div>
        </div>
    </div>
</div>
