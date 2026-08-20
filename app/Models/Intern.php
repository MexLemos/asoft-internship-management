<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Intern
{
    /**
     * Calculates automatic end date: start date + 3 months, then adjusted forward to next Friday.
     */
    public static function calculateEndDate(string $startDate): string
    {
        $startTs = strtotime($startDate);
        if (!$startTs) {
            $startTs = time();
        }
        
        // Add 3 months
        $threeMonthsLater = strtotime('+3 months', $startTs);
        
        // Check day of week (1=Monday, 5=Friday, 7=Sunday)
        $dow = (int)date('N', $threeMonthsLater);
        if ($dow === 5) {
            return date('Y-m-d', $threeMonthsLater);
        }

        // Advance to next Friday
        $nextFriday = strtotime('next Friday', $threeMonthsLater);
        return date('Y-m-d', $nextFriday);
    }

    public static function paginate(
        int $page = 1, 
        int $perPage = 10, 
        string $search = '', 
        string $statusFilter = '', 
        string $sortBy = 'created_at', 
        string $sortDir = 'DESC',
        ?int $supervisorId = null,
        ?int $institutionId = null
    ): array {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $perPage;

        // Allowed sort columns
        $allowedSorts = [
            'name' => 'i.full_name',
            'course' => 'i.course',
            'institution' => 'inst.name',
            'start_date' => 'i.start_date',
            'end_date' => 'i.end_date',
            'status' => 'i.status',
            'created_at' => 'i.id'
        ];
        $orderColumn = $allowedSorts[$sortBy] ?? 'i.id';
        $orderDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $where = ["i.deleted_at IS NULL"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(i.full_name LIKE ? OR u.email LIKE ? OR i.course LIKE ? OR inst.name LIKE ? OR i.internship_area LIKE ? OR i.internship_code LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if (!empty($statusFilter)) {
            $where[] = "i.status = ?";
            $params[] = $statusFilter;
        }

        if ($supervisorId !== null) {
            $where[] = "i.supervisor_id = ?";
            $params[] = $supervisorId;
        }

        if ($institutionId !== null) {
            $where[] = "i.institution_id = ?";
            $params[] = $institutionId;
        }

        $whereClause = implode(" AND ", $where);

        // Count total
        $countSql = "
            SELECT COUNT(*) 
            FROM interns i
            INNER JOIN institutions inst ON inst.id = i.institution_id
            INNER JOIN users u ON u.id = i.user_id
            WHERE {$whereClause}
        ";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = (int)$stmtCount->fetchColumn();

        // Query records
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
            WHERE {$whereClause}
            ORDER BY {$orderColumn} {$orderDir}
            LIMIT ? OFFSET ?
        ";

        $stmt = $pdo->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $p) {
            $stmt->bindValue($paramIndex++, $p);
        }
        $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll();

        // Calculate dynamic status indicators for completion
        foreach ($records as &$rec) {
            $isPastEnd = strtotime($rec['end_date']) < time();
            $isNearEnd = !$isPastEnd && (strtotime($rec['end_date']) <= strtotime('+15 days'));
            
            if ($rec['status'] === 'completed') {
                $rec['display_badge'] = ['label' => 'Concluído', 'class' => 'bg-primary', 'icon' => 'bi-mortarboard-fill'];
            } elseif ($rec['status'] === 'suspended') {
                $rec['display_badge'] = ['label' => 'Suspenso', 'class' => 'bg-danger', 'icon' => 'bi-pause-circle'];
            } elseif ($isNearEnd) {
                $rec['display_badge'] = ['label' => 'Próximo do Fim', 'class' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'];
            } elseif ($isPastEnd && (float)$rec['overall_score'] >= 60.0) {
                $rec['display_badge'] = ['label' => 'Finalizado', 'class' => 'bg-info text-dark', 'icon' => 'bi-check2-all'];
            } else {
                $rec['display_badge'] = ['label' => 'Ativo', 'class' => 'bg-success', 'icon' => 'bi-play-circle'];
            }
        }

        return [
            'data' => $records,
            'total' => $totalRecords,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($totalRecords / $perPage),
            'search' => $search,
            'status_filter' => $statusFilter,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir
        ];
    }

    public static function all(?int $supervisorId = null, ?int $institutionId = null): array
    {
        $res = self::paginate(1, 1000, '', '', 'created_at', 'DESC', $supervisorId, $institutionId);
        return $res['data'];
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

        // Calculate end date automatically: start_date + 3 months -> adjusted to next Friday
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $endDate = self::calculateEndDate($startDate);

        $course = $data['course'] ?? 'Técnico de Informática';
        $customCourse = ($course === 'Outro') ? ($data['custom_course_name'] ?? null) : null;

        $stmt = $pdo->prepare("
            INSERT INTO interns (
                user_id, institution_id, supervisor_id, internship_code, full_name, social_name,
                birth_date, gender, bi_number, bi_issue_date, bi_expiry_date, photo, phone,
                emergency_phone, address, city, province, course, custom_course_name, formation_level, education_area, academic_year,
                student_number, academic_advisor, internship_area, start_date, end_date, status, overall_score, risk_level
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
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
            $course,
            $customCourse,
            $data['formation_level'] ?? '13ª',
            $data['education_area'] ?? null,
            $data['academic_year'] ?? null,
            $data['student_number'] ?? null,
            $data['academic_advisor'] ?? null,
            $data['internship_area'] ?? 'Geral',
            $startDate,
            $endDate,
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
}
