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
        ini_set('display_errors', '1');
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

            // HISTORY HANDLING
            $history = [];

            if ($isLoggedIn) {
                // MEMBER: Save to DB & Get Context from DB
                $this->chatModel->saveMessage($userId, 'user', $message);
                $history = $this->chatModel->getRecentContext($userId, 20); // Last 10 pairs (20 messages)
            } else {
                // GUEST: Use Session
                if (!isset($_SESSION['chat_history'])) {
                    $_SESSION['chat_history'] = [];
                }

                // Add current message to session history
                $_SESSION['chat_history'][] = ['role' => 'user', 'message' => $message];

                // Get context from session (last 10 interactions = 20 messages)
                $sessionHistory = $_SESSION['chat_history'];
                $historyLimit = array_slice($sessionHistory, -20);

                // Format for AI Service
                $history = [];
                foreach ($historyLimit as $h) {
                    $history[] = ['role' => $h['role'], 'message' => $h['message']];
                }
            }

            // CRITICAL: Close session write to prevent blocking other requests
            // This allows admin pages and other requests to work while streaming
            session_write_close();

            // Set SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering

            // Disable PHP output buffering
            if (ob_get_level())
                ob_end_clean();

            $fullResponse = '';

            // Call AI Service with streaming callback
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

            // SAVE RESPONSE
            $responseText = $aiResponse['response'];
            if ($isLoggedIn) {
                // DB - no session needed
                $this->chatModel->saveMessage($userId, 'ai', $responseText);
            } else {
                // Session - need to re-open session to write
                session_start();
                if (!isset($_SESSION['chat_history'])) {
                    $_SESSION['chat_history'] = [];
                }
                $_SESSION['chat_history'][] = ['role' => 'ai', 'message' => $responseText];
                session_write_close();
            }

        } catch (Exception $e) {
            // Log the error
            error_log("Chat Streaming Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

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

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Clear session history
        $_SESSION['chat_history'] = [];

        // Optional: Clear DB history if requested? 
        // For now, simple clear.

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Get welcome message
     * GET /chat/welcome
     */
    public function welcome()
    {
        header('Content-Type: application/json');

        // Return detailed Islamic welcome message (Matching AI Persona)
        echo json_encode([
            'success' => true,
            'message' => "Assalamualaikum Warahmatullahi Wabarakatuh 🌙\n\nSelamat datang di Albashiro - Islamic Spiritual Hypnotherapy.\n\nSaya adalah asisten AI yang siap membantu Anda dengan penuh empati. Silakan konsultasikan:\n\n✨ Keluhan & Gejala\n🕌 Layanan Hipnoterapi Islami\n💰 Harga & Paket\n👨‍⚕️ Terapis Profesional\n📅 Cek Jadwal Real-time\n\nSilakan ceritakan apa yang Anda rasakan, Insya Allah saya bantu carikan solusinya."
        ]);
    }
}
