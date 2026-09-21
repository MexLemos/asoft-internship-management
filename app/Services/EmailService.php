<?php

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    /**
     * Send an email notification formatted as HTML.
     * Logs output to storage/logs/mail.log for traceability and staging testing.
     */
    public static function send(string $toEmail, string $toName, string $subject, string $messageBody, ?string $actionUrl = null): bool
    {
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $appName = $_ENV['APP_NAME'] ?? 'Asoftmedia Internship Management System';
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'https://estagio.softmedia-ao.com', '/');
        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@softmedia-ao.com';
        $fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Asoftmedia AIMS';

        $fullActionUrl = $actionUrl;
        if ($fullActionUrl && !str_starts_with($fullActionUrl, 'http://') && !str_starts_with($fullActionUrl, 'https://')) {
            $fullActionUrl = $appUrl . '/' . ltrim($fullActionUrl, '/');
        }

        $buttonHtml = '';
        if ($fullActionUrl) {
            $buttonHtml = '
                <div style="margin: 25px 0; text-align: center;">
                    <a href="' . htmlspecialchars($fullActionUrl) . '" style="background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">
                        Ver Mensagem no Portal
                    </a>
                </div>';
        }

        $safeBody = nl2br(htmlspecialchars($messageBody));

        $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>{$subject}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #212529;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="background-color: #0d6efd; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700;">{$appName}</h2>
        </div>
        <div style="padding: 25px;">
            <p style="font-size: 16px; margin-top: 0;">Olá, <strong>{$toName}</strong>,</p>
            <div style="font-size: 15px; line-height: 1.6; color: #495057; background-color: #fdfdfd; border-left: 4px solid #0d6efd; padding: 12px 16px; margin: 15px 0;">
                {$safeBody}
            </div>
            {$buttonHtml}
            <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">
            <p style="font-size: 12px; color: #6c757d; margin-bottom: 0;">
                Esta é uma notificação automática enviada pelo sistema {$appName}. Por favor, não responda diretamente a este e-mail.
            </p>
        </div>
    </div>
</body>
</html>
HTML;

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($fromName), $fromEmail),
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $mailSent = false;
        try {
            $mailSent = @mail($toEmail, $encodedSubject, $htmlContent, implode("\r\n", $headers));
        } catch (\Throwable $e) {
            $mailSent = false;
        }

        self::logEmail($toEmail, $subject, $messageBody, (bool)$mailSent);

        return (bool)$mailSent;
    }

    private static function logEmail(string $to, string $subject, string $body, bool $sent): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $status = $sent ? 'SENT' : 'QUEUED/LOGGED';
        $entry = sprintf("[%s] [%s] TO: %s | SUBJECT: %s | BODY: %s\n", $timestamp, $status, $to, $subject, str_replace(["\r", "\n"], ' ', substr($body, 0, 150)));
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
