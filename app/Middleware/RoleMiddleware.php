<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(string ...$roles)
    {
        $this->allowedRoles = $roles;
    }

    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');
        if (!$user) {
            return (new Response())->redirect('/login');
        }

        $userRoles = $user['roles'] ?? [];

        // Super Admin has universal access
        if (in_array('super_admin', $userRoles, true)) {
            return null;
        }

        // Check intersection
        $hasAccess = false;
        foreach ($this->allowedRoles as $role) {
            if (in_array($role, $userRoles, true)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Acesso negado. Perfil não autorizado.'
                ], 403);
            }

            return (new Response())->setStatusCode(403)->setContent(
                View::render('errors.403', [
                    'message' => 'Não tem permissão para aceder a este recurso.'
                ], 'public')
            );
        }

        return null;
    }
}
