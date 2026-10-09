<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (!Session::has('user')) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Não autenticado. Por favor, inicie sessão.'
                ], 401);
            }

            Session::flash('error', 'Sessão expirada. Por favor, autentique-se para continuar.');
            return (new Response())->redirect('/login');
        }

        $user = Session::get('user');
        if (($user['status'] ?? '') !== 'active') {
            Session::destroy();
            Session::flash('error', 'A sua conta está inativa ou bloqueada. Contacte o administrador.');
            return (new Response())->redirect('/login');
        }

        // Força alteração da senha padrão no primeiro acesso (Item 3)
        if (!empty($user['must_change_password']) && (int)$user['must_change_password'] === 1) {
            $path = $request->getPath();
            if ($path !== '/force-password-change' && $path !== '/logout' && !str_starts_with($path, '/api/logout')) {
                if ($request->isAjax() || str_starts_with($path, '/api/')) {
                    return (new Response())->json([
                        'success' => false,
                        'must_change_password' => true,
                        'error' => 'É obrigatório definir uma nova palavra-passe pessoal no primeiro acesso.'
                    ], 403);
                }
                return (new Response())->redirect('/force-password-change');
            }
        }

        return null;
    }
}
