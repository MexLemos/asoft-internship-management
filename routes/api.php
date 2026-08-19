<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Models\Intern;
use App\Models\Institution;
use App\Models\Task;

/** @var Router $router */

$router->group(['prefix' => 'api/v1'], function (Router $r) {
    // Health check endpoint
    $r->get('/health', function (Request $request) {
        return (new Response())->json([
            'status' => 'healthy',
            'system' => 'Asoftmedia Internship Management System (AIMS)',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });

    // Interns API
    $r->get('/interns', function (Request $request) {
        $interns = Intern::all();
        return (new Response())->json([
            'success' => true,
            'count' => count($interns),
            'data' => $interns
        ]);
    });

    // Institutions API
    $r->get('/institutions', function (Request $request) {
        $institutions = Institution::all();
        return (new Response())->json([
            'success' => true,
            'count' => count($institutions),
            'data' => $institutions
        ]);
    });

    // Tasks API
    $r->get('/tasks', function (Request $request) {
        $tasks = Task::all();
        return (new Response())->json([
            'success' => true,
            'count' => count($tasks),
            'data' => $tasks
        ]);
    });
});
