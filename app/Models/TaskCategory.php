<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class TaskCategory
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM task_categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
