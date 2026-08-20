<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Intern;
use App\Models\SystemSetting;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CertificateGeneratorService
{
    public function checkEligibility(int $internId): array
    {
        $pdo = Database::getConnection();
        $intern = Intern::findById($internId);
        if (!$intern) {
            return [
                'eligible' => false, 
                'reasons' => ['Estagiário não encontrado.'],
                'checklist' => []
            ];
        }

        $minAttendance = (int)SystemSetting::get('min_attendance_percentage', 80);
        $minGrade = (int)SystemSetting::get('min_passing_grade', 60);

        // 1. Attendance calculation
        $attStats = Attendance::getStats($internId);
        $totalDays = $attStats['present_count'] + $attStats['absent_count'];
        $attPercentage = ($totalDays > 0) ? ($attStats['present_count'] / $totalDays) * 100 : 100;
        $hoursWorked = (float)($attStats['total_hours_worked'] ?? 0);
        $requiredHours = (float)($intern['total_required_hours'] ?? 300);

        // 2. Period check
        $periodCompleted = strtotime($intern['end_date']) <= time();

        // 3. Hours check
        $hoursCompleted = $hoursWorked >= ($requiredHours * 0.85); // at least 85% of hours or full

        // 4. Attendance check
        $attendanceOk = $attPercentage >= $minAttendance;

        // 5. Tasks check
        $stmtTasks = $pdo->prepare("
            SELECT COUNT(*) as pending_count 
            FROM task_assignments 
            WHERE intern_id = ? AND status IN ('assigned', 'in_progress', 'reopened', 'rejected')
        ");
        $stmtTasks->execute([$internId]);
        $pendingTasksCount = (int)$stmtTasks->fetchColumn();
        $tasksOk = ($pendingTasksCount === 0);

        // 6. Mandatory Lessons & Tests check
        $stmtLessons = $pdo->prepare("
            SELECT COUNT(*) as pending_lessons
            FROM learning_contents lc
            INNER JOIN lessons l ON l.id = lc.lesson_id
            INNER JOIN modules m ON m.id = l.module_id
            INNER JOIN courses c ON c.id = m.course_id
            WHERE c.is_mandatory = 1 
            AND lc.id NOT IN (
                SELECT content_id FROM lesson_progress WHERE intern_id = ? AND status = 'completed'
            )
        ");
        $stmtLessons->execute([$internId]);
        $pendingLessons = (int)$stmtLessons->fetchColumn();
        $lessonsOk = ($pendingLessons === 0);

        // 7. Overall Grade check
        $gradeOk = (float)$intern['overall_score'] >= $minGrade;

        $checklist = [
            'period' => [
                'label' => 'Período de estágio concluído',
                'status' => $periodCompleted,
                'details' => $periodCompleted ? 'Data limite atingida (' . date('d/m/Y', strtotime($intern['end_date'])) . ')' : 'Estágio em andamento até ' . date('d/m/Y', strtotime($intern['end_date']))
            ],
            'hours' => [
                'label' => 'Carga horária mínima cumprida',
                'status' => $hoursCompleted,
                'details' => number_format($hoursWorked, 1) . 'h trabalhadas de ' . number_format($requiredHours, 0) . 'h previstas'
            ],
            'attendance' => [
                'label' => 'Presença mínima atingida',
                'status' => $attendanceOk,
                'details' => round($attPercentage, 1) . '% de presença (Mínimo exigido: ' . $minAttendance . '%)'
            ],
            'tasks' => [
                'label' => 'Tarefas práticas concluídas e aprovadas',
                'status' => $tasksOk,
                'details' => $tasksOk ? 'Todas as tarefas atribuídas foram aprovadas' : $pendingTasksCount . ' tarefa(s) pendente(s) ou reprovada(s)'
            ],
            'academy' => [
                'label' => 'Trilha de cursos e testes obrigatórios',
                'status' => $lessonsOk,
                'details' => $lessonsOk ? 'Aulas e avaliações obrigatórias finalizadas' : $pendingLessons . ' aula(s) obrigatória(s) pendente(s)'
            ],
            'performance' => [
                'label' => 'Média geral de aproveitamento',
                'status' => $gradeOk,
                'details' => number_format((float)$intern['overall_score'], 1) . ' / 100 valores (Mínimo: ' . $minGrade . ' valores)'
            ]
        ];

        $reasons = [];
        foreach ($checklist as $item) {
            if (!$item['status']) {
                $reasons[] = $item['label'] . ' (' . $item['details'] . ')';
            }
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
            'checklist' => $checklist,
            'attendance_percentage' => round($attPercentage, 1),
            'overall_score' => (float)$intern['overall_score']
        ];
    }

    public function generateCertificate(int $internId, string $signatoryName = 'Direcção Geral Asoftmedia', string $signatoryRole = 'Director Geral'): array
    {
        $eligibility = $this->checkEligibility($internId);
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => 'Estagiário não cumpre os requisitos mínimos para emissão de certificado: ' . implode(' | ', $eligibility['reasons'])
            ];
        }

        $intern = Intern::findById($internId);
        $totalHours = (float)($intern['total_required_hours'] ?? 300.00);
        $finalScore = (float)$intern['overall_score'];

        $cert = Certificate::issue($internId, $totalHours, $finalScore, $signatoryName, $signatoryRole);

        // Generate QR Code data URL
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        $validationUrl = "{$baseUrl}/validar/{$cert['validation_hash']}";

        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
        ]);
        $qrcode = (new QRCode($qrOptions))->render($validationUrl);

        return [
            'success' => true,
            'certificate' => $cert,
            'validation_url' => $validationUrl,
            'qr_code_base64' => $qrcode
        ];
    }
}
