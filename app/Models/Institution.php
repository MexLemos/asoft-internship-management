<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Institution
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT i.*, COUNT(DISTINCT intn.id) as total_interns
            FROM institutions i
            LEFT JOIN interns intn ON intn.institution_id = i.id AND intn.deleted_at IS NULL
            WHERE i.deleted_at IS NULL
            GROUP BY i.id
            ORDER BY i.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM institutions WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO institutions (name, type, nif, email, phone, website, address, city, province, contact_person, contact_role, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['type'] ?? 'instituto_medio',
            $data['nif'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['website'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? 'Luanda',
            $data['province'] ?? 'Luanda',
            $data['contact_person'] ?? null,
            $data['contact_role'] ?? null,
            $data['status'] ?? 'active',
            $data['notes'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE institutions
            SET name = ?, type = ?, nif = ?, email = ?, phone = ?, website = ?, address = ?,
                city = ?, province = ?, contact_person = ?, contact_role = ?, status = ?, notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['type'] ?? 'instituto_medio',
            $data['nif'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['website'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? 'Luanda',
            $data['province'] ?? 'Luanda',
            $data['contact_person'] ?? null,
            $data['contact_role'] ?? null,
            $data['status'] ?? 'active',
            $data['notes'] ?? null,
            $id
        ]);
    }
}
