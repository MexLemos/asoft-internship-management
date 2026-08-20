<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Notification;

class NotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];

        $notifications = Notification::getForUser($userId, 50);

        $role = $sessionUser['roles'][0] ?? 'intern';
        $layout = in_array($role, ['super_admin', 'admin'], true) ? 'admin' : ($role === 'supervisor' ? 'supervisor' : ($role === 'institution' ? 'institution' : 'intern'));

        return $this->render('notifications.index', [
            'title' => 'Minhas Notificações - Asoftmedia',
            'notifications' => $notifications
        ], $layout);
    }

    public function markAsRead(Request $request, string $id): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $notificationId = (int)$id;

        Notification::markAsRead($notificationId, $userId);

        $redirect = $request->input('redirect', '/notifications');
        return $this->redirect($redirect);
    }

    public function markAllAsRead(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];

        Notification::markAllAsRead($userId);

        Session::flash('success', 'Todas as notificações foram marcadas como lidas.');
        return $this->redirect('/notifications');
    }
}
