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

class MessagesController extends Controller
{
    public function index(Request $request): Response
    {
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
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $subject = trim((string)$request->input('subject', ''));
        $initialMessage = trim((string)$request->input('message', ''));

        if (empty($subject) || empty($initialMessage)) {
            Session::flash('error', 'Preencha o assunto e a mensagem inicial.');
            return $this->redirect('/institution/messages');
        }

        $pdo = Database::getConnection();

        $stmtInst = $pdo->prepare("SELECT institution_id FROM institution_users WHERE user_id = ? LIMIT 1");
        $stmtInst->execute([$userId]);
        $instId = (int)$stmtInst->fetchColumn();

        if ($instId <= 0) {
            Session::flash('error', 'Instituição não identificada.');
            return $this->redirect('/institution/dashboard');
        }

        try {
            $pdo->beginTransaction();

            $stmtConv = $pdo->prepare("
                INSERT INTO institution_conversations (institution_id, subject, created_by, status, last_message_at, created_at)
                VALUES (?, ?, ?, 'open', NOW(), NOW())
            ");
            $stmtConv->execute([$instId, $subject, $userId]);
            $convId = (int)$pdo->lastInsertId();

            $stmtMsg = $pdo->prepare("
                INSERT INTO institution_messages (conversation_id, sender_id, message, is_read, created_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            $stmtMsg->execute([$convId, $userId, $initialMessage]);

            $pdo->commit();

            // Notify Admin
            AuditLog::log('institution_conversation_started', 'messages', $convId, null, ['subject' => $subject], 'success');

            Session::flash('success', 'Conversa iniciada com a administração com sucesso!');
            return $this->redirect("/institution/messages?conversation={$convId}");
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', 'Erro ao iniciar conversa: ' . $e->getMessage());
            return $this->redirect('/institution/messages');
        }
    }

    public function sendMessage(Request $request, string $conversationId): Response
    {
        $convId = (int)$conversationId;
        $sessionUser = Session::get('user');
        $userId = (int)$sessionUser['id'];
        $message = trim((string)$request->input('message', ''));

        if (empty($message)) {
            return $this->redirect("/institution/messages?conversation={$convId}");
        }

        $pdo = Database::getConnection();

        // Verify ownership
        $stmtInst = $pdo->prepare("SELECT institution_id FROM institution_users WHERE user_id = ? LIMIT 1");
        $stmtInst->execute([$userId]);
        $instId = (int)$stmtInst->fetchColumn();

        $stmtCheck = $pdo->prepare("SELECT id FROM institution_conversations WHERE id = ? AND institution_id = ?");
        $stmtCheck->execute([$convId, $instId]);
        if (!$stmtCheck->fetchColumn()) {
            Session::flash('error', 'Conversa não autorizada.');
            return $this->redirect('/institution/messages');
        }

        $stmtMsg = $pdo->prepare("
            INSERT INTO institution_messages (conversation_id, sender_id, message, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $stmtMsg->execute([$convId, $userId, $message]);

        $stmtUpd = $pdo->prepare("UPDATE institution_conversations SET last_message_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$convId]);

        return $this->redirect("/institution/messages?conversation={$convId}");
    }
}
