<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;

class PrivacyController extends Controller
{
    public function showPolicy(Request $request): Response
    {
        return $this->render('public.privacy_policy', [
            'title' => 'Política de Protecção de Dados e Privacidade - Asoftmedia'
        ], 'public');
    }

    public function showProfilePrivacy(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];

        $pdo = Database::getConnection();

        // Get Consents history
        $stmtConsents = $pdo->prepare("SELECT * FROM privacy_consents WHERE user_id = ? ORDER BY accepted_at DESC");
        $stmtConsents->execute([$userId]);
        $consents = $stmtConsents->fetchAll();

        // Get User Requests history
        $stmtReq = $pdo->prepare("SELECT * FROM privacy_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmtReq->execute([$userId]);
        $requests = $stmtReq->fetchAll();

        $role = $sessionUser['roles'][0] ?? 'intern';
        $layout = in_array($role, ['super_admin', 'admin'], true) ? 'admin' : ($role === 'supervisor' ? 'supervisor' : ($role === 'institution' ? 'institution' : 'intern'));

        return $this->render('profile.privacy', [
            'title' => 'Privacidade e Protecção de Dados Pessoais - Asoftmedia',
            'consents' => $consents,
            'requests' => $requests
        ], $layout);
    }

    public function recordConsent(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $policyVersion = $request->input('policy_version', '1.0');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO privacy_consents (user_id, policy_version, ip_address, user_agent, consent_type, accepted_at)
            VALUES (?, ?, ?, ?, 'general_policy', NOW())
        ");
        $stmt->execute([$userId, $policyVersion, $ip, $ua]);

        AuditLog::log('privacy_consent_accepted', 'privacy', $userId, null, ['version' => $policyVersion], 'success');

        Session::flash('success', 'Termo de consentimento e política de privacidade registados com sucesso!');
        
        $authService = new \App\Services\AuthService();
        $home = $authService->determineHomeRoute($sessionUser['roles'] ?? []);
        return $this->redirect($home);
    }

    public function submitRequest(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $type = $request->input('request_type', 'access');
        $details = trim((string)$request->input('details', ''));

        if (!empty($details)) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO privacy_requests (user_id, request_type, details, status, created_at)
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$userId, $type, $details]);

            AuditLog::log('privacy_request_submitted', 'privacy', $userId, null, ['type' => $type], 'success');

            Session::flash('success', 'A sua solicitação foi registada e encaminhada ao Encarregado de Proteção de Dados da Asoftmedia.');
        }

        return $this->redirect('/profile/privacy');
    }
}
