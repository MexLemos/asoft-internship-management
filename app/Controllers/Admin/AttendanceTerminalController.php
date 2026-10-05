<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\InternDevice;
use App\Services\DynamicQrAttendanceService;

class AttendanceTerminalController extends Controller
{
    private DynamicQrAttendanceService $qrService;

    public function __construct()
    {
        $this->qrService = new DynamicQrAttendanceService();
    }

    /**
     * Ecrã de Terminal Kiosk para exibição do QR Code rotativo na sede.
     */
    public function terminal(Request $request): Response
    {
        $user = Session::get('user');
        $tokenData = $this->qrService->getCurrentTerminalToken($user ? (int)$user['id'] : null);

        $path = $request->getPath();
        $isSupervisor = str_starts_with($path, '/supervisor');
        $layout = $isSupervisor ? 'supervisor' : 'admin';

        return $this->render('admin.attendance.terminal', [
            'title' => 'Terminal de Presença Dinâmica - Asoftmedia',
            'tokenData' => $tokenData,
            'isSupervisor' => $isSupervisor
        ], $layout);
    }

    /**
     * API JSON para obter o QR Code ativo e contagem regressiva em tempo real.
     */
    public function tokenApi(Request $request): Response
    {
        $user = Session::get('user');
        $tokenData = $this->qrService->getCurrentTerminalToken($user ? (int)$user['id'] : null);

        return (new Response())->json([
            'success' => true,
            'token_hash' => $tokenData['token_hash'],
            'scan_url' => $tokenData['scan_url'] ?? '',
            'short_code' => $tokenData['short_code'] ?? '',
            'qr_data_url' => $tokenData['qr_data_url'],
            'seconds_remaining' => $tokenData['seconds_remaining'],
            'expires_at' => $tokenData['expires_at']
        ]);
    }

    /**
     * Gestão de dispositivos vinculados (Device Binding).
     */
    public function devices(Request $request): Response
    {
        $pdo = \App\Core\Database::getConnection();
        $devices = $pdo->query("
            SELECT d.*, i.full_name as intern_name, i.internship_code, i.course
            FROM intern_devices d
            INNER JOIN interns i ON i.id = d.intern_id
            ORDER BY d.last_used_at DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin.attendance.devices', [
            'title' => 'Dispositivos Vinculados & Anti-Fraude - Asoftmedia',
            'devices' => $devices
        ], 'admin');
    }

    /**
     * Autoriza / Homologa um dispositivo.
     */
    public function trustDevice(Request $request, string $id): Response
    {
        $deviceId = (int)$id;
        InternDevice::trustDevice($deviceId);
        AuditLog::log('device_trusted', 'attendance', $deviceId, null, null, 'success');

        Session::flash('success', 'Dispositivo homologado com sucesso!');
        return $this->redirect('/admin/attendance/devices');
    }

    /**
     * Bloqueia um dispositivo suspeito.
     */
    public function blockDevice(Request $request, string $id): Response
    {
        $deviceId = (int)$id;
        InternDevice::blockDevice($deviceId);
        AuditLog::log('device_blocked', 'attendance', $deviceId, null, null, 'success');

        Session::flash('warning', 'Dispositivo bloqueado com sucesso.');
        return $this->redirect('/admin/attendance/devices');
    }

    /**
     * Remove o vínculo de um dispositivo.
     */
    public function removeDevice(Request $request, string $id): Response
    {
        $deviceId = (int)$id;
        InternDevice::removeDevice($deviceId);
        AuditLog::log('device_removed', 'attendance', $deviceId, null, null, 'success');

        Session::flash('success', 'Vínculo do dispositivo removido com sucesso.');
        return $this->redirect('/admin/attendance/devices');
    }
}
