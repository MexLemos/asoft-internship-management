<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AttendanceAttempt;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = AuditLog::getRecent(50);
        $suspiciousAttempts = AttendanceAttempt::getRecentSuspicious(30);

        return $this->render('admin.audit.index', [
            'title' => 'Auditoria e Logs de Segurança - Asoftmedia',
            'logs' => $logs,
            'suspiciousAttempts' => $suspiciousAttempts
        ], 'admin');
    }
}
