/**
 * Asoftmedia Attendance Geolocation Client
 */
class GeolocationAttendance {
    constructor() {
        this.btnCheckIn = document.getElementById('btn-check-in');
        this.btnCheckOut = document.getElementById('btn-check-out');
        this.statusBox = document.getElementById('geo-status-box');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        this.init();
    }

    init() {
        if (this.btnCheckIn) {
            this.btnCheckIn.addEventListener('click', () => this.handleAction('check-in'));
        }
        if (this.btnCheckOut) {
            this.btnCheckOut.addEventListener('click', () => this.handleAction('check-out'));
        }
    }

    handleAction(actionType) {
        const btn = actionType === 'check-in' ? this.btnCheckIn : this.btnCheckOut;
        const originalText = btn.innerHTML;

        if (!navigator.geolocation) {
            this.showFeedback('danger', 'O seu navegador não suporta geolocalização por GPS.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> A obter localização GPS...`;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                this.sendCoordinates(actionType, latitude, longitude, accuracy, btn, originalText);
            },
            (error) => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                let errorMsg = 'Erro ao obter localização.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Permissão de localização negada. Por favor, autorize o acesso ao GPS nas definições do seu navegador.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Sinal GPS indisponível no momento. Tente novamente.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Tempo limite excedido ao tentar obter sinal GPS.';
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

    async sendCoordinates(actionType, latitude, longitude, accuracy, btn, originalText) {
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> A validar na Asoftmedia...`;

        const endpoint = actionType === 'check-in' 
            ? '/intern/attendance/check-in' 
            : '/intern/attendance/check-out';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    latitude,
                    longitude,
                    accuracy,
                    _csrf_token: this.csrfToken
                })
            });

            const data = await response.json();

            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.success) {
                this.showFeedback('success', data.message);
                setTimeout(() => window.location.reload(), 2000);
            } else {
                this.showFeedback('danger', data.message || 'Falha ao validar presença.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalText;
            this.showFeedback('danger', 'Erro de comunicação com o servidor. Verifique a sua ligação à internet.');
        }
    }

    showFeedback(type, message) {
        if (!this.statusBox) return;

        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';

        this.statusBox.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi ${icon} fs-4 me-3"></i>
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new GeolocationAttendance();
});
