<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\InternLifecycleService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo " Verificação Automática do Ciclo de Vida dos Estagiários\n";
echo " Asoftmedia Internship Management System (AIMS)\n";
echo " Data: " . date('Y-m-d H:i:s') . "\n";
echo "========================================================\n\n";

try {
    $service = new InternLifecycleService();
    $transitioned = $service->autoCheckExpiringInternships();
    $count = count($transitioned);

    if ($count === 0) {
        echo "✔ Nenhum contrato expirado detectado. Todos os estagiários ativos estão dentro do prazo previsto.\n";
    } else {
        echo "✔ Concluído! {$count} estagiário(s) transitaram para 'Aguardando Homologação':\n";
        foreach ($transitioned as $item) {
            echo "  - [ID: {$item['intern_id']}] {$item['full_name']} (Término: {$item['end_date']})\n";
        }
    }
} catch (\Throwable $e) {
    echo "❌ Erro durante verificação: " . $e->getMessage() . "\n";
    exit(1);
}
