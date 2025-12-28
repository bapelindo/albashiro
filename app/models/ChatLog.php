<?php
/**
 * ChatLog Model
 * Handles database operations for chat history using chat_conversations table
 */
class ChatLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Save a conversation pair (user message + AI response) directly
     * Called AFTER AI response is complete
     */
    public function saveConversationPair($userId, $userMessage, $aiResponse)
    {
        $sessionId = session_status() === PHP_SESSION_ACTIVE ? session_id() : 'no-session';

        $this->db->query(
            "INSERT INTO chat_conversations (session_id, user_message, ai_response, created_at) 
             VALUES (:session_id, :user_message, :ai_response, NOW())",
            [
                'session_id' => $sessionId,
                'user_message' => $userMessage,
                'ai_response' => $aiResponse
            ]
        );
    }

    /**
     * Save a message to the database (Legacy - kept for backwards compatibility)
     * For chat_conversations table, we save pairs (user + AI response)
     */
    public function saveMessage($userId, $role, $message)
    {
        // For backward compatibility, but chat_conversations uses session_id
        // We'll store messages temporarily and save as conversation pair
        if (!isset($_SESSION['pending_user_message']) && $role === 'user') {
            $_SESSION['pending_user_message'] = $message;
        } elseif ($role === 'ai' && isset($_SESSION['pending_user_message'])) {
            // Save the conversation pair
            $sessionId = session_status() === PHP_SESSION_ACTIVE ? session_id() : 'no-session';

            $this->db->query(
                "INSERT INTO chat_conversations (session_id, user_message, ai_response, created_at) 
                 VALUES (:session_id, :user_message, :ai_response, NOW())",
                [
                    'session_id' => $sessionId,
                    'user_message' => $_SESSION['pending_user_message'],
                    'ai_response' => $message
                ]
            );

            unset($_SESSION['pending_user_message']);
        }
    }

    /**
     * Get full conversation history for a user (by session)
     */
    public function getConversationHistory($userId)
    {
        // Get by session_id instead of user_id
        $sessionId = session_status() === PHP_SESSION_ACTIVE ? session_id() : 'no-session';

        $conversations = $this->db->query(
            "SELECT user_message, ai_response, created_at 
             FROM chat_conversations 
             WHERE session_id = :session_id 
             ORDER BY created_at ASC",
            ['session_id' => $sessionId]
        )->fetchAll();

        // Convert to role-based format for compatibility
        $history = [];
        foreach ($conversations as $conv) {
            $history[] = (object) [
                'role' => 'user',
                'message' => $conv->user_message,
                'created_at' => $conv->created_at
            ];
            $history[] = (object) [
                'role' => 'ai',
                'message' => $conv->ai_response,
                'created_at' => $conv->created_at
            ];
        }

        return $history;
    }

    /**
     * Get recent messages for context (limited)
     */
    public function getRecentContext($userId, $limit = 10)
    {
        $sessionId = session_status() === PHP_SESSION_ACTIVE ? session_id() : 'no-session';

        // Get last N/2 conversation pairs (since each pair = 2 messages)
        $pairLimit = max(1, (int) ($limit / 2));

        $conversations = $this->db->query(
            "SELECT user_message, ai_response 
             FROM chat_conversations 
             WHERE session_id = :session_id 
             ORDER BY created_at DESC 
             LIMIT $pairLimit",
            [
                'session_id' => $sessionId
            ]
        )->fetchAll();

        // Convert to role-based format and reverse to chronological order
        $history = [];
        foreach (array_reverse($conversations) as $conv) {
            $history[] = ['role' => 'user', 'message' => $conv->user_message];
            $history[] = ['role' => 'ai', 'message' => $conv->ai_response];
        }

        return $history;
    }
}
