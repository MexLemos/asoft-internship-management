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

// Test 3.1: Posição dentro do raio configurado da Asoftmedia
$compLat = (float)\App\Models\SystemSetting::get('company_latitude', -8.83833);
$compLng = (float)\App\Models\SystemSetting::get('company_longitude', 13.23444);
$testInside = $attEngine->processCheckIn($internId, $compLat + 0.00005, $compLng + 0.00005, 10.0, '197.149.12.34', 'PHPUnit Test Device');
if (!$testInside['success']) {
    throw new RuntimeException("Falha no teste dentro do raio: " . $testInside['message']);
}
echo "• Teste Dentro do Raio: ✔ Autorizado com sucesso! ({$testInside['message']})\n";

// Test 3.2: Posição a 5000 metros (FORA do raio)
$testOutside = $attEngine->processCheckIn($internId, $compLat + 0.05, $compLng + 0.05, 10.0, '197.149.12.34', 'PHPUnit Test Device');
if ($testOutside['success']) {
    throw new RuntimeException("ERRO: Presença fora do raio foi indevidamente autorizada!");
}
echo "• Teste Fora do Raio: ✔ Bloqueado corretamente! Mensagem: '{$testOutside['message']}'\n\n";

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

// Test 7: Intern Lifecycle State Machine & History
echo "\n7. Teste da Máquina de Estados do Ciclo de Vida do Estagiário ...\n";
$lifecycle = new \App\Services\InternLifecycleService();

// 7.1 Validação de regras teóricas de transição
if (!\App\Services\InternLifecycleService::canTransition('active', 'suspended')) {
    throw new RuntimeException("Erro: Transição active -> suspended deveria ser permitida.");
}
if (\App\Services\InternLifecycleService::canTransition('terminated_anomalous', 'active')) {
    throw new RuntimeException("Erro: Estado terminal não deve permitir transições.");
}
echo "• Guardas da Máquina de Estados: ✔ Validações de transição conformes!\n";

// 7.2 Execução de transição atómica com justificação
$superAdminUser = $pdo->query("SELECT id FROM users WHERE username = 'superadmin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$adminUserId = (int)($superAdminUser['id'] ?? 1);

// Transitar para 'suspended'
$resTrans = $lifecycle->transition($internId, 'suspended', $adminUserId, 'Teste automatizado: suspensão preventiva para auditoria');
if (!$resTrans['success']) {
    throw new RuntimeException("Falha ao executar transição para suspended.");
}

// Verificar se utilizador foi desativado
$userChk = \App\Models\User::findById((int)$intern['user_id']);
if ($userChk['status'] !== 'inactive') {
    throw new RuntimeException("Erro: Conta de utilizador deveria ter sido inativada após suspensão.");
}

// Verificar se histórico de estados registou a transição
$history = \App\Models\Intern::getStatusHistory($internId);
if (empty($history) || $history[0]['to_status'] !== 'suspended') {
    throw new RuntimeException("Erro: Histórico de ciclo de vida não gravou a transição.");
}
echo "• Transição Ativa -> Suspensa: ✔ Registada com sucesso no histórico com conta inativada!\n";

// Restaurar para 'active'
$resRestore = $lifecycle->transition($internId, 'active', $adminUserId, 'Teste automatizado: reativação regular do estágio');
$userChk2 = \App\Models\User::findById((int)$intern['user_id']);
if ($userChk2['status'] !== 'active') {
    throw new RuntimeException("Erro: Conta de utilizador deveria ter sido reativada.");
}
echo "• Reativação Suspensa -> Ativa: ✔ Restaurada com sucesso no histórico com conta ativa!\n";

// 7.3 Tentativa de transição com justificação vazia (deve falhar)
try {
    $lifecycle->transition($internId, 'suspended', $adminUserId, '   ');
    throw new RuntimeException("ERRO: Deveria ter bloqueado transição sem justificação!");
} catch (\InvalidArgumentException $e) {
    echo "• Justificação Obrigatória: ✔ Bloqueou transição vazia corretamente!\n";
}

// Test 8: Mentorship & Continuous Supervision Logs
echo "\n8. Teste de Mentoria e Acompanhamento Contínuo (1-on-1) ...\n";
$supervisorUser = $pdo->query("SELECT id FROM users WHERE username = 'carlos.silva' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$supId = (int)($supervisorUser['id'] ?? $adminUserId);

$sessionLogId = \App\Models\MentorshipLog::create([
    'intern_id' => $internId,
    'supervisor_id' => $supId,
    'session_date' => date('Y-m-d H:i:s'),
    'session_type' => '1_on_1',
    'title' => 'Sessão Teste de Alinhamento e Postura Técnica',
    'summary' => 'Reunião de acompanhamento inicial para avaliar curva de aprendizagem.',
    'topics_discussed' => 'Git, Clean Code, Pontualidade',
    'action_items' => 'Revisar pull requests pendentes até sexta-feira',
    'rating' => 5,
    'is_private' => 1
]);

if ($sessionLogId <= 0) {
    throw new RuntimeException("Falha ao criar registo de mentoria.");
}

// Verificar filtro de privacidade
$allLogs = \App\Models\MentorshipLog::getForIntern($internId, true);
$publicLogs = \App\Models\MentorshipLog::getForIntern($internId, false);

if (count($allLogs) <= count($publicLogs)) {
    throw new RuntimeException("Erro: Registo privado não foi filtrado corretamente para o estagiário.");
}
echo "• Criação & Privacidade de Mentoria: ✔ Sessão 1-on-1 criada e filtro privado validado!\n";

// Limpeza da sessão teste
\App\Models\MentorshipLog::delete($sessionLogId);
echo "• Limpeza de Dados de Teste: ✔ Concluída com sucesso!\n";

// Test 9: Dynamic QR Code Generation & Single-Use Anti-Replay
echo "\n9. Teste de QR Code Dinâmico & Prevenção Anti-Replay ...\n";
$qrService = new \App\Services\DynamicQrAttendanceService();
$tokenData = $qrService->getCurrentTerminalToken($adminUserId);

if (empty($tokenData['token_hash']) || !str_starts_with($tokenData['qr_data_url'], 'data:image/png;base64,')) {
    throw new RuntimeException("Falha na geração do QR Code dinâmico do terminal.");
}
echo "• Geração de Token Rotativo TOTP: ✔ Gerado com sucesso! (Hash: " . substr($tokenData['token_hash'], 0, 16) . "...)\n";

// 9.1 Primeiro resgate válido pelo estagiário
$firstRedeem = $qrService->validateAndRedeem($tokenData['token_hash'], $internId);
if (!$firstRedeem['valid']) {
    throw new RuntimeException("Falha no primeiro resgate do QR Code: " . $firstRedeem['message']);
}
echo "• Primeiro Resgate do Token: ✔ Autorizado com sucesso!\n";

// 9.2 Tentativa de reutilização do mesmo token pelo mesmo estagiário (Anti-Replay)
$secondRedeem = $qrService->validateAndRedeem($tokenData['token_hash'], $internId);
if ($secondRedeem['valid']) {
    throw new RuntimeException("ERRO: Token rotativo foi reutilizado pelo mesmo estagiário (Replay Attack falhou em ser bloqueado)!");
}
echo "• Proteção Anti-Replay: ✔ Segunda tentativa bloqueada com sucesso! ('{$secondRedeem['message']}')\n";

// 9.3 Token adulterado ou inexistente
$fakeRedeem = $qrService->validateAndRedeem('token_fraudulento_inexistente', $internId);
if ($fakeRedeem['valid']) {
    throw new RuntimeException("ERRO: Token inválido foi autorizado!");
}
echo "• Token Fraudulento: ✔ Rejeitado com sucesso!\n";

// Test 10: Device Binding Anti-Fraude & Limiar de Precisão GPS
echo "\n10. Teste de Vínculo de Dispositivos (Device Binding) & Precisão GPS ...\n";
$pdo->exec("DELETE FROM intern_devices WHERE intern_id = {$internId}");
$testDeviceUuid = 'test-device-uuid-' . bin2hex(random_bytes(8));

// 10.1 Primeiro dispositivo do estagiário - Registo e validação
$devRes = \App\Models\InternDevice::validateOrRegister($internId, $testDeviceUuid, 'Chrome Windows PC', 'Mozilla/5.0 Test Suite');
if (!$devRes['valid']) {
    throw new RuntimeException("Falha no registo do primeiro dispositivo: " . $devRes['message']);
}
echo "• Registo do Dispositivo: ✔ Dispositivo vinculado com sucesso!\n";

// 10.2 Bloqueio de dispositivo pelo administrador
$deviceRow = $pdo->query("SELECT id FROM intern_devices WHERE device_uuid = '{$testDeviceUuid}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$testDeviceId = (int)$deviceRow['id'];

\App\Models\InternDevice::blockDevice($testDeviceId);
$blockedCheck = \App\Models\InternDevice::validateOrRegister($internId, $testDeviceUuid, 'Chrome Windows PC', 'Mozilla/5.0 Test Suite');
if ($blockedCheck['valid']) {
    throw new RuntimeException("ERRO: Dispositivo bloqueado foi autorizado!");
}
echo "• Dispositivo Bloqueado: ✔ Acesso bloqueado corretamente! ('{$blockedCheck['message']}')\n";

// 10.3 Homologação / Desbloqueio do dispositivo
\App\Models\InternDevice::trustDevice($testDeviceId);
$trustedCheck = \App\Models\InternDevice::validateOrRegister($internId, $testDeviceUuid, 'Chrome Windows PC', 'Mozilla/5.0 Test Suite');
if (!$trustedCheck['valid']) {
    throw new RuntimeException("Falha na re-homologação do dispositivo.");
}
echo "• Homologação do Dispositivo: ✔ Dispositivo re-autorizado com sucesso!\n";

// 10.4 Limiar de Precisão GPS (Rejeição com precisão degradada > 80m)
$degradedGps = $attEngine->processCheckIn(
    $internId,
    $compLat + 0.00005,
    $compLng + 0.00005,
    250.0, // Precisão degradada de 250 metros (> 80 metros padrão)
    '197.149.12.34',
    'PHPUnit Test Device',
    $testDeviceUuid
);

if ($degradedGps['success']) {
    throw new RuntimeException("ERRO: GPS com precisão degradada (250m) deveria ter sido rejeitado!");
}
echo "• Filtro de Precisão GPS: ✔ Rejeitou com precisão de 250m! ('{$degradedGps['message']}')\n";

// 10.5 Limpeza do dispositivo de teste
\App\Models\InternDevice::removeDevice($testDeviceId);
echo "• Limpeza do Dispositivo de Teste: ✔ Removido com sucesso!\n";

echo "\n========================================================\n";
echo "TODOS OS TESTES DE INTEGRAÇÃO PASSARAM COM 100% DE SUCESSO!\n";
echo "========================================================\n";
