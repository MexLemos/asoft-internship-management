<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Intern;
use App\Models\InternStatusHistory;
use App\Models\Notification;
use App\Models\User;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class InternLifecycleService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_AWAITING_COMPLETION = 'awaiting_completion';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DROPPED_OUT = 'dropped_out';
    public const STATUS_TERMINATED_ANOMALOUS = 'terminated_anomalous';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pendente de Início',
        self::STATUS_ACTIVE => 'Ativo / Em Curso',
        self::STATUS_SUSPENDED => 'Suspenso Temporariamente',
        self::STATUS_AWAITING_COMPLETION => 'Aguardando Homologação',
        self::STATUS_COMPLETED => 'Concluído com Sucesso',
        self::STATUS_DROPPED_OUT => 'Desistência / Abandono',
        self::STATUS_TERMINATED_ANOMALOUS => 'Rescisão por Falta Grave / Anómala',
        self::STATUS_CANCELLED => 'Cancelado'
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => 'bg-warning text-dark',
        self::STATUS_ACTIVE => 'bg-success',
        self::STATUS_SUSPENDED => 'bg-secondary',
        self::STATUS_AWAITING_COMPLETION => 'bg-info text-dark',
        self::STATUS_COMPLETED => 'bg-primary',
        self::STATUS_DROPPED_OUT => 'bg-dark',
        self::STATUS_TERMINATED_ANOMALOUS => 'bg-danger',
        self::STATUS_CANCELLED => 'bg-danger'
    ];

    /**
     * Matriz de transições permitidas (State Transition Guard).
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [
            self::STATUS_ACTIVE,
            self::STATUS_DROPPED_OUT,
            self::STATUS_TERMINATED_ANOMALOUS,
            self::STATUS_CANCELLED
        ],
        self::STATUS_ACTIVE => [
            self::STATUS_SUSPENDED,
            self::STATUS_AWAITING_COMPLETION,
            self::STATUS_COMPLETED,
            self::STATUS_DROPPED_OUT,
            self::STATUS_TERMINATED_ANOMALOUS
        ],
        self::STATUS_SUSPENDED => [
            self::STATUS_ACTIVE,
            self::STATUS_DROPPED_OUT,
            self::STATUS_TERMINATED_ANOMALOUS
        ],
        self::STATUS_AWAITING_COMPLETION => [
            self::STATUS_COMPLETED,
            self::STATUS_ACTIVE, // Prorrogação ou ajuste contratual
            self::STATUS_DROPPED_OUT,
            self::STATUS_TERMINATED_ANOMALOUS
        ],
        self::STATUS_COMPLETED => [
            self::STATUS_ACTIVE // Reabertura excecional por SuperAdmin
        ],
        self::STATUS_DROPPED_OUT => [
            self::STATUS_ACTIVE // Reativação excecional por SuperAdmin
        ],
        self::STATUS_TERMINATED_ANOMALOUS => [], // Estado estritamente terminal
        self::STATUS_CANCELLED => [
            self::STATUS_ACTIVE
        ]
    ];

    /**
     * Verifica se uma transição é teoricamente válida na máquina de estados.
     */
    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return false;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$fromStatus] ?? [];
        return in_array($toStatus, $allowed, true);
    }

    /**
     * Retorna a lista de próximos estados possíveis para um estagiário.
     */
    public static function getAvailableTransitions(string $currentStatus): array
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
    }

    /**
     * Verifica se um conjunto de papéis tem permissão para a transição.
     */
    public static function isUserAuthorizedForTransition(string $fromStatus, string $toStatus, array $roles): bool
    {
        $isSuperAdmin = in_array('super_admin', $roles, true);
        $isAdmin = in_array('admin', $roles, true);
        $isSupervisor = in_array('supervisor', $roles, true);

        if ($isSuperAdmin) {
            return true;
        }

        // Reabertura de estados terminais exige Super Administrador
        if (in_array($fromStatus, [self::STATUS_COMPLETED, self::STATUS_DROPPED_OUT, self::STATUS_CANCELLED], true)) {
            return false;
        }

        // Rescisão anómala ou desistência exige no mínimo Admin
        if (in_array($toStatus, [self::STATUS_TERMINATED_ANOMALOUS, self::STATUS_DROPPED_OUT], true)) {
            return $isAdmin;
        }

        // Conclusão formal com emissão exige Admin ou SuperAdmin
        if ($toStatus === self::STATUS_COMPLETED) {
            return $isAdmin;
        }

        // Supervisores podem solicitar transição para aguardando conclusão ou suspensão preventiva
        if ($isSupervisor) {
            return in_array($toStatus, [self::STATUS_AWAITING_COMPLETION, self::STATUS_SUSPENDED], true);
        }

        return $isAdmin;
    }

    /**
     * Executa a transição de estado de forma atómica com auditoria e efeitos colaterais.
     */
    public function transition(
        int $internId,
        string $newStatus,
        int $changedByUserId,
        string $reason,
        array $metadata = []
    ): array {
        $reason = trim($reason);
        if (empty($reason)) {
            throw new InvalidArgumentException('É obrigatório indicar uma justificação detalhada para a alteração de estado do estagiário.');
        }

        $intern = Intern::findById($internId);
        if (!$intern) {
            throw new RuntimeException("Estagiário ID {$internId} não encontrado.");
        }

        $currentStatus = $intern['status'] ?? self::STATUS_PENDING;

        if (!self::canTransition($currentStatus, $newStatus)) {
            $fromLabel = self::STATUS_LABELS[$currentStatus] ?? $currentStatus;
            $toLabel = self::STATUS_LABELS[$newStatus] ?? $newStatus;
            throw new RuntimeException("Transição inválida na máquina de estados: não é permitido transitar de '{$fromLabel}' para '{$toLabel}'.");
        }

        $changer = User::findById($changedByUserId);
        $changerRoles = $changer ? ($changer['roles'] ?? User::getUserRoles($changedByUserId)) : [];
        if (!self::isUserAuthorizedForTransition($currentStatus, $newStatus, $changerRoles)) {
            throw new RuntimeException("Não possui autorização de segurança suficiente para executar esta transição de estado.");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Atualizar registro do estagiário
            $completionDate = null;
            $exitReason = null;

            if ($newStatus === self::STATUS_COMPLETED) {
                $completionDate = date('Y-m-d');
            } elseif (in_array($newStatus, [self::STATUS_DROPPED_OUT, self::STATUS_TERMINATED_ANOMALOUS], true)) {
                $completionDate = date('Y-m-d');
                $exitReason = $reason;
            }

            $stmtIntern = $pdo->prepare("
                UPDATE interns SET
                    status = ?,
                    status_reason = ?,
                    completion_date = COALESCE(?, completion_date),
                    exit_reason = COALESCE(?, exit_reason),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmtIntern->execute([$newStatus, $reason, $completionDate, $exitReason, $internId]);

            // 2. Sincronizar o estado da conta de utilizador
            $internUserId = (int)$intern['user_id'];
            if (in_array($newStatus, [self::STATUS_DROPPED_OUT, self::STATUS_TERMINATED_ANOMALOUS, self::STATUS_SUSPENDED], true)) {
                // Bloqueia autenticação operacional
                $stmtUser = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
                $stmtUser->execute([$internUserId]);
            } elseif ($newStatus === self::STATUS_COMPLETED) {
                // Mantém ativo em modo Alumni (leitura de histórico/certificados)
                $stmtUser = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmtUser->execute([$internUserId]);
            } elseif ($newStatus === self::STATUS_ACTIVE) {
                // Restaura acesso ativo
                $stmtUser = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmtUser->execute([$internUserId]);
            }

            // 3. Registar no histórico de ciclo de vida
            InternStatusHistory::log(
                $internId,
                $currentStatus,
                $newStatus,
                $changedByUserId,
                $reason,
                $metadata
            );

            // 4. Registar no log de auditoria global
            AuditLog::log('intern_status_transition', 'interns', $internId, [
                'status' => $currentStatus
            ], [
                'status' => $newStatus,
                'reason' => $reason
            ], 'success');

            // 5. Notificar o estagiário sobre a alteração
            $newStatusLabel = self::STATUS_LABELS[$newStatus] ?? $newStatus;
            Notification::create(
                $internUserId,
                'system',
                "Atualização de Estado de Estágio: {$newStatusLabel}",
                "O estado do seu estágio foi alterado para '{$newStatusLabel}'. Motivo: {$reason}",
                '/intern/dashboard'
            );

            $pdo->commit();

            return [
                'success' => true,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'message' => "Estado do estagiário alterado com sucesso para '{$newStatusLabel}'."
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Rotina automática para verificar contratos expirados (end_date < hoje).
     * Transita de 'active' para 'awaiting_completion'.
     */
    public function autoCheckExpiringInternships(): array
    {
        $pdo = Database::getConnection();
        $today = date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT id, user_id, full_name, end_date, supervisor_id
            FROM interns
            WHERE status = 'active' AND end_date < ?
        ");
        $stmt->execute([$today]);
        $expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $transitioned = [];

        // Obtém utilizador do sistema (super_admin de ID 1 ou admin padrão)
        $systemUser = $pdo->query("
            SELECT u.id FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE r.name = 'super_admin' LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        $systemUserId = $systemUser ? (int)$systemUser['id'] : 1;

        foreach ($expired as $row) {
            $internId = (int)$row['id'];
            $reason = "Data prevista de término do estágio ({$row['end_date']}) foi atingida. Transição automática para aguardar homologação final.";

            try {
                $this->transition(
                    $internId,
                    self::STATUS_AWAITING_COMPLETION,
                    $systemUserId,
                    $reason,
                    ['automated' => true, 'trigger' => 'end_date_expired']
                );

                $transitioned[] = [
                    'intern_id' => $internId,
                    'full_name' => $row['full_name'],
                    'end_date' => $row['end_date']
                ];

                // Notificar supervisor responsável se existir
                if (!empty($row['supervisor_id'])) {
                    Notification::create(
                        (int)$row['supervisor_id'],
                        'system',
                        "Estágio Finalizado: {$row['full_name']}",
                        "O estágio de {$row['full_name']} atingiu a data de término e aguarda a sua homologação e avaliação final.",
                        "/supervisor/interns/{$internId}"
                    );
                }
            } catch (\Throwable $e) {
                // Continua processando os demais
            }
        }

        return $transitioned;
    }
}
