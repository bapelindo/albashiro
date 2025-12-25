<?php
/**
 * Albashiro - Chat Controller
 * Handles AI chatbot requests
 */

class Chat extends Controller
{
    private $geminiService;

    public function __construct()
    {
        // Suppress error display for API endpoints (log errors instead)
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        require_once SITE_ROOT . '/app/services/GeminiService.php';
        $this->geminiService = new GeminiService();
    }

    /**
     * Handle chat message from user
     * POST /chat/send
     */
    public function send()
    {
        // Ensure JSON response even on errors
        header('Content-Type: application/json');

        try {
            // Only allow POST requests
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                return;
            }

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['message']) || empty(trim($input['message']))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Message is required']);
                return;
            }

            // Verify CSRF token
            if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                return;
            }

            $userMessage = trim($input['message']);

            // Get conversation history from session
            if (!isset($_SESSION['chat_history'])) {
                $_SESSION['chat_history'] = [];
            }

            $conversationHistory = $_SESSION['chat_history'];

            // Limit history to last 10 messages to avoid token limits
            if (count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
            }

            // Get AI response with metadata
            $result = $this->geminiService->chat($userMessage, $conversationHistory);
            $aiResponse = $result['response'];
            $metadata = $result['metadata'];

            // Add user message to conversation history
            $_SESSION['chat_history'][] = [
                'role' => 'user',
                'content' => $userMessage
            ];

            // Add assistant response to history  
            $_SESSION['chat_history'][] = [
                'role' => 'model',
                'content' => $aiResponse
            ];

            // Log conversation for auto-learning
            try {
                $this->logConversation($userMessage, $aiResponse, $metadata);
            } catch (Exception $e) {
                // Log error but don't break the response
                error_log("Auto-learning log error: " . $e->getMessage());
            }

            // Return success response
            echo json_encode([
                'success' => true,
                'response' => $aiResponse,
                'timestamp' => date('H:i')
            ]);

        } catch (Exception $e) {
            // Catch any unexpected errors
            error_log("Chat send error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Clear chat history
     * POST /chat/clear
     */
    public function clear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $_SESSION['chat_history'] = [];

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Chat history cleared']);
    }

    /**
     * Get welcome message
     * GET /chat/welcome
     */
    public function welcome()
    {
        $welcomeMessage = "Assalamu'alaikum! 👋\n\n";
        $welcomeMessage .= "Selamat datang di Albashiro - Islamic Spiritual Hypnotherapy.\n\n";
        $welcomeMessage .= "Saya adalah asisten AI yang siap membantu Anda dengan informasi tentang:\n";
        $welcomeMessage .= "✨ Layanan hipnoterapi kami\n";
        $welcomeMessage .= "💰 Harga dan paket\n";
        $welcomeMessage .= "👨‍⚕️ Terapis profesional kami\n";
        $welcomeMessage .= "📅 Ketersediaan jadwal\n";
        $welcomeMessage .= "📍 Lokasi dan kontak\n\n";
        $welcomeMessage .= "Silakan tanyakan apa saja yang ingin Anda ketahui! 😊";

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $welcomeMessage
        ]);
    }

    /**
     * Log conversation for auto-learning
     */
    private function logConversation($userMessage, $aiResponse, $metadata)
    {
        try {
            $db = Database::getInstance();
            $sessionId = session_id();

            $db->query("
                INSERT INTO chat_conversations 
                (session_id, user_message, ai_response, knowledge_matched, keywords_searched, response_time_ms)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $sessionId,
                $userMessage,
                $aiResponse,
                $metadata['knowledge_matched'] ?? 0,
                $metadata['keywords_searched'] ?? '',
                $metadata['response_time_ms'] ?? 0
            ]);

            // If no knowledge matched, create suggestion
            if (($metadata['knowledge_matched'] ?? 0) === 0) {
                $this->createKnowledgeSuggestion($userMessage, $metadata['keywords_searched'] ?? '');
            }

        } catch (Exception $e) {
            error_log("Conversation logging error: " . $e->getMessage());
        }
    }

    /**
     * Create or update knowledge suggestion
     */
    private function createKnowledgeSuggestion($question, $keywords)
    {
        try {
            $db = Database::getInstance();

            // Check if similar question already exists
            $existing = $db->query("
                SELECT id, frequency 
                FROM knowledge_suggestions 
                WHERE question = ? AND status = 'pending'
            ", [$question])->fetch();

            if ($existing) {
                // Increment frequency
                $db->query("
                    UPDATE knowledge_suggestions 
                    SET frequency = frequency + 1 
                    WHERE id = ?
                ", [$existing->id]);
            } else {
                // Create new suggestion
                $db->query("
                    INSERT INTO knowledge_suggestions 
                    (question, keywords, frequency, status)
                    VALUES (?, ?, 1, 'pending')
                ", [$question, $keywords]);
            }

        } catch (Exception $e) {
            error_log("Knowledge suggestion error: " . $e->getMessage());
        }
    }
}
