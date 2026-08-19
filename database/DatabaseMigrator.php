<?php

declare(strict_types=1);

namespace Database;

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use Dotenv\Dotenv;
use PDO;

class DatabaseMigrator
{
    private PDO $pdo;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
        $this->pdo = Database::getConnection();
        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function run(): void
    {
        $migrationFiles = glob(__DIR__ . '/migrations/*.sql');
        sort($migrationFiles);

        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmtBatch = $this->pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $currentBatch = ((int)$stmtBatch->fetch()['max_batch']) + 1;

        $newCount = 0;
        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            if (in_array($filename, $executed, true)) {
                continue;
            }

            echo "A executar migration: {$filename} ...\n";
            $sql = file_get_contents($file);

            // Execute SQL migration
            $this->pdo->exec($sql);

            // Record execution
            $insert = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $insert->execute([$filename, $currentBatch]);

            $newCount++;
            echo "✔ Concluída: {$filename}\n";
        }

        if ($newCount === 0) {
            echo "Nenhuma migração pendente. Todas as tabelas estão atualizadas.\n";
        } else {
            echo "Sucesso: {$newCount} migrações executadas no lote {$currentBatch}.\n";
        }
    }
}

if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
    $migrator = new DatabaseMigrator();
    $migrator->run();
}
