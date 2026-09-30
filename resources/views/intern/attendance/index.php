<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 text-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i> Controlo de Presença por Geolocalização
                </h5>
                <p class="small text-muted mb-0">Validação segura de coordenadas GPS e raio de presença.</p>
            </div>
            
            <div class="card-body p-4 text-center">
                <!-- Live Feedback Box -->
                <div id="geo-status-box" class="mb-4"></div>

                <!-- Today's Status Banner -->
                <div class="p-3 bg-light rounded-3 mb-4 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-dark small">Data de Hoje:</strong>
                        <span class="badge bg-secondary"><?= date('d/m/Y') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Entrada Registada:</span>
                        <strong class="text-success"><?= !empty($todayRecord['check_in_time']) ? substr($todayRecord['check_in_time'], 0, 5) : 'Não registada' ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Saída Registada:</span>
                        <strong class="text-primary"><?= !empty($todayRecord['check_out_time']) ? substr($todayRecord['check_out_time'], 0, 5) : 'Não registada' ?></strong>
                    </div>
                </div>

                <!-- Geolocation Action Buttons -->
                <div class="d-grid gap-3 mb-4">
                    <?php if (empty($todayRecord['check_in_time'])): ?>
                        <button id="btn-check-in" class="btn btn-primary btn-attendance py-2">
                            <i class="bi bi-geo-alt-fill me-2"></i> Marcar Entrada GPS
                        </button>
                        <button type="button" class="btn btn-outline-primary py-2" data-bs-toggle="modal" data-bs-target="#qrAttendanceModal" data-action="check-in">
                            <i class="bi bi-qr-code-scan me-2"></i> Marcar Entrada com QR Code do Terminal
                        </button>
                    <?php elseif (empty($todayRecord['check_out_time'])): ?>
                        <button id="btn-check-out" class="btn btn-warning btn-attendance text-dark py-2">
                            <i class="bi bi-geo-alt-fill me-2"></i> Marcar Saída GPS
                        </button>
                        <button type="button" class="btn btn-outline-warning text-dark py-2" data-bs-toggle="modal" data-bs-target="#qrAttendanceModal" data-action="check-out">
                            <i class="bi bi-qr-code-scan me-2"></i> Marcar Saída com QR Code do Terminal
                        </button>
                    <?php else: ?>
                        <div class="alert alert-success d-flex align-items-center justify-content-center py-3">
                            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                            <div>Presença e saída já foram completadas para o dia de hoje!</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-2 bg-light rounded-3 text-start small border">
                    <div class="d-flex justify-content-between text-muted mb-1">
                        <span><i class="bi bi-shield-check text-success me-1"></i> Raio máximo permitido:</span>
                        <strong><?= $radiusMeters ?> metros</strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-1">
                        <span><i class="bi bi-bullseye text-primary me-1"></i> Precisão mínima exigida:</span>
                        <strong><?= $maxAccuracy ?> metros</strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="bi bi-phone text-secondary me-1"></i> Dispositivo:</span>
                        <span id="device-uuid-display" class="font-monospace small text-truncate" style="max-width: 160px;">A detetar...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Stats Summary -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">Resumo de Frequência no Estágio</h6>
            </div>
            <div class="card-body p-3">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="fs-5 fw-bold text-success"><?= $stats['present_count'] ?></div>
                            <div class="text-muted small" style="font-size: 11px;">Presente</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="fs-5 fw-bold text-danger"><?= $stats['absent_count'] ?></div>
                            <div class="text-muted small" style="font-size: 11px;">Faltas</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="fs-5 fw-bold text-warning text-dark"><?= $stats['late_count'] ?></div>
                            <div class="text-muted small" style="font-size: 11px;">Atrasos</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="fs-5 fw-bold text-primary"><?= number_format((float)$stats['total_hours_worked'], 1) ?>h</div>
                            <div class="text-muted small" style="font-size: 11px;">Trabalhadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map & Geofence Perimeter -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-map me-2 text-primary"></i> Perímetro Geográfico da Asoftmedia
                </h6>
            </div>
            <div class="card-body p-0">
                <div id="geofence-map" style="height: 380px; width: 100%; border-radius: 0 0 0.75rem 0.75rem;"></div>
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark">Meu Histórico Recente de Presenças</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Horas</th>
                        <th>Distância GPS</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><strong><?= \App\Helpers\format_date($h['date']) ?></strong></td>
                            <td><?= substr($h['check_in_time'] ?? '--:--', 0, 5) ?></td>
                            <td><?= substr($h['check_out_time'] ?? '--:--', 0, 5) ?></td>
                            <td><?= $h['hours_worked'] ?>h</td>
                            <td><?= round((float)$h['check_in_distance_meters']) ?>m</td>
                            <td>
                                <span class="badge bg-success"><?= \App\Helpers\e($h['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Validação por QR Code Rotativo do Terminal -->
<div class="modal fade" id="qrAttendanceModal" tabindex="-1" aria-labelledby="qrAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="qrAttendanceModalLabel">
                    <i class="bi bi-qr-code-scan text-warning me-2"></i> Validação com Terminal de Ponto Físico
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="small text-muted mb-3">
                    Aponte a câmara para o código QR dinâmico exibido no ecrã da recepção da Asoftmedia ou digite/cole o token gerado.
                </p>

                <!-- Feedback Message Box -->
                <div id="qr-modal-feedback" class="mb-3 d-none"></div>

                <!-- Video / Scanner Container -->
                <div id="qr-reader-container" class="mb-3 rounded-3 overflow-hidden border bg-black d-none position-relative" style="min-height: 250px;">
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>

                <!-- Scanner Controls -->
                <div class="d-grid gap-2 mb-3">
                    <button type="button" id="btn-toggle-camera" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-camera-fill me-1"></i> <span id="camera-btn-text">Abrir Câmara para Digitalizar</span>
                    </button>
                </div>

                <div class="position-relative my-3">
                    <hr class="text-muted">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">ou validação manual</span>
                </div>

                <!-- Manual Token Input -->
                <form id="form-manual-qr">
                    <div class="mb-3 text-start">
                        <label for="input-qr-token" class="form-label small fw-bold text-dark">Código / Hash do QR Code:</label>
                        <input type="text" class="form-control font-monospace" id="input-qr-token" placeholder="Cole o token do terminal..." required autocomplete="off">
                    </div>
                    <button type="submit" id="btn-submit-qr-token" class="btn btn-success w-100 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Validar Presença com Token
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lat = <?= $companyLat ?>;
    const lng = <?= $companyLng ?>;
    const radius = <?= $radiusMeters ?>;

    const map = L.map('geofence-map').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("<b>Sede Asoftmedia</b><br>Luanda, Angola")
        .openPopup();

    L.circle([lat, lng], {
        color: '#0d6efd',
        fillColor: '#3b82f6',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);
});
</script>
