<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Asoftmedia Internship Management System',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Luanda',
    'secret' => $_ENV['APP_SECRET'] ?? 'default_secret_key_change_me',
    
    'company' => [
        'name' => $_ENV['COMPANY_NAME'] ?? 'Asoftmedia',
        'latitude' => (float)($_ENV['COMPANY_LATITUDE'] ?? -8.83833),
        'longitude' => (float)($_ENV['COMPANY_LONGITUDE'] ?? 13.23444),
        'radius_meters' => (int)($_ENV['COMPANY_RADIUS_METERS'] ?? 100),
    ],
    
    'evaluation' => [
        'weights' => [
            'attendance' => (int)($_ENV['WEIGHT_ATTENDANCE'] ?? 20),
            'tasks' => (int)($_ENV['WEIGHT_TASKS'] ?? 30),
            'tests' => (int)($_ENV['WEIGHT_TESTS'] ?? 20),
            'competencies' => (int)($_ENV['WEIGHT_COMPETENCIES'] ?? 15),
            'behavior' => (int)($_ENV['WEIGHT_BEHAVIOR'] ?? 10),
            'final_eval' => (int)($_ENV['WEIGHT_FINAL_EVAL'] ?? 5),
        ],
        'min_attendance' => (int)($_ENV['MIN_ATTENDANCE_PERCENTAGE'] ?? 80),
        'min_grade' => (int)($_ENV['MIN_PASSING_GRADE'] ?? 60),
    ],
    
    'gamification' => [
        'enabled' => filter_var($_ENV['ENABLE_GAMIFICATION'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'public_portfolio' => filter_var($_ENV['ENABLE_PUBLIC_PORTFOLIO'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ]
];
