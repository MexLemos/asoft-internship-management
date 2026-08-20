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

class MessagesController extends Controller
{
    public function index(Request $request): Response
    {
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
        $convId = (int)$conversationId;
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $message = trim((string)$request->input('message', ''));

        if (empty($message)) {
            return $this->redirect("/admin/messages?conversation={$convId}");
        }

        $pdo = Database::getConnection();

        $stmtMsg = $pdo->prepare("
            INSERT INTO institution_messages (conversation_id, sender_id, message, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $stmtMsg->execute([$convId, $userId, $message]);

        $stmtUpd = $pdo->prepare("UPDATE institution_conversations SET last_message_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$convId]);

        // Notify Institution creator
        $stmtConv = $pdo->prepare("SELECT created_by, subject FROM institution_conversations WHERE id = ?");
        $stmtConv->execute([$convId]);
        $conv = $stmtConv->fetch();
        if ($conv) {
            Notification::create(
                (int)$conv['created_by'],
                'message',
                'Nova Resposta da Administração Asoftmedia',
                "A administração respondeu à conversa '{$conv['subject']}': \"{$message}\"",
                "/institution/messages?conversation={$convId}"
            );
        }

        return $this->redirect("/admin/messages?conversation={$convId}");
    }
}
