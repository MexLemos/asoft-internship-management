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
            SELECT i.*, 
                   COUNT(DISTINCT intn.id) as total_interns,
                   u.id as institution_user_id,
                   u.username as institution_username,
                   u.status as user_status
            FROM institutions i
            LEFT JOIN interns intn ON intn.institution_id = i.id AND intn.deleted_at IS NULL
            LEFT JOIN institution_users iu ON iu.institution_id = i.id
            LEFT JOIN users u ON u.id = iu.user_id AND u.deleted_at IS NULL
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
        $pdo->beginTransaction();
        try {
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
            $institutionId = (int)$pdo->lastInsertId();

            // Criação automática do utilizador com perfil "5 institution"
            $email = trim((string)($data['email'] ?? ''));
            if ($email !== '') {
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $check->execute([$email, $email]);
                $existingUserId = $check->fetchColumn();

                if ($existingUserId) {
                    $userId = (int)$existingUserId;
                } else {
                    $userName = !empty($data['contact_person']) ? trim((string)$data['contact_person']) : trim((string)$data['name']);
                    $passwordHash = password_hash('123EstagioAsoft', PASSWORD_BCRYPT);
                    $stmtUser = $pdo->prepare("
                        INSERT INTO users (name, email, phone, username, password_hash, status)
                        VALUES (?, ?, ?, ?, ?, 'active')
                    ");
                    $stmtUser->execute([
                        $userName,
                        $email,
                        $data['phone'] ?? null,
                        $email, // Username para login passa a ser o email
                        $passwordHash
                    ]);
                    $userId = (int)$pdo->lastInsertId();
                }

                // Role 5: institution
                $stmtRole = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, 5)");
                $stmtRole->execute([$userId]);

                // Vínculo na tabela institution_users
                $stmtInstUser = $pdo->prepare("INSERT IGNORE INTO institution_users (institution_id, user_id) VALUES (?, ?)");
                $stmtInstUser->execute([$institutionId, $userId]);
            }

            $pdo->commit();
            return $institutionId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
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

    /**
     * Sincroniza e cria utilizadores institucionais para instituições existentes que ainda não possuam conta.
     */
    public static function syncMissingInstitutionUsers(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT i.* 
            FROM institutions i
            LEFT JOIN institution_users iu ON iu.institution_id = i.id
            WHERE i.deleted_at IS NULL 
              AND i.email IS NOT NULL 
              AND i.email != ''
              AND iu.user_id IS NULL
        ");
        $institutions = $stmt->fetchAll();

        $created = [];
        $passwordHash = password_hash('123EstagioAsoft', PASSWORD_BCRYPT);

        foreach ($institutions as $inst) {
            $email = trim($inst['email']);
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $check->execute([$email, $email]);
            $existingUserId = $check->fetchColumn();

            if ($existingUserId) {
                $userId = (int)$existingUserId;
            } else {
                $name = !empty($inst['contact_person']) ? $inst['contact_person'] : $inst['name'];
                $ins = $pdo->prepare("
                    INSERT INTO users (name, email, phone, username, password_hash, status)
                    VALUES (?, ?, ?, ?, ?, 'active')
                ");
                $ins->execute([
                    $name,
                    $email,
                    $inst['phone'] ?? null,
                    $email,
                    $passwordHash
                ]);
                $userId = (int)$pdo->lastInsertId();
            }

            // Atribui Role 5 (institution)
            $stmtRole = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, 5)");
            $stmtRole->execute([$userId]);

            // Associa em institution_users
            $stmtIU = $pdo->prepare("INSERT IGNORE INTO institution_users (institution_id, user_id) VALUES (?, ?)");
            $stmtIU->execute([(int)$inst['id'], $userId]);

            $created[] = [
                'institution' => $inst['name'],
                'email' => $email,
                'user_id' => $userId
            ];
        }

        return $created;
    }
}
