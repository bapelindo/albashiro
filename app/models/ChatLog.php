<?php
/**
 * ChatLog Model
 * Handles database operations for chat history
 */
class ChatLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Save a message to the database
     */
    public function saveMessage($userId, $role, $message)
    {
        $this->db->query(
            "INSERT INTO chat_logs (user_id, role, message, created_at) VALUES (:user_id, :role, :message, NOW())",
            [
                'user_id' => $userId,
                'role' => $role,
                'message' => $message
            ]
        );
    }

    /**
     * Get full conversation history for a user
     */
    public function getConversationHistory($userId)
    {
        return $this->db->query(
            "SELECT role, message, created_at FROM chat_logs WHERE user_id = :user_id ORDER BY created_at ASC",
            ['user_id' => $userId]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent messages for context (limited)
     */
    public function getRecentContext($userId, $limit = 10)
    {
        // Fetch last N messages
        $results = $this->db->query(
            "SELECT role, message FROM chat_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit",
            [
                'user_id' => $userId,
                'limit' => $limit
            ]
        )->fetchAll(PDO::FETCH_ASSOC);

        // Reverse to chronological order
        return array_reverse($results);
    }
}
