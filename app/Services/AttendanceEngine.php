<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceAttempt;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\SystemSetting;
use function App\Helpers\calculate_haversine_distance;
use function App\Helpers\is_valid_coordinate;

class AttendanceEngine
{
    public function processCheckIn(int $internId, float $lat, float $lng, ?float $accuracy, string $ip, string $userAgent): array
    {
        $intern = Intern::findById($internId);
        if (!$intern) {
            return ['success' => false, 'message' => 'Estagiário não encontrado.'];
        }

        // Validate coordinate bounds
        if (!is_valid_coordinate($lat, $lng)) {
            return ['success' => false, 'message' => 'Coordenadas de localização inválidas ou inexistentes.'];
        }

        // 1. Check if today is a scheduled day of week (1=Monday, ..., 7=Sunday)
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
                'user_agent' => $userAgent
            ]);

            return [
                'success' => false,
                'message' => 'Hoje não é um dia de presença previsto no seu calendário de estágio.'
            ];
        }

        // 2. Fetch company location and geofence radius
        $companyLat = (float)SystemSetting::get('company_latitude', -8.83833);
        $companyLng = (float)SystemSetting::get('company_longitude', 13.23444);
        $radiusMeters = (int)SystemSetting::get('company_radius_meters', 100);

        // 3. Calculate distance via Haversine
        $distanceMeters = calculate_haversine_distance($lat, $lng, $companyLat, $companyLng);

        $isWithinRadius = $distanceMeters <= $radiusMeters;

        if (!$isWithinRadius) {
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
                'user_agent' => $userAgent
            ]);

            AuditLog::log('attendance_checkin_blocked', 'attendance', $internId, null, [
                'distance' => $distanceMeters,
                'radius' => $radiusMeters
            ], 'suspicious');

            return [
                'success' => false,
                'message' => "Você não está dentro da área autorizada para marcar presença. Distância atual: {$formattedDist}m da Asoftmedia (limite permitido: {$radiusMeters}m).",
                'distance' => round($distanceMeters, 1),
                'allowed_radius' => $radiusMeters
            ];
        }

        // 4. Calculate if on time or late
        $currentTime = date('H:i:s');
        $expectedStart = $intern['expected_start_time'] ?? '08:00:00';
        $tolerance = (int)($intern['tolerance_minutes'] ?? 15);
        $maxOnTime = date('H:i:s', strtotime("{$expectedStart} +{$tolerance} minutes"));

        $checkInStatus = ($currentTime > $maxOnTime) ? 'late' : 'on_time';

        // 5. Record Check-In
        Attendance::recordCheckIn($internId, [
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
            'distance_meters' => $distanceMeters,
            'ip' => $ip,
            'device' => $userAgent,
            'status' => $checkInStatus
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
            'user_agent' => $userAgent
        ]);

        AuditLog::log('attendance_checkin_success', 'attendance', $internId, null, [
            'status' => $checkInStatus,
            'distance' => round($distanceMeters, 1)
        ], 'success');

        $statusMsg = ($checkInStatus === 'late') ? ' (Atrasado)' : ' (Pontual)';

        return [
            'success' => true,
            'message' => "Presença registrada com sucesso às " . date('H:i') . "{$statusMsg}! Distância da empresa: " . round($distanceMeters) . "m.",
            'time' => date('H:i'),
            'distance' => round($distanceMeters, 1),
            'status' => $checkInStatus
        ];
    }

    public function processCheckOut(int $internId, float $lat, float $lng, ?float $accuracy, string $ip, string $userAgent): array
    {
        $todayRecord = Attendance::getTodayForIntern($internId);
        if (!$todayRecord || empty($todayRecord['check_in_time'])) {
            return ['success' => false, 'message' => 'Não é possível registrar saída sem ter marcado entrada hoje.'];
        }

        if (!empty($todayRecord['check_out_time'])) {
            return ['success' => false, 'message' => 'A saída já foi registrada hoje às ' . substr($todayRecord['check_out_time'], 0, 5) . '.'];
        }

        $companyLat = (float)SystemSetting::get('company_latitude', -8.83833);
        $companyLng = (float)SystemSetting::get('company_longitude', 13.23444);
        $radiusMeters = (int)SystemSetting::get('company_radius_meters', 100);

        $distanceMeters = calculate_haversine_distance($lat, $lng, $companyLat, $companyLng);
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
                'user_agent' => $userAgent
            ]);

            return [
                'success' => false,
                'message' => "Você precisa estar na Asoftmedia para registrar a saída. Distância: {$formattedDist}m."
            ];
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
            'user_agent' => $userAgent
        ]);

        AuditLog::log('attendance_checkout_success', 'attendance', $internId, null, [
            'distance' => round($distanceMeters, 1)
        ], 'success');

        return [
            'success' => true,
            'message' => "Saída registrada com sucesso às " . date('H:i') . "!",
            'time' => date('H:i')
        ];
    }
}
