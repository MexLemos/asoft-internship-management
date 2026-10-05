<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\Intern;
use App\Services\AttendanceEngine;
use App\Services\DynamicQrAttendanceService;

class AttendanceScanController extends Controller
{
    private DynamicQrAttendanceService $qrService;
    private AttendanceEngine $attendanceEngine;

    public function __construct()
    {
        $this->qrService = new DynamicQrAttendanceService();
        $this->attendanceEngine = new AttendanceEngine();
    }

    public function index(Request $request): Response
    {
        $rawToken = (string)$request->input('token', '');
        $token = $this->qrService->extractTokenHash($rawToken);

        $qrValidation = !empty($token) ? $this->qrService->validateOnly($token) : [
            'valid' => false,
            'message' => 'Nenhum código QR fornecido. Por favor, aponte a câmara para o monitor do terminal na recepção da Asoftmedia.'
        ];

        $user = Session::get('user');
        $isLoggedIn = !empty($user);
        $roles = $user['roles'] ?? [];
        $isIntern = in_array('intern', $roles, true);
        $isStaff = in_array('supervisor', $roles, true) || in_array('admin', $roles, true) || in_array('super_admin', $roles, true);

        $intern = null;
        $todayRecord = null;
        $actionType = 'check_in';

        if ($isLoggedIn && $isIntern) {
            $intern = Intern::findByUserId((int)$user['id']);
            if ($intern) {
                $todayRecord = Attendance::getTodayForIntern((int)$intern['id']);
                if ($todayRecord) {
                    if (empty($todayRecord['check_out'])) {
                        $actionType = 'check_out';
                    } else {
                        $actionType = 'completed';
                    }
                }
            }
        }

        $loginRedirectUrl = '/login?redirect=' . urlencode('/attendance/scan?token=' . urlencode($token));

        return $this->render('attendance.scan', [
            'title' => 'Registo de Ponto por QR Code - Asoftmedia',
            'token' => $token,
            'qrValidation' => $qrValidation,
            'isLoggedIn' => $isLoggedIn,
            'user' => $user,
            'isIntern' => $isIntern,
            'isStaff' => $isStaff,
            'intern' => $intern,
            'todayRecord' => $todayRecord,
            'actionType' => $actionType,
            'loginRedirectUrl' => $loginRedirectUrl
        ], 'auth');
    }

    public function confirm(Request $request): Response
    {
        $user = Session::get('user');
        if (!$user) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Sessão expirada. Por favor, inicie sessão.'], 401);
            }
            Session::flash('error', 'Por favor, autentique-se para registar a presença.');
            return $this->redirect('/login');
        }

        $intern = Intern::findByUserId((int)$user['id']);
        if (!$intern) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Apenas contas de estagiário podem registar presença.'], 403);
            }
            Session::flash('error', 'A sua conta não está vinculada a um perfil de estagiário.');
            return $this->redirect('/attendance/scan');
        }

        $internId = (int)$intern['id'];
        $rawToken = (string)$request->input('token', '');
        $qrToken = $this->qrService->extractTokenHash($rawToken);

        $lat = (float)$request->input('latitude', 0.0);
        $lng = (float)$request->input('longitude', 0.0);
        $accuracy = $request->input('accuracy') !== null ? (float)$request->input('accuracy') : null;
        $deviceUuid = $request->input('device_uuid') ? trim((string)$request->input('device_uuid')) : null;

        $todayRecord = Attendance::getTodayForIntern($internId);
        $isCheckOut = $todayRecord && empty($todayRecord['check_out']);

        if ($isCheckOut) {
            $result = $this->attendanceEngine->processCheckOut(
                $internId,
                $lat,
                $lng,
                $accuracy,
                $request->ip(),
                $request->userAgent(),
                $deviceUuid,
                $qrToken
            );
        } else {
            $result = $this->attendanceEngine->processCheckIn(
                $internId,
                $lat,
                $lng,
                $accuracy,
                $request->ip(),
                $request->userAgent(),
                $deviceUuid,
                $qrToken
            );
        }

        if ($request->isAjax()) {
            return $this->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        return $this->redirect('/attendance/scan?token=' . urlencode($qrToken));
    }
}
