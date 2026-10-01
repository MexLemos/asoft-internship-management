<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AttendanceAttempt;
use App\Models\AuditLog;
use App\Services\DatabaseAutoMigrator;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $pdo = Database::getConnection();
        $logs = AuditLog::getRecent(50);
        $suspiciousAttempts = AttendanceAttempt::getRecentSuspicious(30);

        // Ler últimas linhas do error.log para inspeção direta pelo painel
        $errorLogFile = dirname(__DIR__, 3) . '/storage/logs/error.log';
        $errorLogs = [];
        if (file_exists($errorLogFile)) {
            $lines = file($errorLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                $errorLogs = array_slice($lines, -50);
                $errorLogs = array_reverse($errorLogs);
            }
        }

        $isSchemaSynced = DatabaseAutoMigrator::isSchemaSynchronized($pdo);

        return $this->render('admin.audit.index', [
            'title' => 'Auditoria, Logs e Saúde do Sistema - Asoftmedia',
            'logs' => $logs,
            'suspiciousAttempts' => $suspiciousAttempts,
            'errorLogs' => $errorLogs,
            'isSchemaSynced' => $isSchemaSynced
        ], 'admin');
    }

    public function syncSchema(Request $request): Response
    {
        $pdo = Database::getConnection();
        $result = DatabaseAutoMigrator::ensureSchemaUpToDate($pdo);

        AuditLog::log('schema_manual_sync', 'system', null, null, $result, 'success');

        Session::flash('success', $result['message']);
        return $this->redirect('/admin/audit');
    }
}
