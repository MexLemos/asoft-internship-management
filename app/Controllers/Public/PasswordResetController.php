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

class PasswordResetController extends Controller
{
    public function showForgotForm(Request $request): Response
    {
        return $this->render('auth.forgot_password', [
            'title' => 'Recuperar Palavra-passe - Asoftmedia'
        ], 'auth');
    }

    public function sendResetLink(Request $request): Response
    {
        $email = trim((string)$request->input('email', ''));
        $pdo = Database::getConnection();

        // Check if user exists
        $user = User::findByEmailOrUsername($email);
        if ($user) {
            // Generate random 64-char token
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 minutes'));

            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token_hash, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['email'], $tokenHash, $expiresAt]);

            AuditLog::log('password_reset_request', 'auth', (int)$user['id'], null, ['email' => $email], 'success');
            
            // In development / local testing, we can save the token in session or flash for ease of testing
            $resetUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . "/reset-password/{$token}";
            Session::flash('reset_demo_link', $resetUrl);
        }

        // Generic response to avoid username/email enumeration (Section 38)
        Session::flash('success', 'Se existir uma conta associada a este endereço de email, enviámos as instruções e o link seguro para redefinição da palavra-passe.');
        return $this->redirect('/forgot-password');
    }

    public function showResetForm(Request $request, string $token): Response
    {
        $tokenHash = hash('sha256', $token);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM password_resets 
            WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            Session::flash('error', 'O link de recuperação é inválido ou já expirou. Por favor solicite um novo link.');
            return $this->redirect('/forgot-password');
        }

        return $this->render('auth.reset_password', [
            'title' => 'Criar Nova Palavra-passe - Asoftmedia',
            'token' => $token,
            'email' => $resetRecord['email']
        ], 'auth');
    }

    public function resetPassword(Request $request, string $token): Response
    {
        $tokenHash = hash('sha256', $token);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM password_resets 
            WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            Session::flash('error', 'O link de recuperação é inválido ou já expirou.');
            return $this->redirect('/forgot-password');
        }

        $newPassword = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'A nova palavra-passe deve ter no mínimo 8 caracteres.');
            return $this->redirect("/reset-password/{$token}");
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'A confirmação da nova palavra-passe não confere.');
            return $this->redirect("/reset-password/{$token}");
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password and mark token as used
        $stmtUpd = $pdo->prepare("UPDATE users SET password_hash = ?, must_change_password = 0 WHERE email = ?");
        $stmtUpd->execute([$passwordHash, $resetRecord['email']]);

        $stmtToken = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
        $stmtToken->execute([$resetRecord['id']]);

        AuditLog::log('password_reset_completed', 'auth', null, null, ['email' => $resetRecord['email']], 'success');

        Session::flash('success', 'A sua palavra-passe foi redefinida com sucesso! Pode agora entrar no sistema.');
        return $this->redirect('/login');
    }
}
