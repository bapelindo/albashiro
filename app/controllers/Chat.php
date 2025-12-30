<?php
/**
 * Albashiro - Chat Controller
 * Handles AI chatbot requests
 */

class Chat extends Controller
{
    private $aiService;
    private $chatModel; // Added chatModel property explicitly although usually dynamic

    public function __construct()
    {
        // Suppress error display for API endpoints (log errors instead)
        ini_set('display_errors', '0'); // FIXED: Should be 0 in production
        error_reporting(E_ALL);

        // Instantiation - Use OllamaService (Local Standalone)
        require_once SITE_ROOT . '/app/services/OllamaService.php';
        $this->aiService = new OllamaService();

        // Load Chat Model
        $this->chatModel = $this->model('ChatLog'); // Assuming model name is ChatLog or similar, verifying below
    }

    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('/auth/login'));
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Mark messages as read (if method exists)
        // $this->markMessagesAsRead($userId); // Commented out if not sure of method existence

        // Get user data for UI
        $userModel = $this->model('User');
        $user = $userModel->getUserById($userId);

        // Get conversation history
        // Assuming chatModel has getConversationHistory
        // To be safe, let's use the model correctly. 
        // Logic from previous file implies $this->chatModel is available.
        $chatHistory = $this->chatModel->getConversationHistory($userId);

        $data = [
            'title' => 'Chat Konsultasi - Albashiro',
            'user' => $user,
            'chat_history' => $chatHistory
        ];

        $this->view('chat/index', $data);
    }

    /**
     * Handle streaming chat message from user
     * POST /chat/stream
     * Uses Server-Sent Events (SSE) for real-time streaming
     */
    public function stream()
    {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $message = isset($input['message']) ? trim($input['message']) : '';

        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message is required']);
            return;
        }

        try {
            // Verify CSRF token
            if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
                if (isset($_SESSION['user_id'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                    return;
                }
            }

            // Determine User Context (Member vs Guest)
            $isLoggedIn = isset($_SESSION['user_id']);
            $userId = $isLoggedIn ? $_SESSION['user_id'] : null;

            // HISTORY HANDLING - GET HISTORY FIRST (before current message)
            $history = [];

            if ($isLoggedIn) {
                // MEMBER: Get PREVIOUS context from DB first (NOT including current message)
                $history = $this->chatModel->getRecentContext($userId, 20); // Last 10 pairs
                // NOTE: User message will be saved AFTER AI response (as pair)
            } else {
                // GUEST: Get from session (NOT including current message yet)
                if (!isset($_SESSION['chat_history'])) {
                    $_SESSION['chat_history'] = [];
                }

                // Get existing history BEFORE adding current message
                $sessionHistory = $_SESSION['chat_history'];
                $historyLimit = array_slice($sessionHistory, -20);

                // Format for AI Service
                foreach ($historyLimit as $h) {
                    $history[] = ['role' => $h['role'], 'message' => $h['message']];
                }

                // Will add current message to session AFTER AI response
            }

            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', false);

            // CRITICAL: Clear ANY accidental output (warnings, spaces, BOMs)
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            // UNLOCK SESSION early to prevent blocking
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');
            header('Content-Encoding: none');

            ob_implicit_flush(true);

            $fullResponse = '';

            // Call AI Service with streaming callback
            $aiResponse = $this->aiService->chatStream($message, $history, function ($token, $done) use (&$fullResponse) {
                $fullResponse .= $token;

                // Send SSE event
                echo "data: " . json_encode([
                    'token' => $token,
                    'done' => $done
                ]) . "\n\n";

                // Flush output immediately
                if (ob_get_level())
                    ob_flush();
                flush();
            }, function ($status) {
                // Send Status Event (Thinking Process)
                echo "data: " . json_encode([
                    'status' => $status
                ]) . "\n\n";

                if (ob_get_level())
                    ob_flush();
                flush();
            });

            // Send completion event with metadata
            echo "data: " . json_encode([
                'done' => true,
                'metadata' => $aiResponse['metadata'] ?? []
            ]) . "\n\n";

            if (ob_get_level())
                ob_flush();
            flush();

            // SAVE COMPLETE CONVERSATION PAIR (User + AI) to DB
            $responseText = $aiResponse['response'] ?? '';

            // Save to DB using ChatLog model
            $this->chatModel->saveConversationPair($userId, $message, $responseText);

            // Also save to session for guests (for current session context)
            if (!$isLoggedIn) {
                // Reopen session to save history
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['chat_history'][] = ['role' => 'user', 'message' => $message];
                $_SESSION['chat_history'][] = ['role' => 'ai', 'message' => $responseText];
            }

        } catch (Exception $e) {
            // Log error for debugging
            error_log("Chat stream error: " . $e->getMessage());

            // Send error event
            echo "data: " . json_encode([
                'error' => true,
                'message' => "Maaf, terjadi kesalahan sistem. Silakan coba lagi."
            ]) . "\n\n";

            if (ob_get_level())
                ob_flush();
            flush();
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

        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session history (for both logged in and guest users)
        $_SESSION['chat_history'] = [];

        // Optional: Clear DB history for logged-in users
        if (isset($_SESSION['user_id']) && $this->chatModel) {
            try {
                // Uncomment if you want to clear DB history
                // $this->chatModel->clearHistory($_SESSION['user_id']);
            } catch (Exception $e) {
                error_log("Clear history error: " . $e->getMessage());
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Get welcome message
     * GET /chat/welcome
     */
    public function welcome()
    {
        // RESET SESSION HISTORY on Welcome Call (New Session / Refresh)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear history for guests (using session)
        if (isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }

        header('Content-Type: application/json');

        // Return detailed Islamic welcome message (Matching AI Persona)
        echo json_encode([
            'success' => true,
            'message' => "Assalamualaikum Warahmatullahi Wabarakatuh 🌙\n\nSelamat datang di Albashiro - Islamic Spiritual Hypnotherapy.\n\nSaya adalah asisten AI yang siap membantu Anda dengan penuh empati. Silakan konsultasikan:\n\n✨ Keluhan & Gejala\n🕌 Layanan Hipnoterapi Islami\n💰 Harga & Paket\n👨‍⚕️ Terapis Profesional\n📅 Cek Jadwal Real-time\n\nSilakan ceritakan apa yang Anda rasakan, Insya Allah saya bantu carikan solusinya."
        ]);
    }
}
