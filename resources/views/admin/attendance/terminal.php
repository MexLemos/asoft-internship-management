<div class="row justify-content-center">
    <div class="col-lg-9 text-center">
        <!-- Terminal Card -->
        <div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-dark text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-danger px-3 py-1">
                        <i class="bi bi-broadcast me-1"></i> TERMINAL AO VIVO
                    </span>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-qr-code-scan me-2 text-warning"></i> Terminal de Ponto Físico - Asoftmedia
                    </h5>
                    <span id="live-clock" class="badge bg-secondary font-monospace px-3 py-1 fs-6">--:--:--</span>
                </div>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-2">Validação de Presença Física na Sede</h3>
                    <p class="text-muted">
                        Aponte a <strong>câmara fotográfica normal do telemóvel</strong> para o código QR dinâmico abaixo para registar a sua presença.
                    </p>
                </div>

                <!-- QR Container -->
                <div class="p-3 bg-white rounded-4 d-inline-block shadow-sm mb-3 border position-relative" style="max-width: 380px;">
                    <img id="terminal-qr-img" src="<?= $tokenData['qr_data_url'] ?>" alt="QR Code Dinâmico" class="img-fluid rounded-3" style="width: 320px; height: 320px; object-fit: contain;">
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                        <span class="font-monospace">PIN: <strong id="terminal-pin" class="text-primary"><?= $tokenData['short_code'] ?? substr($tokenData['token_hash'], 0, 6) ?></strong></span>
                        <span class="badge bg-light text-secondary border">TOTP 30s</span>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="mb-4" style="max-width: 420px; margin: 0 auto;">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="bi bi-arrow-repeat me-1"></i> Renovação automática em:</span>
                        <strong id="countdown-text" class="text-primary font-monospace"><?= $tokenData['seconds_remaining'] ?>s</strong>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div id="countdown-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?= min(100, ($tokenData['seconds_remaining'] / 30) * 100) ?>%"></div>
                    </div>
                </div>

                <!-- 3-Step Clear Instructions -->
                <div class="row g-3 text-start mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                                <strong class="small text-dark">Aponte a Câmara</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Abra a câmara do seu smartphone e enquadre o código QR no ecrã. Não precisa de instalar apps adicionais.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary rounded-circle px-2 py-1">2</span>
                                <strong class="small text-dark">Toque no Link</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Toque na notificação do link seguro que surge no visor do telemóvel para abrir a validação do terminal.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success rounded-circle px-2 py-1">3</span>
                                <strong class="small text-dark">Presença Gravada</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                O sistema confirma a sua presença física e regista automaticamente o horário de entrada ou saída.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Anti-Fraud Info Box -->
                <div class="alert alert-info border-0 d-flex align-items-center justify-content-center gap-3 py-3 rounded-3 text-start small mb-0">
                    <i class="bi bi-shield-check fs-2 text-info flex-shrink-0"></i>
                    <div>
                        <strong>Validação Criptográfica Anti-Fraude:</strong>
                        <div class="text-muted">
                            Cada código QR possui uma chave de uso único (nonce de 30 segundos). Fotos tiradas à distância ou códigos partilhados expiram de imediato e não permitem marcação.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                <?php if (empty($isSupervisor)): ?>
                    <a href="/admin/attendance/devices" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-phone me-1"></i> Dispositivos Vinculados
                    </a>
                <?php else: ?>
                    <a href="/supervisor/interns" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i> Lista de Estagiários
                    </a>
                <?php endif; ?>
                <span class="small text-muted">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Sede Asoftmedia &bull; Luanda, Angola
                </span>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleFullScreen()">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Ecrã Inteiro (Kiosk)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Relógio ao Vivo
    const clockEl = document.getElementById('live-clock');
    if (clockEl) {
        setInterval(() => {
            const now = new Date();
            clockEl.innerText = now.toTimeString().split(' ')[0];
        }, 1000);
    }

    // 2. Contagem Regressiva e Renovação do QR Code
    let secondsLeft = <?= (int)($tokenData['seconds_remaining'] ?? 30) ?>;
    const textEl = document.getElementById('countdown-text');
    const barEl = document.getElementById('countdown-bar');
    const imgEl = document.getElementById('terminal-qr-img');
    const pinEl = document.getElementById('terminal-pin');

    const updateCountdown = () => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            fetchNewToken();
        } else {
            if (textEl) textEl.innerText = secondsLeft + 's';
            if (barEl) {
                barEl.style.width = ((secondsLeft / 30) * 100) + '%';
                if (secondsLeft <= 5) {
                    barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-danger';
                } else if (secondsLeft <= 10) {
                    barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark';
                } else {
                    barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
                }
            }
        }
    };

    let timer = setInterval(updateCountdown, 1000);

    const fetchNewToken = async () => {
        clearInterval(timer);
        if (textEl) textEl.innerText = 'A renovar...';
        if (barEl) barEl.style.width = '100%';

        try {
            const res = await fetch('/api/attendance/token');
            const data = await res.json();
            if (data.success && data.qr_data_url) {
                if (imgEl) imgEl.src = data.qr_data_url;
                if (pinEl && data.short_code) pinEl.innerText = data.short_code;
                secondsLeft = data.seconds_remaining || 30;
                if (textEl) textEl.innerText = secondsLeft + 's';
                if (barEl) {
                    barEl.style.width = '100%';
                    barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
                }
            }
        } catch (err) {
            console.error('Falha ao atualizar token QR:', err);
            secondsLeft = 5;
        } finally {
            timer = setInterval(updateCountdown, 1000);
        }
    };
});

function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            alert(`Erro ao ativar ecrã inteiro: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}
</script>
