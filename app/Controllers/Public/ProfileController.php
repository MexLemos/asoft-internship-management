<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $user = User::findById((int)$sessionUser['id']);
        $intern = Intern::findByUserId((int)$sessionUser['id']);

        $role = $sessionUser['roles'][0] ?? 'intern';
        $layout = in_array($role, ['super_admin', 'admin'], true) ? 'admin' : ($role === 'supervisor' ? 'supervisor' : ($role === 'institution' ? 'institution' : 'intern'));

        return $this->render('profile.show', [
            'title' => 'Meu Perfil - Asoftmedia',
            'user' => $user,
            'intern' => $intern
        ], $layout);
    }

    public function update(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $data = $request->all();

        $name = trim((string)($data['name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $linkedin = trim((string)($data['linkedin_url'] ?? ''));
        $github = trim((string)($data['github_url'] ?? ''));

        $pdo = Database::getConnection();

        // Handle Profile Photo Upload if present
        $photoName = null;
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes, true)) {
                Session::flash('error', 'Formato de imagem inválido. Aceite apenas JPG, PNG ou WebP.');
                return $this->redirect('/profile');
            }

            if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                Session::flash('error', 'A imagem deve ter no máximo 2MB.');
                return $this->redirect('/profile');
            }

            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photoName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . strtolower($ext);
            $targetDir = dirname(__DIR__, 3) . '/public/uploads/avatars/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photoName);

            $stmtPhoto = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmtPhoto->execute([$photoName, $userId]);
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, phone = ?, linkedin_url = ?, github_url = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $linkedin, $github, $userId]);

        // If intern, also update intern record phone
        $intern = Intern::findByUserId($userId);
        if ($intern) {
            $stmtIntern = $pdo->prepare("UPDATE interns SET full_name = ?, phone = ? WHERE id = ?");
            $stmtIntern->execute([$name, $phone, (int)$intern['id']]);
        }

        // Update session
        $updatedUser = User::findById($userId);
        $updatedUser['roles'] = $sessionUser['roles'] ?? [];
        $updatedUser['permissions'] = $sessionUser['permissions'] ?? [];
        Session::set('user', $updatedUser);

        AuditLog::log('profile_update', 'users', $userId, null, null, 'success');

        Session::flash('success', 'Perfil atualizado com sucesso!');
        return $this->redirect('/profile');
    }

    public function changePassword(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $user = User::findById((int)$sessionUser['id']);

        $role = $sessionUser['roles'][0] ?? 'intern';
        $layout = in_array($role, ['super_admin', 'admin'], true) ? 'admin' : ($role === 'supervisor' ? 'supervisor' : ($role === 'institution' ? 'institution' : 'intern'));

        return $this->render('profile.change_password', [
            'title' => 'Alterar Palavra-passe - Asoftmedia',
            'user' => $user
        ], $layout);
    }

    public function updatePassword(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $user = User::findById($userId);

        $currentPassword = (string)$request->input('current_password', '');
        $newPassword = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        // If must change password, current password check can be optional if first login
        if (!$user['must_change_password']) {
            if (!password_verify($currentPassword, $user['password_hash'])) {
                Session::flash('error', 'A palavra-passe atual informada está incorreta.');
                return $this->redirect('/profile/change-password');
            }
        }

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'A nova palavra-passe deve ter no mínimo 8 caracteres.');
            return $this->redirect('/profile/change-password');
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'A confirmação da nova palavra-passe não confere.');
            return $this->redirect('/profile/change-password');
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?");
        $stmt->execute([$newHash, $userId]);

        AuditLog::log('password_change', 'users', $userId, null, null, 'success');

        Session::flash('success', 'Palavra-passe alterada com sucesso!');
        
        $authService = new \App\Services\AuthService();
        $homeRoute = $authService->determineHomeRoute($sessionUser['roles'] ?? []);
        return $this->redirect($homeRoute);
    }
}
