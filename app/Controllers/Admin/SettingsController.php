<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = SystemSetting::all();
        $settingsGrouped = [];
        foreach ($settings as $s) {
            $settingsGrouped[$s['group_name']][] = $s;
        }

        return $this->render('admin.settings.index', [
            'title' => 'Configurações Globais do Sistema - Asoftmedia',
            'settingsGrouped' => $settingsGrouped
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $all = $request->all();
        unset($all['_csrf_token']);

        // Check evaluation weights sum
        $wAtt = (int)($all['weight_attendance'] ?? 20);
        $wTasks = (int)($all['weight_tasks'] ?? 30);
        $wTests = (int)($all['weight_tests'] ?? 20);
        $wComp = (int)($all['weight_competencies'] ?? 15);
        $wBehav = (int)($all['weight_behavior'] ?? 10);
        $wFinal = (int)($all['weight_final_eval'] ?? 5);

        $sum = $wAtt + $wTasks + $wTests + $wComp + $wBehav + $wFinal;
        if ($sum !== 100) {
            Session::flash('error', "A soma dos pesos de avaliação deve ser exatamente 100% (Soma atual: {$sum}%).");
            return $this->redirect('/admin/settings');
        }

        foreach ($all as $key => $value) {
            SystemSetting::set($key, (string)$value);
        }

        AuditLog::log('settings_update', 'settings', null, null, $all, 'success');

        Session::flash('success', 'Configurações do sistema atualizadas com sucesso!');
        return $this->redirect('/admin/settings');
    }
}
