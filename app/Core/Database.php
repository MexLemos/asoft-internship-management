<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns singleton PDO connection instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dbConfig = $config['connections']['mysql'];

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $dbConfig['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $dbConfig['options']
                );

                \App\Services\DatabaseAutoMigrator::ensureSchemaUpToDate(self::$instance);
            } catch (PDOException $e) {
                error_log('Database Connection Error: ' . $e->getMessage());
                throw new RuntimeException('Não foi possível conectar à base de dados: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Executes a callback within a database transaction.
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
