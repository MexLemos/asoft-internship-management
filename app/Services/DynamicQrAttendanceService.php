<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use PDO;

class DynamicQrAttendanceService
{
    public const TOKEN_VALIDITY_SECONDS = 30;

    /**
     * Gera ou recupera o token QR dinâmico ativo para exibição no terminal da sede.
     */
    public function getCurrentTerminalToken(?int $generatedBy = null): array
    {
        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');

        // 1. Procurar token ainda válido com pelo menos 5 segundos de vida restante
        $stmt = $pdo->prepare("
            SELECT * FROM dynamic_attendance_tokens 
            WHERE expires_at > DATE_ADD(?, INTERVAL 5 SECOND)
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$now]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $secondsRemaining = max(0, strtotime($existing['expires_at']) - time());
            return [
                'token_hash' => $existing['token_hash'],
                'expires_at' => $existing['expires_at'],
                'seconds_remaining' => $secondsRemaining,
                'qr_data_url' => $this->generateQrImage($existing['token_hash'])
            ];
        }

        // 2. Gerar novo token rotativo TOTP
        $seed = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_VALIDITY_SECONDS);
        $tokenHash = hash('sha256', $seed . '|' . $expiresAt . '|' . ($generatedBy ?? 1));

        $insert = $pdo->prepare("
            INSERT INTO dynamic_attendance_tokens (token_hash, token_seed, generated_by, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $insert->execute([$tokenHash, $seed, $generatedBy, $expiresAt]);

        // Limpeza assíncrona de tokens expirados há mais de 1 hora
        $pdo->exec("DELETE FROM dynamic_attendance_tokens WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

        return [
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'seconds_remaining' => self::TOKEN_VALIDITY_SECONDS,
            'qr_data_url' => $this->generateQrImage($tokenHash)
        ];
    }

    /**
     * Valida e consome o token QR rotativo lido pelo estagiário.
     */
    public function validateAndRedeem(string $tokenHash, int $internId): array
    {
        $tokenHash = trim($tokenHash);
        if (empty($tokenHash)) {
            return [
                'valid' => false,
                'message' => 'Código QR dinâmico não fornecido.'
            ];
        }

        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');

        // 1. Procurar token válido e não expirado
        $stmt = $pdo->prepare("
            SELECT * FROM dynamic_attendance_tokens 
            WHERE token_hash = ? AND expires_at >= ?
            LIMIT 1
        ");
        $stmt->execute([$tokenHash, $now]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return [
                'valid' => false,
                'message' => 'O Código QR lido expirou ou é inválido. Aponte a câmara para o monitor do terminal na sede para ler o código atual.'
            ];
        }

        $tokenId = (int)$token['id'];

        // 2. Verificar se este estagiário já consumiu este mesmo token
        $stmtCheck = $pdo->prepare("
            SELECT id FROM dynamic_token_redemptions 
            WHERE token_id = ? AND intern_id = ?
        ");
        $stmtCheck->execute([$tokenId, $internId]);
        if ($stmtCheck->fetchColumn()) {
            return [
                'valid' => false,
                'message' => 'Este código QR já foi utilizado na sua marcação.'
            ];
        }

        // 3. Registar o resgate único
        $stmtRedeem = $pdo->prepare("
            INSERT INTO dynamic_token_redemptions (token_id, intern_id) 
            VALUES (?, ?)
        ");
        $stmtRedeem->execute([$tokenId, $internId]);

        return [
            'valid' => true,
            'token_id' => $tokenId,
            'message' => 'Presença física no terminal da Asoftmedia comprovada com sucesso via QR Code rotativo!'
        ];
    }

    /**
     * Renderiza o QR Code em Base64 Data URL.
     */
    private function generateQrImage(string $payload): string
    {
        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 8,
            'imageBase64' => true
        ]);

        return (new QRCode($qrOptions))->render($payload);
    }
}
