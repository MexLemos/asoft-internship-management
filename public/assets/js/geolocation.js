/**
 * Asoftmedia Attendance Geolocation & Dynamic QR Client
 * Handles GPS accuracy thresholds, device binding UUIDs, and dynamic QR terminal scanning.
 */
class GeolocationAttendance {
    constructor() {
        this.btnCheckIn = document.getElementById('btn-check-in');
        this.btnCheckOut = document.getElementById('btn-check-out');
        this.statusBox = document.getElementById('geo-status-box');
        this.deviceUuidDisplay = document.getElementById('device-uuid-display');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Device UUID Binding
        this.deviceId = this.getOrCreateDeviceId();
        if (this.deviceUuidDisplay) {
            this.deviceUuidDisplay.innerText = this.deviceId;
            this.deviceUuidDisplay.title = this.deviceId;
        }

        // QR Modal Elements
        this.qrModalEl = document.getElementById('qrAttendanceModal');
        this.qrModalFeedback = document.getElementById('qr-modal-feedback');
        this.formManualQr = document.getElementById('form-manual-qr');
        this.inputQrToken = document.getElementById('input-qr-token');
        this.btnSubmitQrToken = document.getElementById('btn-submit-qr-token');
        this.btnToggleCamera = document.getElementById('btn-toggle-camera');
        this.cameraBtnText = document.getElementById('camera-btn-text');
        this.qrReaderContainer = document.getElementById('qr-reader-container');

        this.html5QrCode = null;
        this.isScanning = false;
        this.currentAction = 'check-in';

        this.init();
    }

    init() {
        if (this.btnCheckIn) {
            this.btnCheckIn.addEventListener('click', () => this.handleGpsAction('check-in'));
        }
        if (this.btnCheckOut) {
            this.btnCheckOut.addEventListener('click', () => this.handleGpsAction('check-out'));
        }

        // QR Modal listeners
        if (this.qrModalEl) {
            this.qrModalEl.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (button && button.getAttribute('data-action')) {
                    this.currentAction = button.getAttribute('data-action');
                }
                this.clearQrFeedback();
            });

            this.qrModalEl.addEventListener('hidden.bs.modal', () => {
                this.stopCameraScanner();
                this.clearQrFeedback();
            });
        }

        if (this.formManualQr) {
            this.formManualQr.addEventListener('submit', (e) => {
                e.preventDefault();
                const raw = this.inputQrToken.value.trim();
                const token = this.extractToken(raw);
                if (token) {
                    this.inputQrToken.value = token;
                    this.sendAttendancePayload(this.currentAction, {
                        qr_token: token,
                        device_uuid: this.deviceId
                    }, true);
                }
            });
        }

        if (this.btnToggleCamera) {
            this.btnToggleCamera.addEventListener('click', () => {
                if (this.isScanning) {
                    this.stopCameraScanner();
                } else {
                    this.startCameraScanner();
                }
            });
        }

        // Quick PIN for PC users
        const btnPinQuick = document.getElementById('btn-submit-pin-quick');
        const inputPinQuick = document.getElementById('terminal-pin-quick');
        const pinFeedback = document.getElementById('pin-quick-feedback');

        if (btnPinQuick && inputPinQuick) {
            btnPinQuick.addEventListener('click', () => {
                const pin = inputPinQuick.value.trim();
                const action = btnPinQuick.getAttribute('data-action') || 'check-in';
                if (!pin || pin.length < 4) {
                    if (pinFeedback) {
                        pinFeedback.style.display = 'block';
                        pinFeedback.className = 'text-danger small mt-2 fw-semibold';
                        pinFeedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Digite o PIN de 6 dígitos exibido no ecrã da recepção.';
                    }
                    return;
                }

                if (pinFeedback) {
                    pinFeedback.style.display = 'none';
                }

                btnPinQuick.disabled = true;
                const origHtml = btnPinQuick.innerHTML;
                btnPinQuick.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> A validar...';

                this.sendAttendancePayload(action, {
                    qr_token: pin,
                    device_uuid: this.deviceId
                }, false, btnPinQuick, origHtml);
            });

            inputPinQuick.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    btnPinQuick.click();
                }
            });
        }
    }

    /**
     * Recupera ou gera identificador único de dispositivo (UUID).
     */
    getOrCreateDeviceId() {
        let id = localStorage.getItem('as_intern_device_uuid');
        if (!id) {
            if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
                id = crypto.randomUUID();
            } else {
                id = 'dev-' + Math.random().toString(36).substring(2, 10) + '-' + Date.now().toString(36);
            }
            localStorage.setItem('as_intern_device_uuid', id);
        }
        return id;
    }

    /**
     * Extrai o hash do token caso seja lido um URL completo da câmara ou colado no input.
     */
    extractToken(input) {
        if (!input) return '';
        input = input.trim();
        if (input.includes('token=')) {
            try {
                const url = new URL(input);
                const t = url.searchParams.get('token');
                if (t) return t.trim();
            } catch (e) {
                const match = input.match(/[?&]token=([^&]+)/);
                if (match) return decodeURIComponent(match[1]).trim();
            }
        }
        return input;
    }

    /**
     * Validação e marcação via GPS com verificação de precisão e device_uuid.
     */
    handleGpsAction(actionType) {
        const btn = actionType === 'check-in' ? this.btnCheckIn : this.btnCheckOut;
        const originalText = btn.innerHTML;

        if (!navigator.geolocation) {
            this.showFeedback('danger', 'O seu navegador ou telemóvel não suporta geolocalização por GPS.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> A calibrar sinal GPS...`;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                const roundedAcc = Math.round(accuracy);

                // Feedback em tempo real sobre a qualidade do sinal
                if (accuracy > 100) {
                    this.showFeedback('warning', `Aviso: Sinal GPS com baixa precisão (${roundedAcc}m). Em computadores (PC) ou locais fechados, utilize o campo <strong>"Está no Computador (PC)?"</strong> com o PIN de 6 dígitos exibido no monitor da receção.`);
                }

                this.sendAttendancePayload(actionType, {
                    latitude,
                    longitude,
                    accuracy,
                    device_uuid: this.deviceId
                }, false, btn, originalText);
            },
            (error) => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                let errorMsg = 'Erro ao obter localização GPS.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Permissão de GPS recusada no navegador. Se estiver no PC, utilize o campo <strong>"Está no Computador (PC)?"</strong> com o PIN de 6 dígitos.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Sinal GPS indisponível no dispositivo. Se estiver no PC, utilize o campo <strong>"Está no Computador (PC)?"</strong> com o PIN de 6 dígitos.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Tempo limite esgotado ao calibrar GPS. Se estiver no PC, utilize o campo <strong>"Está no Computador (PC)?"</strong> com o PIN de 6 dígitos.';
                        break;
                }
                this.showFeedback('danger', errorMsg);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    /**
     * Envia o payload para a API de presença (Check-in ou Check-out).
     */
    async sendAttendancePayload(actionType, payload, isQrMode = false, btn = null, originalText = '') {
        const endpoint = actionType === 'check-in' 
            ? '/intern/attendance/check-in' 
            : '/intern/attendance/check-out';

        if (isQrMode) {
            this.setQrLoading(true);
        } else if (btn) {
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> A validar na Asoftmedia...`;
        }

        payload._csrf_token = this.csrfToken;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (isQrMode) {
                this.setQrLoading(false);
                if (data.success) {
                    this.stopCameraScanner();
                    this.showQrFeedback('success', data.message || 'Presença validada com sucesso via QR Code!');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    this.showQrFeedback('danger', data.message || 'Código QR expirado ou inválido.');
                }
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }

                if (data.success) {
                    this.showFeedback('success', data.message);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    this.showFeedback('danger', data.message || 'Falha ao validar presença.');
                }
            }
        } catch (err) {
            if (isQrMode) {
                this.setQrLoading(false);
                this.showQrFeedback('danger', 'Erro de ligação com o servidor da Asoftmedia.');
            } else if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                this.showFeedback('danger', 'Erro de ligação ao servidor. Verifique a sua ligação à internet.');
            }
        }
    }

    /**
     * Inicia o leitor de câmara Html5Qrcode.
     */
    startCameraScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            this.showQrFeedback('warning', 'O leitor de câmara direto não está disponível neste navegador. Por favor, cole o token no campo manual.');
            return;
        }

        this.qrReaderContainer.classList.remove('d-none');
        this.cameraBtnText.innerText = 'A ligar câmara...';
        this.html5QrCode = new Html5Qrcode("qr-reader");

        const config = { fps: 10, qrbox: { width: 220, height: 220 } };

        this.html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                // Ao detectar com sucesso o QR Code rotativo
                this.stopCameraScanner();
                const token = this.extractToken(decodedText);
                this.inputQrToken.value = token;
                this.sendAttendancePayload(this.currentAction, {
                    qr_token: token,
                    device_uuid: this.deviceId
                }, true);
            },
            (errorMsg) => {
                // Procura contínua sem erro fatal
            }
        ).then(() => {
            this.isScanning = true;
            this.cameraBtnText.innerText = 'Desligar Câmara';
            this.btnToggleCamera.classList.replace('btn-outline-primary', 'btn-outline-danger');
        }).catch((err) => {
            this.qrReaderContainer.classList.add('d-none');
            this.cameraBtnText.innerText = 'Abrir Câmara para Digitalizar';
            this.showQrFeedback('warning', 'Não foi possível aceder à câmara: ' + (err.message || 'Permissão negada.'));
        });
    }

    /**
     * Pára o leitor de câmara.
     */
    stopCameraScanner() {
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.stop().then(() => {
                this.html5QrCode.clear();
                this.isScanning = false;
                this.qrReaderContainer.classList.add('d-none');
                this.cameraBtnText.innerText = 'Abrir Câmara para Digitalizar';
                this.btnToggleCamera.classList.replace('btn-outline-danger', 'btn-outline-primary');
            }).catch(() => {
                this.isScanning = false;
                this.qrReaderContainer.classList.add('d-none');
            });
        }
    }

    setQrLoading(loading) {
        if (this.btnSubmitQrToken) {
            this.btnSubmitQrToken.disabled = loading;
            if (loading) {
                this.btnSubmitQrToken.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> A validar token...`;
            } else {
                this.btnSubmitQrToken.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Validar Presença com Token`;
            }
        }
    }

    showQrFeedback(type, message) {
        if (!this.qrModalFeedback) return;
        this.qrModalFeedback.className = `alert alert-${type} py-2 small mb-3 text-start`;
        this.qrModalFeedback.innerHTML = `<i class="bi bi-info-circle-fill me-2"></i> ${message}`;
        this.qrModalFeedback.classList.remove('d-none');
    }

    clearQrFeedback() {
        if (this.qrModalFeedback) {
            this.qrModalFeedback.classList.add('d-none');
            this.qrModalFeedback.innerHTML = '';
        }
    }

    showFeedback(type, message) {
        if (!this.statusBox) return;

        const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'warning' ? 'bi-exclamation-circle-fill' : 'bi-exclamation-triangle-fill');

        this.statusBox.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show d-flex align-items-center text-start" role="alert">
                <i class="bi ${icon} fs-4 me-3"></i>
                <div class="small">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new GeolocationAttendance();
});
