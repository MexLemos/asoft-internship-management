<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Certificate
{
    public static function findByInternId(int $internId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE intern_id = ? LIMIT 1");
        $stmt->execute([$internId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByValidationHash(string $hash): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT cert.*, i.full_name as intern_name, i.course, i.internship_area, i.start_date, i.end_date,
                   inst.name as institution_name
            FROM certificates cert
            INNER JOIN interns i ON i.id = cert.intern_id
            INNER JOIN institutions inst ON inst.id = i.institution_id
            WHERE cert.validation_hash = ? OR cert.certificate_code = ?
            LIMIT 1
        ");
        $stmt->execute([$hash, $hash]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function issue(int $internId, float $totalHours, float $finalScore, string $signatoryName, string $signatoryRole): array
    {
        $pdo = Database::getConnection();
        
        $code = 'AST-CERT-' . strtoupper(bin2hex(random_bytes(4)));
        $hash = hash('sha256', $code . '_' . $internId . '_' . time());

        $stmt = $pdo->prepare("
            INSERT INTO certificates (
                intern_id, certificate_code, validation_hash, total_hours_completed,
                final_score, issue_date, completion_date, signatory_name, signatory_role, status
            ) VALUES (
                ?, ?, ?, ?,
                ?, CURDATE(), CURDATE(), ?, ?, 'valid'
            )
            ON DUPLICATE KEY UPDATE
                total_hours_completed = VALUES(total_hours_completed),
                final_score = VALUES(final_score),
                issue_date = CURDATE(),
                status = 'valid'
        ");

        $stmt->execute([
            $internId,
            $code,
            $hash,
            $totalHours,
            $finalScore,
            $signatoryName,
            $signatoryRole
        ]);

        return self::findByInternId($internId);
    }

    public static function logValidation(int $certificateId, ?string $ip, ?string $userAgent): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO certificate_logs (certificate_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$certificateId, $ip, $userAgent]);
    }
}
