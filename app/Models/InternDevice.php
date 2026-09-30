<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class InternDevice
{
    /**
     * Valida ou vincula o dispositivo do estagiário.
     * Impede que um estagiário marque presença em nome de outro ou use dispositivos não autorizados.
     */
    public static function validateOrRegister(
        int $internId,
        string $deviceUuid,
        ?string $deviceName = null,
        ?string $userAgent = null
    ): array {
        $deviceUuid = trim($deviceUuid);
        if (empty($deviceUuid)) {
            return [
                'valid' => false,
                'is_new' => false,
                'trusted' => false,
                'message' => 'Identificador de dispositivo ausente ou inválido.'
            ];
        }

        $pdo = Database::getConnection();

        // 1. Verificar dispositivos existentes deste estagiário
        $stmt = $pdo->prepare("SELECT * FROM intern_devices WHERE intern_id = ?");
        $stmt->execute([$internId]);
        $existingDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Se for o primeiro dispositivo do estagiário, vincula automaticamente como confiável
        if (empty($existingDevices)) {
            $insert = $pdo->prepare("
                INSERT INTO intern_devices (intern_id, device_uuid, device_name, user_agent, is_trusted)
                VALUES (?, ?, ?, ?, 1)
            ");
            $insert->execute([
                $internId,
                $deviceUuid,
                $deviceName ?: 'Dispositivo Principal',
                $userAgent
            ]);

            return [
                'valid' => true,
                'is_new' => true,
                'trusted' => true,
                'message' => 'Dispositivo vinculado com sucesso à sua conta.'
            ];
        }

        // 3. Se já existem dispositivos vinculados, verificar se o UUID atual corresponde
        foreach ($existingDevices as $dev) {
            if ($dev['device_uuid'] === $deviceUuid) {
                if (!(bool)$dev['is_trusted']) {
                    return [
                        'valid' => false,
                        'is_new' => false,
                        'trusted' => false,
                        'message' => 'Este dispositivo foi temporariamente bloqueado ou suspenso pela supervisão.'
                    ];
                }

                // Atualizar último uso
                $upd = $pdo->prepare("
                    UPDATE intern_devices 
                    SET last_used_at = CURRENT_TIMESTAMP, user_agent = COALESCE(?, user_agent) 
                    WHERE id = ?
                ");
                $upd->execute([$userAgent, (int)$dev['id']]);

                return [
                    'valid' => true,
                    'is_new' => false,
                    'trusted' => true,
                    'message' => 'Dispositivo reconhecido e confiável.'
                ];
            }
        }

        // 4. Tentativa com dispositivo não cadastrado para um estagiário já vinculado
        // Registar como não confiável (pendente) para auditoria e alertar
        $insPending = $pdo->prepare("
            INSERT INTO intern_devices (intern_id, device_uuid, device_name, user_agent, is_trusted)
            VALUES (?, ?, ?, ?, 0)
        ");
        $insPending->execute([
            $internId,
            $deviceUuid,
            $deviceName ?: 'Dispositivo Não Autorizado / Secundário',
            $userAgent
        ]);

        return [
            'valid' => false,
            'is_new' => true,
            'trusted' => false,
            'message' => 'A sua conta já está vinculada a outro dispositivo. Por motivos de segurança e prevenção de partilha de credenciais, o uso de um novo aparelho exige aprovação do supervisor.'
        ];
    }

    /**
     * Lista os dispositivos vinculados a um estagiário.
     */
    public static function getDevicesForIntern(int $internId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM intern_devices 
            WHERE intern_id = ? 
            ORDER BY is_trusted DESC, last_used_at DESC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Autoriza / Homologa um dispositivo.
     */
    public static function trustDevice(int $deviceId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE intern_devices SET is_trusted = 1 WHERE id = ?");
        return $stmt->execute([$deviceId]);
    }

    /**
     * Bloqueia um dispositivo.
     */
    public static function blockDevice(int $deviceId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE intern_devices SET is_trusted = 0 WHERE id = ?");
        return $stmt->execute([$deviceId]);
    }

    /**
     * Remove o vínculo de um dispositivo (permite revincular).
     */
    public static function removeDevice(int $deviceId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM intern_devices WHERE id = ?");
        return $stmt->execute([$deviceId]);
    }
}
