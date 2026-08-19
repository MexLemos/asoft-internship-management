<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    private array $exceptRoutes = [
        '/api/',
        '/validar/'
    ];

    public function handle(Request $request): ?Response
    {
        $method = $request->getMethod();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return null;
        }

        $path = $request->getPath();
        foreach ($this->exceptRoutes as $except) {
            if (str_starts_with($path, $except)) {
                return null;
            }
        }

        $token = $request->input('_csrf_token') ?? $request->getHeader('X-CSRF-TOKEN');

        if (!CSRF::validate($token)) {
            if ($request->isAjax()) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Token CSRF inválido ou expirado. Recarregue a página.'
                ], 419);
            }

            Session::flash('error', 'A sua sessão de segurança expirou. Por favor, tente novamente.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            return (new Response())->redirect($referer);
        }

        return null;
    }
}
