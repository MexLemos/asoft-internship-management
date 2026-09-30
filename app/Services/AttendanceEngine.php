<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceAttempt;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\InternDevice;
use App\Models\SystemSetting;
use function App\Helpers\calculate_haversine_distance;
use function App\Helpers\is_valid_coordinate;

class AttendanceEngine
{
    /**
     * Processa a marcação de entrada com filtro de precisão, validação de dispositivo e suporte a QR Code rotativo.
     */
    public function processCheckIn(
        int $internId,
        float $lat,
        float $lng,
        ?float $accuracy,
        string $ip,
        string $userAgent,
        ?string $deviceUuid = null,
        ?string $qrToken = null
    ): array {
        $intern = Intern::findById($internId);
        if (!$intern) {
            return ['success' => false, 'message' => 'Estagiário não encontrado.'];
        }

        // 1. Validar estado do estágio (apenas 'active' pode marcar presença regular)
        if (($intern['status'] ?? '') !== 'active') {
            return [
                'success' => false,
                'message' => 'O seu estágio encontra-se com estado \'' . Intern::getStatusLabel($intern['status'] ?? '') . '\'. A marcação de ponto está indisponível.'
            ];
        }

        // 2. Validação de Dispositivo (Device Binding anti-partilha de credenciais)
        if (!empty($deviceUuid)) {
            $deviceCheck = InternDevice::validateOrRegister($internId, $deviceUuid, null, $userAgent);
            if (!$deviceCheck['valid']) {
                AttendanceAttempt::log([
                    'intern_id' => $internId,
                    'type' => 'check_in',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'accuracy' => $accuracy,
                    'distance_meters' => 0,
                    'is_within_radius' => false,
                    'status' => 'blocked_untrusted_device',
                    'failure_reason' => $deviceCheck['message'],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device_uuid' => $deviceUuid,
                    'verification_method' => !empty($qrToken) ? 'dynamic_qr' : 'gps'
                ]);

                AuditLog::log('attendance_checkin_untrusted_device', 'attendance', $internId, null, [
                    'device_uuid' => $deviceUuid,
                    'ip' => $ip
                ], 'suspicious');

                return [
                    'success' => false,
                    'message' => $deviceCheck['message']
                ];
            }
        }

        // 3. Verificar calendário semanal do estagiário
        $currentDayOfWeek = (int)date('N');
        $scheduleDays = Intern::getScheduleDays($internId);
        $isScheduledToday = false;
        foreach ($scheduleDays as $sd) {
            if ((int)$sd['day_of_week'] === $currentDayOfWeek && (bool)$sd['is_active']) {
                $isScheduledToday = true;
                break;
            }
        }

        if (!$isScheduledToday) {
            AttendanceAttempt::log([
                'intern_id' => $internId,
                'type' => 'check_in',
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => $accuracy,
                'distance_meters' => 0,
                'is_within_radius' => false,
                'status' => 'blocked_time_invalid',
                'failure_reason' => 'Hoje não é um dia programado para o seu estágio.',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_uuid' => $deviceUuid
            ]);

            return [
                'success' => false,
                'message' => 'Hoje não é um dia de presença previsto no seu calendário de estágio.'
            ];
        }

        // 4. Mecanismo de Prova Física: Dynamic QR Code OU Geolocalização com Filtro de Precisão
        $verificationMethod = 'gps';
        $flaggedForReview = false;
        $flagReason = null;
        $companyLat = (float)SystemSetting::get('company_latitude', -8.83833);
        $companyLng = (float)SystemSetting::get('company_longitude', 13.23444);
        $radiusMeters = (int)SystemSetting::get('company_radius_meters', 100);
        $distanceMeters = calculate_haversine_distance($lat, $lng, $companyLat, $companyLng);

        if (!empty($qrToken)) {
            // Validação pelo terminal de QR Code rotativo na sede
            $qrService = new DynamicQrAttendanceService();
            $qrRes = $qrService->validateAndRedeem($qrToken, $internId);

            if (!$qrRes['valid']) {
                AttendanceAttempt::log([
                    'intern_id' => $internId,
                    'type' => 'check_in',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'accuracy' => $accuracy,
                    'distance_meters' => $distanceMeters,
                    'is_within_radius' => false,
                    'status' => 'blocked_invalid_qr',
                    'failure_reason' => $qrRes['message'],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device_uuid' => $deviceUuid,
                    'verification_method' => 'dynamic_qr'
                ]);

                return [
                    'success' => false,
                    'message' => $qrRes['message']
                ];
            }

            $verificationMethod = is_valid_coordinate($lat, $lng) ? 'hybrid_gps_qr' : 'dynamic_qr';
        } else {
            // Validação padrão por coordenadas GPS
            if (!is_valid_coordinate($lat, $lng)) {
                return ['success' => false, 'message' => 'Coordenadas de localização GPS inválidas ou não capturadas.'];
            }

            // Filtro de Precisão Mínima Exigida (Accuracy Threshold)
            $maxAccuracy = (float)SystemSetting::get('max_gps_accuracy_meters', 80.0);
            if ($accuracy !== null && $accuracy > $maxAccuracy) {
                $accRound = round($accuracy);
                AttendanceAttempt::log([
                    'intern_id' => $internId,
                    'type' => 'check_in',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'accuracy' => $accuracy,
                    'distance_meters' => $distanceMeters,
                    'is_within_radius' => false,
                    'status' => 'blocked_accuracy_insufficient',
                    'failure_reason' => "Precisão do sinal GPS insuficiente ({$accRound}m, limite aceitável: {$maxAccuracy}m).",
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device_uuid' => $deviceUuid
                ]);

                return [
                    'success' => false,
                    'message' => "Precisão do sinal GPS insuficiente ({$accRound}m). O limite máximo de incerteza técnica é de {$maxAccuracy}m. Aproxime-se de uma janela, ligue o Wi-Fi ou faça a leitura do QR Code no terminal da receção."
                ];
            }

            // Validação de proximidade geográfica com zona de incerteza (Buffer)
            $isWithinRadius = $distanceMeters <= $radiusMeters;

            if (!$isWithinRadius) {
                // Verificar se a incerteza do sinal cobre o raio da sede (Buffer de tolerância)
                if ($accuracy !== null && ($distanceMeters - $accuracy) <= $radiusMeters && $distanceMeters <= ($radiusMeters + 40)) {
                    $flaggedForReview = true;
                    $flagReason = "Localização no limiar do perímetro com incerteza de sinal (" . round($distanceMeters) . "m com precisão de " . round($accuracy) . "m).";
                } else {
                    $formattedDist = round($distanceMeters);
                    AttendanceAttempt::log([
                        'intern_id' => $internId,
                        'type' => 'check_in',
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'accuracy' => $accuracy,
                        'distance_meters' => $distanceMeters,
                        'is_within_radius' => false,
                        'status' => 'blocked_out_of_range',
                        'failure_reason' => "Fora da área autorizada ({$formattedDist}m de distância, limite: {$radiusMeters}m).",
                        'ip_address' => $ip,
                        'user_agent' => $userAgent,
                        'device_uuid' => $deviceUuid
                    ]);

                    AuditLog::log('attendance_checkin_blocked', 'attendance', $internId, null, [
                        'distance' => $distanceMeters,
                        'radius' => $radiusMeters,
                        'accuracy' => $accuracy
                    ], 'suspicious');

                    return [
                        'success' => false,
                        'message' => "Você não está dentro da área autorizada para marcar presença. Distância atual: {$formattedDist}m da Asoftmedia (limite permitido: {$radiusMeters}m).",
                        'distance' => round($distanceMeters, 1),
                        'allowed_radius' => $radiusMeters
                    ];
                }
            }
        }

        // 5. Calcular pontualidade / atraso
        $currentTime = date('H:i:s');
        $expectedStart = $intern['expected_start_time'] ?? '08:00:00';
        $tolerance = (int)($intern['tolerance_minutes'] ?? 15);
        $maxOnTime = date('H:i:s', strtotime("{$expectedStart} +{$tolerance} minutes"));

        $checkInStatus = ($currentTime > $maxOnTime) ? 'late' : 'on_time';

        // 6. Gravar Registo de Presença
        Attendance::recordCheckIn($internId, [
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
            'distance_meters' => $distanceMeters,
            'ip' => $ip,
            'device' => $userAgent,
            'status' => $checkInStatus,
            'verification_method' => $verificationMethod,
            'device_uuid' => $deviceUuid,
            'flagged_for_review' => $flaggedForReview,
            'flag_reason' => $flagReason
        ]);

        AttendanceAttempt::log([
            'intern_id' => $internId,
            'type' => 'check_in',
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'distance_meters' => $distanceMeters,
            'is_within_radius' => true,
            'status' => 'success',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_uuid' => $deviceUuid,
            'verification_method' => $verificationMethod
        ]);

        AuditLog::log('attendance_checkin_success', 'attendance', $internId, null, [
            'status' => $checkInStatus,
            'distance' => round($distanceMeters, 1),
            'method' => $verificationMethod,
            'flagged' => $flaggedForReview
        ], 'success');

        $statusMsg = ($checkInStatus === 'late') ? ' (Atrasado)' : ' (Pontual)';
        $extraMsg = $flaggedForReview ? ' [Aviso: Encaminhado para homologação de sinal]' : '';

        return [
            'success' => true,
            'message' => "Presença registada com sucesso às " . date('H:i') . "{$statusMsg}!{$extraMsg}",
            'time' => date('H:i'),
            'distance' => round($distanceMeters, 1),
            'status' => $checkInStatus,
            'method' => $verificationMethod,
            'flagged' => $flaggedForReview
        ];
    }

    /**
     * Processa a saída do estagiário.
     */
    public function processCheckOut(
        int $internId,
        float $lat,
        float $lng,
        ?float $accuracy,
        string $ip,
        string $userAgent,
        ?string $deviceUuid = null,
        ?string $qrToken = null
    ): array {
        $todayRecord = Attendance::getTodayForIntern($internId);
        if (!$todayRecord || empty($todayRecord['check_in_time'])) {
            return ['success' => false, 'message' => 'Não é possível registar saída sem ter marcado entrada hoje.'];
        }

        if (!empty($todayRecord['check_out_time'])) {
            return ['success' => false, 'message' => 'A saída já foi registada hoje às ' . substr($todayRecord['check_out_time'], 0, 5) . '.'];
        }

        // Validação de Dispositivo
        if (!empty($deviceUuid)) {
            $deviceCheck = InternDevice::validateOrRegister($internId, $deviceUuid, null, $userAgent);
            if (!$deviceCheck['valid']) {
                return ['success' => false, 'message' => $deviceCheck['message']];
            }
        }

        $companyLat = (float)SystemSetting::get('company_latitude', -8.83833);
        $companyLng = (float)SystemSetting::get('company_longitude', 13.23444);
        $radiusMeters = (int)SystemSetting::get('company_radius_meters', 100);
        $distanceMeters = calculate_haversine_distance($lat, $lng, $companyLat, $companyLng);

        $verificationMethod = 'gps';

        if (!empty($qrToken)) {
            $qrService = new DynamicQrAttendanceService();
            $qrRes = $qrService->validateAndRedeem($qrToken, $internId);
            if (!$qrRes['valid']) {
                return ['success' => false, 'message' => $qrRes['message']];
            }
            $verificationMethod = is_valid_coordinate($lat, $lng) ? 'hybrid_gps_qr' : 'dynamic_qr';
        } else {
            // Filtro de Precisão GPS
            $maxAccuracy = (float)SystemSetting::get('max_gps_accuracy_meters', 80.0);
            if ($accuracy !== null && $accuracy > $maxAccuracy) {
                return [
                    'success' => false,
                    'message' => "Precisão do sinal GPS insuficiente (" . round($accuracy) . "m) para registar saída."
                ];
            }

            $isWithinRadius = $distanceMeters <= $radiusMeters;
            if (!$isWithinRadius) {
                $formattedDist = round($distanceMeters);
                AttendanceAttempt::log([
                    'intern_id' => $internId,
                    'type' => 'check_out',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'accuracy' => $accuracy,
                    'distance_meters' => $distanceMeters,
                    'is_within_radius' => false,
                    'status' => 'blocked_out_of_range',
                    'failure_reason' => "Fora da empresa ({$formattedDist}m).",
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device_uuid' => $deviceUuid
                ]);

                return [
                    'success' => false,
                    'message' => "Você precisa de estar na Asoftmedia para registar a saída. Distância atual: {$formattedDist}m."
                ];
            }
        }

        Attendance::recordCheckOut($internId, [
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
            'distance_meters' => $distanceMeters,
            'ip' => $ip,
            'device' => $userAgent,
            'status' => 'normal'
        ]);

        AttendanceAttempt::log([
            'intern_id' => $internId,
            'type' => 'check_out',
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'distance_meters' => $distanceMeters,
            'is_within_radius' => true,
            'status' => 'success',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_uuid' => $deviceUuid,
            'verification_method' => $verificationMethod
        ]);

        AuditLog::log('attendance_checkout_success', 'attendance', $internId, null, [
            'distance' => round($distanceMeters, 1),
            'method' => $verificationMethod
        ], 'success');

        return [
            'success' => true,
            'message' => "Saída registada com sucesso às " . date('H:i') . "!",
            'time' => date('H:i')
        ];
    }
}
