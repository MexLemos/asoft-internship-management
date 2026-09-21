<?php

declare(strict_types=1);

namespace App\Controllers\Institution;

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

        // Get institution associated with this user
        $stmtInst = $pdo->prepare("
            SELECT i.* 
            FROM institutions i
            INNER JOIN institution_users iu ON iu.institution_id = i.id
            WHERE iu.user_id = ?
            LIMIT 1
        ");
        $stmtInst->execute([(int)$sessionUser['id']]);
        $institution = $stmtInst->fetch();

        if (!$institution) {
            Session::flash('error', 'Instituição não vinculada ao utilizador.');
            return $this->redirect('/institution/dashboard');
        }

        $instId = (int)$institution['id'];

        // Get conversations for this institution only (Anti-IDOR)
        $stmtConv = $pdo->prepare("
            SELECT ic.*, u.name as creator_name,
                   (SELECT COUNT(*) FROM institution_messages im WHERE im.conversation_id = ic.id AND im.is_read = 0 AND im.sender_id != ?) as unread_count,
                   (SELECT message FROM institution_messages im WHERE im.conversation_id = ic.id ORDER BY im.created_at DESC LIMIT 1) as last_message
            FROM institution_conversations ic
            INNER JOIN users u ON u.id = ic.created_by
            WHERE ic.institution_id = ?
            ORDER BY ic.last_message_at DESC
        ");
        $stmtConv->execute([(int)$sessionUser['id'], $instId]);
        $conversations = $stmtConv->fetchAll();

        // If conversation selected
        $selectedConvId = (int)$request->input('conversation', ($conversations[0]['id'] ?? 0));
        $messages = [];
        $activeConversation = null;

        if ($selectedConvId > 0) {
            // Verify ownership
            $stmtActive = $pdo->prepare("SELECT * FROM institution_conversations WHERE id = ? AND institution_id = ?");
            $stmtActive->execute([$selectedConvId, $instId]);
            $activeConversation = $stmtActive->fetch();

            if ($activeConversation) {
                // Mark messages as read
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

        return $this->render('institution.messages.index', [
            'title' => 'Canal de Comunicação com a Administração - Asoftmedia',
            'institution' => $institution,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages
        ], 'institution');
    }

    public function createConversation(Request $request): Response
    {
        $this->ensureAttachmentColumns();
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $subject = trim((string)$request->input('subject', ''));
        $initialMessage = trim((string)$request->input('message', ''));
        $attachment = $this->handleAttachmentUpload();

        if (empty($subject) || (empty($initialMessage) && !$attachment)) {
            Session::flash('error', 'Preencha o assunto e a mensagem inicial ou anexe um ficheiro.');
            return $this->redirect('/institution/messages');
        }

        if (empty($initialMessage) && $attachment) {
            $initialMessage = 'Envio de ficheiro anexo: ' . $attachment['name'];
        }

        $pdo = Database::getConnection();

        $stmtInst = $pdo->prepare("
            SELECT i.id, i.name 
            FROM institutions i
            INNER JOIN institution_users iu ON iu.institution_id = i.id
            WHERE iu.user_id = ? 
            LIMIT 1
        ");
        $stmtInst->execute([$userId]);
        $inst = $stmtInst->fetch();

        if (!$inst) {
            Session::flash('error', 'Instituição não identificada.');
            return $this->redirect('/institution/dashboard');
        }

        $instId = (int)$inst['id'];
        $instName = $inst['name'];

        try {
            $pdo->beginTransaction();

            $stmtConv = $pdo->prepare("
                INSERT INTO institution_conversations (institution_id, subject, created_by, status, last_message_at, created_at)
                VALUES (?, ?, ?, 'open', NOW(), NOW())
            ");
            $stmtConv->execute([$instId, $subject, $userId]);
            $convId = (int)$pdo->lastInsertId();

            $stmtMsg = $pdo->prepare("
                INSERT INTO institution_messages (conversation_id, sender_id, message, attachment_path, attachment_name, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmtMsg->execute([$convId, $userId, $initialMessage, $attachment['path'] ?? null, $attachment['name'] ?? null]);

            $pdo->commit();

            AuditLog::log('institution_conversation_started', 'messages', $convId, null, ['subject' => $subject], 'success');

            // Notify & Email Admins
            $stmtAdmins = $pdo->query("
                SELECT DISTINCT u.id, u.name, u.email
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.id
                INNER JOIN roles r ON r.id = ur.role_id
                WHERE r.name IN ('super_admin', 'admin') AND u.deleted_at IS NULL AND u.status = 'active'
            ");
            $admins = $stmtAdmins->fetchAll();

            $notifTitle = "Nova Mensagem Institucional: {$instName}";
            $notifMsg = "A instituição {$instName} iniciou a conversa '{$subject}': \"{$initialMessage}\"";
            if ($attachment) {
                $notifMsg .= " (Anexo: {$attachment['name']})";
            }
            $actionUrl = "/admin/messages?conversation={$convId}";

            foreach ($admins as $admin) {
                Notification::create((int)$admin['id'], 'info', $notifTitle, $notifMsg, $actionUrl);
                EmailService::send($admin['email'], $admin['name'], $notifTitle, $notifMsg, $actionUrl);
            }

            Session::flash('success', 'Conversa iniciada com a administração com sucesso!');
            return $this->redirect("/institution/messages?conversation={$convId}");
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Erro ao iniciar conversa: ' . $e->getMessage());
            return $this->redirect('/institution/messages');
        }
    }

    public function sendMessage(Request $request, string $conversationId): Response
    {
        $this->ensureAttachmentColumns();
        $convId = (int)$conversationId;
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $message = trim((string)$request->input('message', ''));
        $attachment = $this->handleAttachmentUpload();

        if (empty($message) && !$attachment) {
            return $this->redirect("/institution/messages?conversation={$convId}");
        }

        if (empty($message) && $attachment) {
            $message = 'Envio de ficheiro anexo: ' . $attachment['name'];
        }

        $pdo = Database::getConnection();

        // Verify ownership
        $stmtInst = $pdo->prepare("
            SELECT i.id, i.name 
            FROM institutions i
            INNER JOIN institution_users iu ON iu.institution_id = i.id
            WHERE iu.user_id = ? 
            LIMIT 1
        ");
        $stmtInst->execute([$userId]);
        $inst = $stmtInst->fetch();

        if (!$inst) {
            Session::flash('error', 'Instituição não identificada.');
            return $this->redirect('/institution/messages');
        }

        $instId = (int)$inst['id'];
        $instName = $inst['name'];

        $stmtCheck = $pdo->prepare("SELECT subject FROM institution_conversations WHERE id = ? AND institution_id = ?");
        $stmtCheck->execute([$convId, $instId]);
        $conv = $stmtCheck->fetch();
        if (!$conv) {
            Session::flash('error', 'Conversa não autorizada.');
            return $this->redirect('/institution/messages');
        }

        $stmtMsg = $pdo->prepare("
            INSERT INTO institution_messages (conversation_id, sender_id, message, attachment_path, attachment_name, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmtMsg->execute([$convId, $userId, $message, $attachment['path'] ?? null, $attachment['name'] ?? null]);

        $stmtUpd = $pdo->prepare("UPDATE institution_conversations SET last_message_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$convId]);

        // Notify & Email Admins
        $stmtAdmins = $pdo->query("
            SELECT DISTINCT u.id, u.name, u.email
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE r.name IN ('super_admin', 'admin') AND u.deleted_at IS NULL AND u.status = 'active'
        ");
        $admins = $stmtAdmins->fetchAll();

        $notifTitle = "Nova Mensagem de {$instName}";
        $notifMsg = "Mensagem recebida na conversa '{$conv['subject']}': \"{$message}\"";
        if ($attachment) {
            $notifMsg .= " (Anexo: {$attachment['name']})";
        }
        $actionUrl = "/admin/messages?conversation={$convId}";

        foreach ($admins as $admin) {
            Notification::create((int)$admin['id'], 'info', $notifTitle, $notifMsg, $actionUrl);
            EmailService::send($admin['email'], $admin['name'], $notifTitle, $notifMsg, $actionUrl);
        }

        return $this->redirect("/institution/messages?conversation={$convId}");
    }
}
