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

        return null;
    }
}
