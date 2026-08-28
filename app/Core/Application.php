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

        // Ensure log directory exists
        $logDir = $this->basePath . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if ($isDebug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Catch PHP warnings and notices
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($isDebug) {
            $this->logError("PHP Error ({$errno}): {$errstr} in {$errfile} on line {$errline}");
            if ($isDebug && (error_reporting() & $errno)) {
                // In debug mode, don't crash for notices/warnings unless fatal
                return false;
            }
            return true;
        });

        // Catch Uncaught Exceptions
        set_exception_handler(function (Throwable $e) use ($isDebug) {
            $this->handleException($e, $isDebug);
        });

        // Catch Fatal Errors on shutdown
        register_shutdown_function(function () use ($isDebug) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->logError("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
                if (!headers_sent()) {
                    $this->handleFatalError($error['message'], $isDebug);
                }
            }
        });
    }

    private function logError(string $message): void
    {
        $logFile = $this->basePath . '/storage/logs/error.log';
        $entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        error_log($entry, 3, $logFile);
    }

    private function handleException(Throwable $e, bool $isDebug): void
    {
        $this->logError(sprintf(
            "Exception: %s in %s on line %d\nStack trace:\n%s",
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
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();
                $data['trace'] = $e->getTrace();
            }
            $response->json($data, $statusCode)->send();
            return;
        }

        $viewData = [
            'message' => $isDebug ? $e->getMessage() : 'Ocorreu um erro inesperado ao processar o seu pedido.',
            'exception' => $isDebug ? $e : null,
            'isDebug' => $isDebug
        ];

        try {
            $html = View::render('errors.500', $viewData, 'public');
            $response->setContent($html)->send();
        } catch (\Throwable $renderErr) {
            $this->logError("Failed to render 500 view: " . $renderErr->getMessage());
            echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
            echo "<h1 style='color: #dc3545;'>500 - Erro Interno do Servidor</h1>";
            echo "<p>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>";
            if ($isDebug) {
                echo "<pre style='text-align: left; background: #f8f9fa; padding: 15px; border-radius: 6px;'>" . htmlspecialchars((string)$e) . "</pre>";
            }
            echo "<a href='/login' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 6px;'>Voltar ao Início</a>";
            echo "</div>";
        }
    }

    private function handleFatalError(string $message, bool $isDebug): void
    {
        $response = new Response();
        $response->setStatusCode(500);

        $viewData = [
            'message' => $isDebug ? $message : 'Ocorreu um erro crítico no servidor.',
            'exception' => null,
            'isDebug' => $isDebug
        ];

        try {
            $html = View::render('errors.500', $viewData, 'public');
            $response->setContent($html)->send();
        } catch (\Throwable) {
            echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
            echo "<h1 style='color: #dc3545;'>500 - Erro Interno do Servidor</h1>";
            echo "<p>Ocorreu um erro ao processar a sua solicitação.</p>";
            echo "</div>";
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request = new Request();
        try {
            $response = $this->router->dispatch($request);
            $response->send();
        } catch (Throwable $e) {
            $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $this->handleException($e, $isDebug);
        }
    }
}
