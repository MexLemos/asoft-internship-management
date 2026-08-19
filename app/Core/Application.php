<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use Throwable;

class Application
{
    private string $basePath;
    private Router $router;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->router = new Router();
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        // Load .env if exists
        if (file_exists($this->basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->safeLoad();
        }

        // Set timezone
        $timezone = $_ENV['APP_TIMEZONE'] ?? 'Africa/Luanda';
        date_default_timezone_set($timezone);

        // Session start
        Session::start();

        // Error handling
        $this->setupErrorHandling();
    }

    private function setupErrorHandling(): void
    {
        $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($isDebug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_exception_handler(function (Throwable $e) use ($isDebug) {
            $this->handleException($e, $isDebug);
        });
    }

    private function handleException(Throwable $e, bool $isDebug): void
    {
        error_log(sprintf(
            "[%s] Exception: %s in %s on line %d\nStack trace: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        $statusCode = 500;
        $response = new Response();
        $response->setStatusCode($statusCode);

        $request = new Request();
        if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
            $data = [
                'success' => false,
                'error' => $isDebug ? $e->getMessage() : 'Ocorreu um erro interno no servidor.'
            ];
            if ($isDebug) {
                $data['trace'] = $e->getTrace();
            }
            $response->json($data, $statusCode)->send();
            return;
        }

        $viewData = [
            'message' => $isDebug ? $e->getMessage() : 'Ocorreu um erro interno ao processar o seu pedido.',
            'exception' => $isDebug ? $e : null
        ];

        try {
            $html = View::render('errors.500', $viewData, 'public');
            $response->setContent($html)->send();
        } catch (\Throwable) {
            echo "<h1>500 - Erro Interno do Servidor</h1>";
            if ($isDebug) {
                echo "<pre>" . htmlspecialchars((string)$e) . "</pre>";
            }
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request = new Request();
        $response = $this->router->dispatch($request);
        $response->send();
    }
}
