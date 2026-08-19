<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Intern
{
    public static function all(?int $supervisorId = null, ?int $institutionId = null): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT i.*, 
                   inst.name as institution_name,
                   sup.name as supervisor_name,
                   u.email as user_email,
                   u.username as user_username,
                   u.status as account_status,
                   s.expected_start_time,
                   s.expected_end_time,
                   s.daily_hours,
                   s.total_required_hours,
                   (SELECT COUNT(*) FROM attendance a WHERE a.intern_id = i.id AND a.status = 'present') as days_present,
                   (SELECT COUNT(*) FROM attendance a WHERE a.intern_id = i.id AND a.status = 'absent') as days_absent,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.intern_id = i.id AND ta.status = 'approved') as tasks_completed,
                   (SELECT COUNT(*) FROM task_assignments ta WHERE ta.intern_id = i.id AND ta.status IN ('assigned', 'in_progress', 'reopened')) as tasks_pending
            FROM interns i
            INNER JOIN institutions inst ON inst.id = i.institution_id
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN users sup ON sup.id = i.supervisor_id
            LEFT JOIN intern_schedules s ON s.intern_id = i.id
            WHERE i.deleted_at IS NULL
        ";

        $params = [];
        if ($supervisorId !== null) {
            $sql .= " AND i.supervisor_id = ?";
            $params[] = $supervisorId;
        }
        if ($institutionId !== null) {
            $sql .= " AND i.institution_id = ?";
            $params[] = $institutionId;
        }

        $sql .= " ORDER BY i.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT i.*, 
                   inst.name as institution_name,
                   inst.nif as institution_nif,
                   sup.name as supervisor_name,
                   u.email as user_email,
                   u.username as user_username,
                   u.status as account_status,
                   s.expected_start_time,
                   s.expected_end_time,
                   s.tolerance_minutes,
                   s.daily_hours,
                   s.total_required_hours
            FROM interns i
            INNER JOIN institutions inst ON inst.id = i.institution_id
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN users sup ON sup.id = i.supervisor_id
            LEFT JOIN intern_schedules s ON s.intern_id = i.id
            WHERE i.id = ? AND i.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $intern = $stmt->fetch();
        if ($intern) {
            $intern['schedule_days'] = self::getScheduleDays((int)$intern['id']);
        }
        return $intern ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM interns WHERE user_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id ? self::findById((int)$id) : null;
    }

    public static function getScheduleDays(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT isd.day_of_week, isd.is_active
            FROM intern_schedule_days isd
            INNER JOIN intern_schedules sch ON sch.id = isd.intern_schedule_id
            WHERE sch.intern_id = ?
            ORDER BY isd.day_of_week ASC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO interns (
                user_id, institution_id, supervisor_id, internship_code, full_name, social_name,
                birth_date, gender, bi_number, bi_issue_date, bi_expiry_date, photo, phone,
                emergency_phone, address, city, province, course, education_area, academic_year,
                student_number, academic_advisor, internship_area, start_date, end_date, status, overall_score, risk_level
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $data['user_id'],
            $data['institution_id'],
            $data['supervisor_id'] ?? null,
            $data['internship_code'],
            $data['full_name'],
            $data['social_name'] ?? null,
            $data['birth_date'] ?? null,
            $data['gender'] ?? 'M',
            $data['bi_number'],
            $data['bi_issue_date'] ?? null,
            $data['bi_expiry_date'] ?? null,
            $data['photo'] ?? null,
            $data['phone'] ?? null,
            $data['emergency_phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? 'Luanda',
            $data['province'] ?? 'Luanda',
            $data['course'],
            $data['education_area'] ?? null,
            $data['academic_year'] ?? null,
            $data['student_number'] ?? null,
            $data['academic_advisor'] ?? null,
            $data['internship_area'] ?? 'Desenvolvimento de Software',
            $data['start_date'],
            $data['end_date'],
            $data['status'] ?? 'active',
            $data['overall_score'] ?? 0.00,
            $data['risk_level'] ?? 'normal'
        ]);

        $internId = (int)$pdo->lastInsertId();

        // Create Schedule
        $stmtSched = $pdo->prepare("
            INSERT INTO intern_schedules (intern_id, expected_start_time, expected_end_time, tolerance_minutes, daily_hours, total_required_hours)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtSched->execute([
            $internId,
            $data['expected_start_time'] ?? '08:00:00',
            $data['expected_end_time'] ?? '12:00:00',
            $data['tolerance_minutes'] ?? 15,
            $data['daily_hours'] ?? 4.00,
            $data['total_required_hours'] ?? 300.00
        ]);
        $schedId = (int)$pdo->lastInsertId();

        // Create Days
        $days = $data['active_days'] ?? [1, 2, 4, 5];
        $stmtDays = $pdo->prepare("INSERT INTO intern_schedule_days (intern_schedule_id, day_of_week, is_active) VALUES (?, ?, ?)");
        for ($d = 1; $d <= 7; $d++) {
            $stmtDays->execute([$schedId, $d, in_array($d, $days, true) ? 1 : 0]);
        }

        return $internId;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE interns
            SET institution_id = ?, supervisor_id = ?, full_name = ?, social_name = ?,
                birth_date = ?, gender = ?, bi_number = ?, phone = ?, emergency_phone = ?,
                address = ?, course = ?, education_area = ?, academic_year = ?,
                internship_area = ?, start_date = ?, end_date = ?, status = ?, risk_level = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['institution_id'],
            $data['supervisor_id'] ?? null,
            $data['full_name'],
            $data['social_name'] ?? null,
            $data['birth_date'] ?? null,
            $data['gender'] ?? 'M',
            $data['bi_number'],
            $data['phone'] ?? null,
            $data['emergency_phone'] ?? null,
            $data['address'] ?? null,
            $data['course'],
            $data['education_area'] ?? null,
            $data['academic_year'] ?? null,
            $data['internship_area'] ?? 'Desenvolvimento de Software',
            $data['start_date'],
            $data['end_date'],
            $data['status'] ?? 'active',
            $data['risk_level'] ?? 'normal',
            $id
        ]);
    }
}
