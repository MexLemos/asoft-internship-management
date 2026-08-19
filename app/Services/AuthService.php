<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;

class AuthService
{
    public function attempt(string $identifier, string $password, string $ip): array
    {
        $user = User::findByEmailOrUsername($identifier);

        if (!$user) {
            AuditLog::log('login_failed', 'auth', null, null, ['identifier' => $identifier], 'failed');
            return ['success' => false, 'message' => 'Credenciais inválidas. Verifique o seu email/usuário e senha.'];
        }

        // Check if locked
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            AuditLog::log('login_blocked_lockout', 'auth', (int)$user['id'], null, null, 'suspicious');
            return [
                'success' => false,
                'message' => "Conta temporariamente bloqueada por excesso de tentativas. Tente novamente em {$remaining} minutos."
            ];
        }

        // Check if inactive/blocked
        if ($user['status'] !== 'active') {
            AuditLog::log('login_blocked_status', 'auth', (int)$user['id'], null, ['status' => $user['status']], 'failed');
            return ['success' => false, 'message' => 'A sua conta está inativa ou bloqueada. Contacte o administrador.'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            User::incrementFailedAttempts((int)$user['id']);
            AuditLog::log('login_failed_password', 'auth', (int)$user['id'], null, null, 'failed');
            return ['success' => false, 'message' => 'Credenciais inválidas. Verifique o seu email/usuário e senha.'];
        }

        // Success - regenerate session
        Session::regenerate();

        // Update login stats
        User::updateLastLogin((int)$user['id'], $ip);

        // Sanitize user array for session
        unset($user['password_hash']);
        Session::set('user', $user);

        AuditLog::log('login_success', 'auth', (int)$user['id'], null, ['roles' => $user['roles']], 'success');

        return [
            'success' => true,
            'user' => $user,
            'redirect' => $this->determineHomeRoute($user['roles'])
        ];
    }

    public function logout(): void
    {
        $user = Session::get('user');
        if ($user) {
            AuditLog::log('logout', 'auth', (int)$user['id'], null, null, 'success');
        }
        Session::destroy();
    }

    public function determineHomeRoute(array $roles): string
    {
        if (in_array('super_admin', $roles, true) || in_array('admin', $roles, true)) {
            return '/admin/dashboard';
        }
        if (in_array('supervisor', $roles, true)) {
            return '/supervisor/dashboard';
        }
        if (in_array('intern', $roles, true)) {
            return '/intern/dashboard';
        }
        if (in_array('institution', $roles, true)) {
            return '/institution/dashboard';
        }
        return '/login';
    }
}
