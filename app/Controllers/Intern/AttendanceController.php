<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\Intern;
use App\Models\SystemSetting;
use App\Services\AttendanceEngine;

class AttendanceController extends Controller
{
    private AttendanceEngine $attendanceEngine;

    public function __construct()
    {
        $this->attendanceEngine = new AttendanceEngine();
    }

    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        $internId = (int)$intern['id'];
        $todayRecord = Attendance::getTodayForIntern($internId);
        $history = Attendance::getForIntern($internId, 30);
        $stats = Attendance::getStats($internId);

        $companyLat = (float)SystemSetting::get('company_latitude', -8.83833);
        $companyLng = (float)SystemSetting::get('company_longitude', 13.23444);
        $radiusMeters = (int)SystemSetting::get('company_radius_meters', 100);

        return $this->render('intern.attendance.index', [
            'title' => 'Marcar Presença por Geolocalização - Asoftmedia',
            'intern' => $intern,
            'todayRecord' => $todayRecord,
            'history' => $history,
            'stats' => $stats,
            'companyLat' => $companyLat,
            'companyLng' => $companyLng,
            'radiusMeters' => $radiusMeters
        ], 'intern');
    }

    public function checkIn(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->json(['success' => false, 'message' => 'Estagiário não autenticado.'], 401);
        }

        $lat = (float)$request->input('latitude', 0.0);
        $lng = (float)$request->input('longitude', 0.0);
        $accuracy = $request->input('accuracy') ? (float)$request->input('accuracy') : null;

        $result = $this->attendanceEngine->processCheckIn(
            (int)$intern['id'],
            $lat,
            $lng,
            $accuracy,
            $request->ip(),
            $request->userAgent()
        );

        $statusCode = $result['success'] ? 200 : 403;
        return $this->json($result, $statusCode);
    }

    public function checkOut(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->json(['success' => false, 'message' => 'Estagiário não autenticado.'], 401);
        }

        $lat = (float)$request->input('latitude', 0.0);
        $lng = (float)$request->input('longitude', 0.0);
        $accuracy = $request->input('accuracy') ? (float)$request->input('accuracy') : null;

        $result = $this->attendanceEngine->processCheckOut(
            (int)$intern['id'],
            $lat,
            $lng,
            $accuracy,
            $request->ip(),
            $request->userAgent()
        );

        $statusCode = $result['success'] ? 200 : 403;
        return $this->json($result, $statusCode);
    }
}
