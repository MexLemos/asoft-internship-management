<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RateLimitMiddleware
{
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function handle(Request $request): ?Response
    {
        $ip = $request->ip();
        $path = $request->getPath();
        $key = '_rate_limit_' . md5($ip . '_' . $path);

        $data = Session::get($key, ['count' => 0, 'expires_at' => time() + $this->decaySeconds]);

        if (time() > $data['expires_at']) {
            $data = ['count' => 1, 'expires_at' => time() + $this->decaySeconds];
        } else {
            $data['count']++;
        }

        Session::set($key, $data);

        if ($data['count'] > $this->maxAttempts) {
            $retryAfter = $data['expires_at'] - time();
            if ($request->isAjax() || str_starts_with($path, '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'error' => "Limite de requisições excedido. Tente novamente em {$retryAfter} segundos."
                ], 429);
            }

            Session::flash('error', "Muitas tentativas. Por favor, aguarde {$retryAfter} segundos.");
            return (new Response())->setStatusCode(429)->setContent(
                "<h1>429 - Limite de Requisições Excedido</h1><p>Por favor, aguarde {$retryAfter} segundos.</p>"
            );
        }

        return null;
    }
}
