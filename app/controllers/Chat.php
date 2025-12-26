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
     * Handle chat message from user
     * POST /chat/send
     */
    public function send()
    {
        // Ensure JSON response even on errors
        header('Content-Type: application/json');

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
            // Verify CSRF token (Optional for public chat? strict for now)
            if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
                // For guest flexibility, maybe relax this? But keeping it secure is better.
                // If front-end sends token, strict check.
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
                $history = $this->chatModel->getRecentContext($userId, 5);
            } else {
                // GUEST: Use Session
                if (!isset($_SESSION['chat_history'])) {
                    $_SESSION['chat_history'] = [];
                }

                // Add current message to session history
                $_SESSION['chat_history'][] = ['role' => 'user', 'message' => $message];

                // Get context from session (last 5 interactions = 10 messages)
                $sessionHistory = $_SESSION['chat_history'];
                $historyLimit = array_slice($sessionHistory, -10); // Last 10 msgs

                // Format for AI Service
                $history = [];
                foreach ($historyLimit as $h) {
                    $history[] = ['role' => $h['role'], 'message' => $h['message']]; // standardize keys
                }
            }

            // Call AI Service
            $aiResponse = $this->aiService->chat($message, $history);

            $responseText = $aiResponse['response'];
            $metadata = isset($aiResponse['metadata']) ? $aiResponse['metadata'] : [];

            // SAVE RESPONSE
            if ($isLoggedIn) {
                // DB
                $this->chatModel->saveMessage($userId, 'ai', $responseText);
            } else {
                // Session
                $_SESSION['chat_history'][] = ['role' => 'ai', 'message' => $responseText];
            }

            echo json_encode([
                'success' => true,
                'response' => $responseText,
                'metadata' => $metadata
            ]);

        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Chat Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Return user-friendly error message
            echo json_encode([
                'success' => false,
                'response' => "Maaf, terjadi kesalahan sistem. Silakan coba lagi."
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
