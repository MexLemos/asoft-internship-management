<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Intern;

class AttendanceRequiredMiddleware
{
    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');
        if (!$user) {
            return (new Response())->redirect('/login');
        }

        // Super administradores têm isenção pedagógica
        $userRoles = $user['roles'] ?? [];
        if (in_array('super_admin', $userRoles, true)) {
            return null;
        }

        $intern = Intern::findByUserId((int)$user['id']);
        if (!$intern) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'message' => 'Estagiário não autenticado.'
                ], 401);
            }
            return (new Response())->redirect('/login');
        }

        $internId = (int)$intern['id'];

        // 1. Verificar regime de trabalho e autorização remota
        $workMode = $intern['work_mode'] ?? 'presential';
        $remoteUntil = $intern['remote_authorized_until'] ?? null;
        $isRemoteAuthorized = ($workMode === 'remote')
            || (!empty($remoteUntil) && $remoteUntil >= date('Y-m-d'));

        if ($isRemoteAuthorized) {
            // Estagiário autorizado em regime remoto: dispensa de marcação física na sede
            return null;
        }

        // 2. Verificar se o estagiário já realizou o check-in no dia de hoje
        $todayRecord = Attendance::getTodayForIntern($internId);
        $hasCheckedIn = !empty($todayRecord['check_in_time']);

        if (!$hasCheckedIn) {
            AuditLog::log('attendance_required_blocked', 'intern', $internId, null, [
                'path' => $request->getPath(),
                'method' => $request->getMethod(),
                'work_mode' => $workMode
            ], 'suspicious');

            if ($request->isAjax() || $request->getMethod() === 'POST' || str_starts_with($request->getPath(), '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'message' => 'Ação bloqueada: É obrigatório registar a sua presença (check-in) na sede da Asoftmedia hoje antes de iniciar tarefas, submeter código ou realizar avaliações.'
                ], 403);
            }

            Session::flash('warning', 'Acesso condicionado: Para realizar esta atividade ou avaliação, deve primeiro marcar a sua presença de hoje no terminal da receção ou por geolocalização.');
            return (new Response())->redirect('/intern/attendance');
        }

        return null;
    }
}
