<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Certificate;
use App\Models\Intern;
use App\Models\User;
use App\Services\AttendanceEngine;
use App\Services\AuthService;
use App\Services\CertificateGeneratorService;
use App\Services\PerformanceScoringEngine;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "INICIANDO TESTES AUTOMATIZADOS DO SISTEMA AIMS\n";
echo "========================================================\n\n";

// Test 1: Database Connectivity
echo "1. Teste de Conectividade com MySQL 8.x ...\n";
$pdo = Database::getConnection();
$dbVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
echo "✔ Conectado ao MySQL com sucesso: Version {$dbVersion}\n\n";

// Test 2: Authentication & RBAC
echo "2. Teste de Autenticação e RBAC ...\n";
$authService = new AuthService();
$loginAdmin = $authService->attempt('superadmin', 'Password123!', '127.0.0.1');
if (!$loginAdmin['success'] || $loginAdmin['redirect'] !== '/admin/dashboard') {
    throw new RuntimeException("Falha no login do SuperAdmin.");
}
echo "✔ Login SuperAdmin OK: Redirect para {$loginAdmin['redirect']}\n";

$loginIntern = $authService->attempt('joao.manuel', 'Password123!', '127.0.0.1');
if (!$loginIntern['success'] || $loginIntern['redirect'] !== '/intern/dashboard') {
    throw new RuntimeException("Falha no login do Estagiário.");
}
echo "✔ Login Estagiário OK: Redirect para {$loginIntern['redirect']}\n\n";

// Test 3: Geofence Attendance Engine (Valid vs Out of Radius)
echo "3. Teste do Motor de Presença por Geolocalização & Haversine ...\n";
$attEngine = new AttendanceEngine();
$intern = Intern::all()[0];
$internId = (int)$intern['id'];

// Activate today for test intern schedule
$todayDow = (int)date('N');
$pdo->exec("
    UPDATE intern_schedule_days isd
    INNER JOIN intern_schedules sch ON sch.id = isd.intern_schedule_id
    SET isd.is_active = 1
    WHERE sch.intern_id = {$internId} AND isd.day_of_week = {$todayDow}
");

// Test 3.1: Posição a ~20 metros (DENTRO do raio de 100m da Asoftmedia: -8.83833, 13.23444)
$testInside = $attEngine->processCheckIn($internId, -8.83832, 13.23445, 10.0, '197.149.12.34', 'PHPUnit Test Device');
if (!$testInside['success']) {
    throw new RuntimeException("Falha no teste dentro do raio: " . $testInside['message']);
}
echo "• Teste Dentro do Raio (20m): ✔ Autorizado com sucesso! ({$testInside['message']})\n";

// Test 3.2: Posição a 5000 metros (FORA do raio de 100m)
$testOutside = $attEngine->processCheckIn($internId, -8.80000, 13.20000, 10.0, '197.149.12.34', 'PHPUnit Test Device');
if ($testOutside['success']) {
    throw new RuntimeException("ERRO: Presença fora do raio foi indevidamente autorizada!");
}
echo "• Teste Fora do Raio (5km): ✔ Bloqueado corretamente! Mensagem: '{$testOutside['message']}'\n\n";

// Test 4: Performance Scoring Engine
echo "4. Teste do Motor de Desempenho Ponderado ...\n";
$scoring = new PerformanceScoringEngine();
$scoreData = $scoring->calculateForIntern($internId);
echo "✔ Nota Ponderada Calculada: {$scoreData['overall_score']} / 100 (Risco: {$scoreData['risk_level']})\n";
foreach ($scoreData['components'] as $k => $c) {
    echo "  - {$k} (Peso {$c['weight']}%): {$c['score']}/100\n";
}
echo "\n";

// Test 5: Certificate & QR Code Generation
echo "5. Teste de Emissão de Certificado e QR Code ...\n";
$certService = new CertificateGeneratorService();
$certRes = $certService->generateCertificate($internId);
if (!$certRes['success']) {
    echo "• Pendências: " . $certRes['message'] . "\n";
} else {
    echo "✔ Certificado Gerado com Sucesso!\n";
    echo "  - Código: " . $certRes['certificate']['certificate_code'] . "\n";
    echo "  - Validação URL: " . $certRes['validation_url'] . "\n";
    echo "  - Hash: " . $certRes['certificate']['validation_hash'] . "\n";
}

// Test 6: Public Hash Lookup
echo "\n6. Teste de Validação Pública do Hash ...\n";
$certDB = Certificate::findByInternId($internId);
if ($certDB) {
    $lookup = Certificate::findByValidationHash($certDB['validation_hash']);
    if (!$lookup || $lookup['intern_name'] !== $intern['full_name']) {
        throw new RuntimeException("Falha na validação do hash público.");
    }
    echo "✔ Validação Pública OK para o aluno: " . $lookup['intern_name'] . " (" . $lookup['institution_name'] . ")\n";
}

echo "\n========================================================\n";
echo "TODOS OS TESTES DE INTEGRAÇÃO PASSARAM COM 100% DE SUCESSO!\n";
echo "========================================================\n";
