<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SystemSetting
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT setting_value, data_type FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (!$row) {
            return $default;
        }

        $val = match ($row['data_type']) {
            'int' => (int)$row['setting_value'],
            'float' => (float)$row['setting_value'],
            'boolean' => filter_var($row['setting_value'], FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($row['setting_value'], true),
            default => $row['setting_value']
        };

        self::$cache[$key] = $val;
        return $val;
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM system_settings ORDER BY group_name ASC, setting_key ASC");
        return $stmt->fetchAll();
    }

    public static function set(string $key, string $value): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $res = $stmt->execute([$value, $key]);
        unset(self::$cache[$key]);
        return $res;
    }
}
