<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Institution;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo " Sincronização de Contas Institucionais (Role 5)\n";
echo " Senha Padrão: 123EstagioAsoft\n";
echo "========================================================\n\n";

try {
    $results = Institution::syncMissingInstitutionUsers();
    $count = count($results);

    if ($count === 0) {
        echo "✔ Todas as instituições já possuem contas vinculadas. Nenhuma ação necessária.\n";
    } else {
        echo "✔ Sincronização concluída com sucesso! {$count} conta(s) criada(s):\n";
        foreach ($results as $item) {
            echo "  - Instituição: {$item['institution']}\n";
            echo "    Email/Username: {$item['email']}\n";
            echo "    Palavra-passe: 123EstagioAsoft\n";
            echo "    ID do Utilizador: {$item['user_id']}\n\n";
        }
    }
} catch (\Throwable $e) {
    echo "❌ Erro ao sincronizar: " . $e->getMessage() . "\n";
    exit(1);
}
