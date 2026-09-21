<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Institution;
use App\Models\Notification;
use App\Services\EmailService;

class MessagesController extends Controller
{
    private function ensureAttachmentColumns(): void
    {
        try {
            $pdo = Database::getConnection();
            $pdo->query("SELECT attachment_path FROM institution_messages LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo = Database::getConnection();
                $pdo->exec("
                    ALTER TABLE institution_messages
                    ADD COLUMN attachment_path VARCHAR(255) NULL AFTER message,
                    ADD COLUMN attachment_name VARCHAR(255) NULL AFTER attachment_path
                ");
            } catch (\Throwable $ignored) {}
        }
    }

    private function handleAttachmentUpload(): ?array
    {
        if (empty($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > 20 * 1024 * 1024) {
            return null;
        }

        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExts = [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx',
            'txt', 'rtf', 'odt', 'ods', 'odp',
            'zip', 'rar', '7z', 'tar', 'gz',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'
        ];

        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        $targetDir = dirname(__DIR__, 3) . '/public/uploads/messages/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $targetDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'path' => '/uploads/messages/' . $newFileName,
                'name' => $originalName
            ];
        }

        return null;
    }

    public function index(Request $request): Response
    {
        $this->ensureAttachmentColumns();
        $sessionUser = Session::get('user');
        $pdo = Database::getConnection();

        // Get all institutional conversations
        $stmtConv = $pdo->query("
            SELECT ic.*, inst.name as institution_name, u.name as creator_name,
                   (SELECT COUNT(*) FROM institution_messages im WHERE im.conversation_id = ic.id AND im.is_read = 0 AND im.sender_id != {$sessionUser['id']}) as unread_count,
                   (SELECT message FROM institution_messages im WHERE im.conversation_id = ic.id ORDER BY im.created_at DESC LIMIT 1) as last_message
            FROM institution_conversations ic
            INNER JOIN institutions inst ON inst.id = ic.institution_id
            INNER JOIN users u ON u.id = ic.created_by
            ORDER BY ic.last_message_at DESC
        ");
        $conversations = $stmtConv->fetchAll();

        $selectedConvId = (int)$request->input('conversation', ($conversations[0]['id'] ?? 0));
        $messages = [];
        $activeConversation = null;

        if ($selectedConvId > 0) {
            $stmtActive = $pdo->prepare("
                SELECT ic.*, inst.name as institution_name, u.name as creator_name
                FROM institution_conversations ic
                INNER JOIN institutions inst ON inst.id = ic.institution_id
                INNER JOIN users u ON u.id = ic.created_by
                WHERE ic.id = ?
            ");
            $stmtActive->execute([$selectedConvId]);
            $activeConversation = $stmtActive->fetch();

            if ($activeConversation) {
                // Mark as read
                $stmtRead = $pdo->prepare("UPDATE institution_messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?");
                $stmtRead->execute([$selectedConvId, (int)$sessionUser['id']]);

                // Load messages
                $stmtMsg = $pdo->prepare("
                    SELECT im.*, u.name as sender_name, u.email as sender_email
                    FROM institution_messages im
                    INNER JOIN users u ON u.id = im.sender_id
                    WHERE im.conversation_id = ?
                    ORDER BY im.created_at ASC
                ");
                $stmtMsg->execute([$selectedConvId]);
                $messages = $stmtMsg->fetchAll();
            }
        }

        return $this->render('admin.messages.index', [
            'title' => 'Mensagens das Instituições - Asoftmedia',
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages
        ], 'admin');
    }

    public function reply(Request $request, string $conversationId): Response
    {
        $this->ensureAttachmentColumns();
        $convId = (int)$conversationId;
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $message = trim((string)$request->input('message', ''));
        $attachment = $this->handleAttachmentUpload();

        if (empty($message) && !$attachment) {
            return $this->redirect("/admin/messages?conversation={$convId}");
        }

        if (empty($message) && $attachment) {
            $message = 'Envio de ficheiro anexo: ' . $attachment['name'];
        }

        $pdo = Database::getConnection();

        $stmtMsg = $pdo->prepare("
            INSERT INTO institution_messages (conversation_id, sender_id, message, attachment_path, attachment_name, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmtMsg->execute([$convId, $userId, $message, $attachment['path'] ?? null, $attachment['name'] ?? null]);

        $stmtUpd = $pdo->prepare("UPDATE institution_conversations SET last_message_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$convId]);

        // Notify Institution creator & associated users
        $stmtConv = $pdo->prepare("
            SELECT ic.created_by, ic.subject, ic.institution_id, i.name as institution_name
            FROM institution_conversations ic
            INNER JOIN institutions i ON i.id = ic.institution_id
            WHERE ic.id = ?
        ");
        $stmtConv->execute([$convId]);
        $conv = $stmtConv->fetch();

        if ($conv) {
            $stmtUsers = $pdo->prepare("
                SELECT DISTINCT u.id, u.name, u.email
                FROM users u
                LEFT JOIN institution_users iu ON iu.user_id = u.id AND iu.institution_id = ?
                WHERE (iu.institution_id = ? OR u.id = ?) AND u.deleted_at IS NULL AND u.status = 'active'
            ");
            $stmtUsers->execute([(int)$conv['institution_id'], (int)$conv['institution_id'], (int)$conv['created_by']]);
            $recipients = $stmtUsers->fetchAll();

            $notifTitle = "Nova Resposta da Administração Asoftmedia";
            $notifMsg = "A administração respondeu à conversa '{$conv['subject']}': \"{$message}\"";
            if ($attachment) {
                $notifMsg .= " (Anexo: {$attachment['name']})";
            }
            $actionUrl = "/institution/messages?conversation={$convId}";

            foreach ($recipients as $recipient) {
                Notification::create(
                    (int)$recipient['id'],
                    'info',
                    $notifTitle,
                    $notifMsg,
                    $actionUrl
                );
                EmailService::send(
                    $recipient['email'],
                    $recipient['name'],
                    $notifTitle,
                    $notifMsg,
                    $actionUrl
                );
            }
        }

        return $this->redirect("/admin/messages?conversation={$convId}");
    }
}
