<div class="row justify-content-center">
    <div class="col-lg-8 text-center">
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

            <div class="card-body p-5">
                <div class="mb-3">
                    <h4 class="fw-bold text-dark mb-1">Aponte a câmara do telemóvel para validar a sua presença</h4>
                    <p class="text-muted small">
                        O código QR abaixo é dinâmico e renova-se automaticamente a cada <strong>30 segundos</strong> para impedir fraudes ou partilha de fotos.
                    </p>
                </div>

                <!-- QR Container -->
                <div class="p-3 bg-light rounded-4 d-inline-block shadow-sm mb-4 border" style="max-width: 380px;">
                    <img id="terminal-qr-img" src="<?= $tokenData['qr_data_url'] ?>" alt="QR Code Dinâmico" class="img-fluid rounded-3" style="width: 320px; height: 320px; object-fit: contain;">
                </div>

                <!-- Countdown Timer -->
                <div class="mb-4" style="max-width: 400px; margin: 0 auto;">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="bi bi-arrow-repeat me-1"></i> Renovação do código em:</span>
                        <strong id="countdown-text" class="text-primary font-monospace"><?= $tokenData['seconds_remaining'] ?>s</strong>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div id="countdown-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?= min(100, ($tokenData['seconds_remaining'] / 30) * 100) ?>%"></div>
                    </div>
                </div>

                <!-- Instructions Banner -->
                <div class="alert alert-info border-0 d-flex align-items-center justify-content-center gap-3 py-3 rounded-3 text-start small mb-0">
                    <i class="bi bi-shield-lock-fill fs-2 text-info"></i>
                    <div>
                        <strong>Protocolo Anti-Fraude Ativo:</strong>
                        <div class="text-muted">
                            Cada código possui um token de uso único (nonce) por estagiário. Leituras repetidas de fotos capturadas à distância são invalidadas pelo servidor.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                <a href="/admin/attendance/devices" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-phone me-1"></i> Dispositivos Autorizados
                </a>
                <span class="small text-muted">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Luanda, Angola &bull; Sede Asoftmedia
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
    setInterval(() => {
        const now = new Date();
        clockEl.innerText = now.toTimeString().split(' ')[0];
    }, 1000);

    // 2. Contagem Regressiva e Renovação do QR Code
    let secondsLeft = <?= (int)$tokenData['seconds_remaining'] ?>;
    const textEl = document.getElementById('countdown-text');
    const barEl = document.getElementById('countdown-bar');
    const imgEl = document.getElementById('terminal-qr-img');

    const updateCountdown = () => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            fetchNewToken();
        } else {
            textEl.innerText = secondsLeft + 's';
            barEl.style.width = ((secondsLeft / 30) * 100) + '%';
            if (secondsLeft <= 5) {
                barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-danger';
            } else if (secondsLeft <= 10) {
                barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark';
            } else {
                barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
            }
        }
    };

    let timer = setInterval(updateCountdown, 1000);

    const fetchNewToken = async () => {
        clearInterval(timer);
        textEl.innerText = 'A atualizar...';
        barEl.style.width = '100%';

        try {
            const res = await fetch('/api/attendance/token');
            const data = await res.json();
            if (data.success && data.qr_data_url) {
                imgEl.src = data.qr_data_url;
                secondsLeft = data.seconds_remaining || 30;
                textEl.innerText = secondsLeft + 's';
                barEl.style.width = '100%';
                barEl.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
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
