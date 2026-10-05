<div class="card auth-card shadow-lg border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <!-- Brand Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-2">
                <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Asoftmedia" style="width: 68px; height: 68px; object-fit: contain;">
            </div>
            <h4 class="fw-bold text-dark mb-0">ASOFTMEDIA</h4>
            <p class="text-muted small">Terminal de Presença • Leitura por Código QR</p>
        </div>

        <!-- Flash alerts -->
        <?php if ($err = \App\Helpers\flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?= \App\Helpers\e($err) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($suc = \App\Helpers\flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= \App\Helpers\e($suc) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Token Validation Banner -->
        <?php if (!$qrValidation['valid']): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 text-center mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning d-block mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Código QR Expirado ou Inválido</h6>
                <p class="small text-muted mb-3">
                    <?= \App\Helpers\e($qrValidation['message']) ?>
                </p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i> Por razões de segurança, os códigos renovam a cada 15 segundos. Por favor, aponte a câmara novamente para o monitor da sede.
                </p>
            </div>
        <?php else: ?>
            <div class="alert alert-success bg-success-subtle border-0 rounded-3 p-3 mb-4 text-center">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <i class="bi bi-shield-check-fill text-success fs-4"></i>
                    <h6 class="fw-bold text-success mb-0">Terminal Identificado</h6>
                </div>
                <div class="small text-secondary">
                    Presença física na sede da Asoftmedia confirmada via terminal digital.
                </div>
            </div>
        <?php endif; ?>

        <!-- Content by Auth State -->
        <?php if (!$isLoggedIn): ?>
            <!-- GUEST / NOT LOGGED IN -->
            <div class="text-center py-2">
                <div class="p-3 bg-light rounded-3 border mb-4 text-start">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-person-badge-fill text-primary fs-5 mt-1"></i>
                        <div>
                            <strong class="d-block small text-dark">Autenticação Necessária</strong>
                            <span class="text-muted small">
                                O código do terminal foi reconhecido. Para associar este registo ao seu perfil, faça login com a sua conta.
                            </span>
                        </div>
                    </div>
                </div>

                <a href="<?= $loginRedirectUrl ?>" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sessão para Registar
                </a>
                
                <p class="small text-muted mb-0">
                    Após o início de sessão, o seu ponto será registado com 1 toque.
                </p>
            </div>

        <?php elseif ($isIntern): ?>
            <!-- LOGGED IN AS INTERN -->
            <div class="intern-scan-box">
                <!-- Intern Card Mini -->
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                        <?= strtoupper(substr($intern['full_name'], 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <h6 class="fw-bold mb-0 text-dark text-truncate"><?= \App\Helpers\e($intern['full_name']) ?></h6>
                        <span class="badge bg-secondary-subtle text-secondary small">
                            <?= \App\Helpers\e($intern['internship_code']) ?>
                        </span>
                    </div>
                </div>

                <!-- Dynamic Status Box -->
                <div id="ajax-feedback" class="d-none alert small mb-3" role="alert"></div>

                <?php if ($actionType === 'completed'): ?>
                    <div class="card border-0 bg-success-subtle rounded-3 p-4 text-center mb-3">
                        <i class="bi bi-check-circle-fill text-success display-4 mb-2"></i>
                        <h5 class="fw-bold text-success mb-1">Presença Completa Hoje!</h5>
                        <p class="small text-secondary mb-3">
                            Você já registou a entrada e a saída no dia de hoje.
                        </p>
                        <div class="d-flex justify-content-around bg-white p-2 rounded-2 border small">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Entrada</span>
                                <strong><?= date('H:i', strtotime($todayRecord['check_in'])) ?></strong>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Saída</span>
                                <strong><?= date('H:i', strtotime($todayRecord['check_out'])) ?></strong>
                            </div>
                        </div>
                    </div>

                    <a href="/intern/attendance" class="btn btn-outline-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-calendar3 me-1"></i> Ver Meu Histórico de Ponto
                    </a>

                <?php elseif ($qrValidation['valid']): ?>
                    <!-- Ready to Check In or Check Out -->
                    <form id="attendance-scan-form" action="/attendance/scan/confirm" method="POST">
                        <?= \App\Helpers\csrf_field() ?>
                        <input type="hidden" name="token" value="<?= \App\Helpers\e($token) ?>">
                        <input type="hidden" name="latitude" id="input-lat" value="0">
                        <input type="hidden" name="longitude" id="input-lng" value="0">
                        <input type="hidden" name="accuracy" id="input-acc" value="">
                        <input type="hidden" name="device_uuid" id="input-device-uuid" value="">

                        <?php if ($actionType === 'check_in'): ?>
                            <div class="text-center mb-4">
                                <span class="text-muted small d-block mb-1">Ação atual:</span>
                                <h5 class="fw-bold text-dark">Registar Entrada no Estágio</h5>
                                <p class="text-muted small mb-0">Pressione o botão abaixo para confirmar a sua chegada à Asoftmedia.</p>
                            </div>

                            <button type="button" id="btn-submit-action" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Confirmar Entrada (Check-In)
                            </button>
                        <?php else: ?>
                            <div class="text-center mb-4">
                                <span class="text-muted small d-block mb-1">Entrada registada hoje às <?= date('H:i', strtotime($todayRecord['check_in'])) ?></span>
                                <h5 class="fw-bold text-dark">Registar Saída do Estágio</h5>
                                <p class="text-muted small mb-0">Pressione o botão abaixo para concluir o seu dia de estágio.</p>
                            </div>

                            <button type="button" id="btn-submit-action" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-right me-2"></i> Confirmar Saída (Check-Out)
                            </button>
                        <?php endif; ?>
                    </form>

                    <div id="gps-hint" class="text-center mt-3 small text-muted" style="font-size: 0.78rem;">
                        <i class="bi bi-geo-alt me-1"></i> A sincronizar com o terminal da recepção...
                    </div>
                <?php else: ?>
                    <a href="/intern/attendance" class="btn btn-outline-secondary w-100 py-2">
                        <i class="bi bi-arrow-left me-1"></i> Voltar ao Meu Painel
                    </a>
                <?php endif; ?>
            </div>

        <?php elseif ($isStaff): ?>
            <!-- LOGGED IN AS SUPERVISOR OR ADMIN (Verification Mode) -->
            <div class="staff-test-box">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info text-dark px-2 py-1">Modo de Supervisão</span>
                        <strong class="small text-dark">Validação de Infraestrutura</strong>
                    </div>
                    <p class="small text-secondary mb-2">
                        Você leu este código com sucesso utilizando a sua câmara. Como <strong>supervisor/administrador</strong>, o registo de presença é exclusivo para estagiários, mas o teste confirma que o terminal está 100% funcional.
                    </p>
                    <?php if ($qrValidation['valid'] && !empty($qrValidation['token'])): ?>
                        <div class="bg-white p-2 rounded border small text-muted font-monospace" style="font-size: 0.75rem;">
                            <div>PIN do Terminal: <?= \App\Helpers\e($qrValidation['token']['pin_code'] ?? 'TOTP') ?></div>
                            <div>Expiração: <?= \App\Helpers\e($qrValidation['token']['expires_at'] ?? 'N/D') ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <a href="/supervisor/attendance/terminal" class="btn btn-primary fw-semibold">
                        <i class="bi bi-qr-code-scan me-1"></i> Ver Terminal de Ponto
                    </a>
                    <a href="/supervisor/dashboard" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Ir para o Meu Painel
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-info small text-center">
                Autenticado como utilizador institucional. O registo de ponto é exclusivo para estagiários.
            </div>
            <a href="/" class="btn btn-outline-secondary btn-sm w-100">Voltar ao Painel</a>
        <?php endif; ?>

        <div class="text-center mt-4 pt-2 border-top">
            <span class="small text-muted" style="font-size: 0.75rem;">
                &copy; <?= date('Y') ?> Asoftmedia • Sistema de Gestão de Estágios
            </span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSubmit = document.getElementById('btn-submit-action');
    const form = document.getElementById('attendance-scan-form');
    const feedback = document.getElementById('ajax-feedback');
    const inputLat = document.getElementById('input-lat');
    const inputLng = document.getElementById('input-lng');
    const inputAcc = document.getElementById('input-acc');
    const inputUuid = document.getElementById('input-device-uuid');
    const gpsHint = document.getElementById('gps-hint');

    // Recupera UUID do dispositivo ou gera
    let deviceUuid = localStorage.getItem('as_intern_device_uuid');
    if (!deviceUuid) {
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
            deviceUuid = crypto.randomUUID();
        } else {
            deviceUuid = 'dev-' + Math.random().toString(36).substring(2, 10) + '-' + Date.now().toString(36);
        }
        localStorage.setItem('as_intern_device_uuid', deviceUuid);
    }
    if (inputUuid) {
        inputUuid.value = deviceUuid;
    }

    // Tentar obter geolocalização de fundo silenciosa para prova híbrida
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (inputLat) inputLat.value = pos.coords.latitude;
                if (inputLng) inputLng.value = pos.coords.longitude;
                if (inputAcc) inputAcc.value = pos.coords.accuracy;
                if (gpsHint) gpsHint.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> GPS e Terminal sincronizados.';
            },
            (err) => {
                // Se falhar GPS, não impede o check via terminal QR Code
                if (gpsHint) gpsHint.innerHTML = '<i class="bi bi-shield-check text-success me-1"></i> Validação por presença física no terminal.';
            },
            { timeout: 5000, maximumAge: 60000, enableHighAccuracy: true }
        );
    }

    if (btnSubmit && form) {
        btnSubmit.addEventListener('click', async function() {
            btnSubmit.disabled = true;
            const originalHtml = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> A validar presença...';

            const formData = new FormData(form);
            const payload = {};
            formData.forEach((value, key) => { payload[key] = value; });

            try {
                const response = await fetch('/attendance/scan/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    feedback.className = 'alert alert-success small mb-3';
                    feedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> <strong>Sucesso!</strong> ' + data.message;
                    feedback.classList.remove('d-none');
                    btnSubmit.classList.replace('btn-success', 'btn-primary');
                    btnSubmit.innerHTML = '<i class="bi bi-check2-all me-1"></i> Presença Registada!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1800);
                } else {
                    feedback.className = 'alert alert-danger small mb-3';
                    feedback.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> ' + (data.message || 'Erro ao registar presença.');
                    feedback.classList.remove('d-none');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalHtml;
                }
            } catch (err) {
                // Fallback para envio tradicional de formulário
                form.submit();
            }
        });
    }
});
</script>
