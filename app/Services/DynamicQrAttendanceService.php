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
            $activeHash = $existing['token_hash'];
            $activeExpiresAt = $existing['expires_at'];
            $activeSecondsRemaining = max(0, strtotime($existing['expires_at']) - time());
        } else {
            // Gerar novo token rotativo TOTP
            $seed = bin2hex(random_bytes(16));
            $activeExpiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_VALIDITY_SECONDS);
            $activeHash = hash('sha256', $seed . '|' . $activeExpiresAt . '|' . ($generatedBy ?? 1));
            $activeSecondsRemaining = self::TOKEN_VALIDITY_SECONDS;

            $insert = $pdo->prepare("
                INSERT INTO dynamic_attendance_tokens (token_hash, token_seed, generated_by, expires_at)
                VALUES (?, ?, ?, ?)
            ");
            $insert->execute([$activeHash, $seed, $generatedBy, $activeExpiresAt]);

            // Limpeza assíncrona de tokens expirados há mais de 1 hora
            $pdo->exec("DELETE FROM dynamic_attendance_tokens WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        }

        $scanUrl = $this->getScanUrl($activeHash);

        return [
            'token_hash' => $activeHash,
            'scan_url' => $scanUrl,
            'short_code' => strtoupper(substr($activeHash, 0, 6)),
            'expires_at' => $activeExpiresAt,
            'seconds_remaining' => $activeSecondsRemaining,
            'qr_data_url' => $this->generateQrImage($scanUrl)
        ];
    }

    /**
     * Constrói o URL de leitura móvel direto a partir do hash do token.
     */
    public function getScanUrl(string $tokenHash): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        if (empty($baseUrl) && isset($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        }
        if (empty($baseUrl)) {
            $baseUrl = 'https://estagio.softmedia-ao.com';
        }
        return $baseUrl . '/attendance/scan?token=' . urlencode($tokenHash);
    }

    /**
     * Valida se um token é autêntico e ainda está dentro da janela de validade (sem consumir).
     */
    public function validateOnly(string $tokenHash): array
    {
        $tokenHash = $this->extractTokenHash($tokenHash);
        if (empty($tokenHash)) {
            return [
                'valid' => false,
                'message' => 'Código QR não fornecido.'
            ];
        }

        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');

        $len = strlen($tokenHash);
        if ($len <= 8) {
            $stmt = $pdo->prepare("
                SELECT * FROM dynamic_attendance_tokens 
                WHERE UPPER(LEFT(token_hash, ?)) = UPPER(?) AND expires_at >= DATE_SUB(?, INTERVAL 20 SECOND)
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([$len, $tokenHash, $now]);
        } else {
            $stmt = $pdo->prepare("
                SELECT * FROM dynamic_attendance_tokens 
                WHERE token_hash = ? AND expires_at >= ?
                LIMIT 1
            ");
            $stmt->execute([$tokenHash, $now]);
        }
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return [
                'valid' => false,
                'message' => 'O código PIN/QR expirou ou é inválido. Aponte a câmara novamente ou verifique o PIN no monitor da sede.'
            ];
        }

        return [
            'valid' => true,
            'token' => $token,
            'message' => 'Código PIN/QR ativo e válido no terminal da sede.'
        ];
    }

    /**
     * Extrai o hash do token caso o payload lido seja um URL completo.
     */
    public function extractTokenHash(string $input): string
    {
        $input = trim($input);
        if (str_contains($input, 'token=')) {
            $queryStr = parse_url($input, PHP_URL_QUERY);
            if (!empty($queryStr)) {
                parse_str($queryStr, $params);
                if (!empty($params['token'])) {
                    return trim((string)$params['token']);
                }
            }
        }
        return $input;
    }

    /**
     * Valida e consome o token QR rotativo lido pelo estagiário.
     */
    public function validateAndRedeem(string $tokenHash, int $internId): array
    {
        $tokenHash = $this->extractTokenHash($tokenHash);
        if (empty($tokenHash)) {
            return [
                'valid' => false,
                'message' => 'Código PIN/QR dinâmico não fornecido.'
            ];
        }

        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');

        // 1. Procurar token válido (suporta hash completo ou PIN de 6 caracteres)
        $len = strlen($tokenHash);
        if ($len <= 8) {
            $stmt = $pdo->prepare("
                SELECT * FROM dynamic_attendance_tokens 
                WHERE UPPER(LEFT(token_hash, ?)) = UPPER(?) AND expires_at >= DATE_SUB(?, INTERVAL 20 SECOND)
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([$len, $tokenHash, $now]);
        } else {
            $stmt = $pdo->prepare("
                SELECT * FROM dynamic_attendance_tokens 
                WHERE token_hash = ? AND expires_at >= ?
                LIMIT 1
            ");
            $stmt->execute([$tokenHash, $now]);
        }
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return [
                'valid' => false,
                'message' => 'O Código PIN/QR expirou ou é inválido. Consulte o monitor do terminal na sede para obter o código atual.'
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
