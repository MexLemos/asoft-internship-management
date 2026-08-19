<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class PermissionMiddleware
{
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');
        if (!$user) {
            return (new Response())->redirect('/login');
        }

        $userRoles = $user['roles'] ?? [];
        if (in_array('super_admin', $userRoles, true)) {
            return null;
        }

        $permissions = $user['permissions'] ?? [];
        if (!in_array($this->permission, $permissions, true)) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'error' => "Permissão necessária ausente: [{$this->permission}]."
                ], 403);
            }

            return (new Response())->setStatusCode(403)->setContent(
                View::render('errors.403', [
                    'message' => "Não possui a permissão necessária ({$this->permission}) para realizar esta ação."
                ], 'public')
            );
        }

        return null;
    }
}
