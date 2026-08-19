<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(Request $request): Response
    {
        if (Session::has('user')) {
            $user = Session::get('user');
            $route = $this->authService->determineHomeRoute($user['roles'] ?? []);
            return $this->redirect($route);
        }

        return $this->render('auth.login', [
            'title' => 'Iniciar Sessão - Asoftmedia'
        ], 'auth');
    }

    public function login(Request $request): Response
    {
        $identifier = trim((string)$request->input('identifier', ''));
        $password = (string)$request->input('password', '');

        if (empty($identifier) || empty($password)) {
            Session::flash('error', 'Por favor, preencha o utilizador/email e a palavra-passe.');
            return $this->redirect('/login');
        }

        $result = $this->authService->attempt($identifier, $password, $request->ip());

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            return $this->redirect('/login');
        }

        Session::flash('success', 'Bem-vindo de volta, ' . htmlspecialchars($result['user']['name']) . '!');
        return $this->redirect($result['redirect']);
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout();
        Session::flash('info', 'Sessão terminada com sucesso.');
        return $this->redirect('/login');
    }
}
