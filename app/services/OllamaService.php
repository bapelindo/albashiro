<?php
/**
 * Albashiro - Ollama AI Service (Local Standalone)
 * Handles interactions with Local Ollama API (gemma3:1b)
 */

class OllamaService
{
    private $baseUrl;
    private $model;
    private $timeout = 45;  // Increased from 30 to 45 seconds
    private $db;

    // Auto-learning metadata
    private $lastKnowledgeMatchCount = 0;
    private $lastSearchKeywords = '';

    /**
     * Constructor
     * @param string|null $host Url host Ollama (default: Config OLLAMA_API_URL or local)
     * @param string|null $model Nama model (default: Config OLLAMA_MODEL or gemma3:1b)
     * @param int $timeout Timeout dalam detik (default: 120)
     */
    public function __construct(
        ?string $host = null,
        ?string $model = null,
        int $timeout = 120
    ) {
        $defaultHost = defined('OLLAMA_API_URL') ? OLLAMA_API_URL : 'http://localhost:11434';
        $defaultModel = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'qwen2.5:0.5b';

        $this->baseUrl = rtrim($host ?? $defaultHost, '/');
        $this->model = $model ?? $defaultModel;
        $this->timeout = $timeout;

        $this->db = Database::getInstance();
    }

    /**
     * Adapter for Chat Controller
     * Matches the interface expected by Chat.php: chat($message, $history)
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        // Increase execution time for AI processing
        set_time_limit(60);  // 60 seconds

        $startTime = microtime(true);

        // Increase PHP execution time for slow AI responses
        set_time_limit(150); // 2.5 minutes

        // Performance tracking
        $perfData = [
            'session_id' => session_id(),
            'user_message' => substr($userMessage, 0, 500),
            'ai_response' => '',
            'provider' => 'Local Ollama',
            'model' => $this->model,
            'total_time_ms' => 0,
            'api_call_time_ms' => null,
            'db_services_time_ms' => null,
            'db_therapists_time_ms' => null,
            'db_schedule_time_ms' => null,
            'db_knowledge_time_ms' => null,
            'context_build_time_ms' => null,
            'knowledge_matched' => 0,
            'keywords_searched' => '',
            'error_occurred' => 0,
            'error_message' => null,
            'fallback_used' => 0,
            'fallback_reason' => null
        ];

        // Build context with timing
        $contextStart = microtime(true);
        $systemContext = $this->buildSystemContext($userMessage, $perfData);
        $perfData['context_build_time_ms'] = round((microtime(true) - $contextStart) * 1000);

        // Prepare messages for /api/chat
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemContext];

        foreach ($conversationHistory as $msg) {
            $role = $msg['role'] === 'ai' ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $msg['message']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            // Use /api/chat for conversation context
            $apiStart = microtime(true);
            $response = $this->generateChat($messages);
            $apiTime = round((microtime(true) - $apiStart) * 1000);
            $perfData['api_call_time_ms'] = $apiTime;

            $perfData['ai_response'] = substr($response, 0, 1000);

            $responseTime = round((microtime(true) - $startTime) * 1000);
            $perfData['total_time_ms'] = $responseTime;

            // Log performance
            $logStart = microtime(true);
            $this->logPerformance($perfData);
            $logTime = round((microtime(true) - $logStart) * 1000);

            return [
                'response' => $response,
                'metadata' => [
                    'knowledge_matched' => $this->lastKnowledgeMatchCount,
                    'keywords_searched' => $this->lastSearchKeywords,
                    'response_time_ms' => $responseTime,
                    'provider' => 'Local Ollama (' . $this->model . ')'
                ]
            ];
        } catch (Exception $e) {
            error_log("Ollama Error: " . $e->getMessage());

            $perfData['error_occurred'] = 1;
            $perfData['error_message'] = substr($e->getMessage(), 0, 500);
            $perfData['ai_response'] = 'Error: ' . substr($e->getMessage(), 0, 100);
            $perfData['total_time_ms'] = round((microtime(true) - $startTime) * 1000);

            // Log failure
            $this->logPerformance($perfData);

            return [
                'response' => "Maaf, koneksi terputus. Silakan coba lagi atau hubungi admin via WhatsApp.",
                'metadata' => ['error' => true, 'debug_last_error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Mengirim chat prompt ke Ollama (api/chat)
     */
    private function generateChat(array $messages, array $options = []): string
    {
        $endpoint = $this->baseUrl . '/api/chat';

        // Optimized for Speed: Use 8 threads, keep_alive 5m
        $cpuThreads = 8;

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => '5m',  // Keep model in memory for continuous chat
            'options' => [
                'temperature' => 0.7,
                'num_ctx' => 2048,      // Balanced context window
                'num_predict' => 300,   // Longer responses without truncation
                'num_thread' => $cpuThreads,
                'top_k' => 40,
                'top_p' => 0.9,
                'repeat_penalty' => 1.1
            ]
        ];

        if (!empty($options)) {
            if (isset($options['options'])) {
                $payload['options'] = array_merge($payload['options'], $options['options']);
                unset($options['options']);
            }
            $payload = array_merge($payload, $options);
        }

        $response = $this->sendRequest($endpoint, $payload);

        if (isset($response['message']['content'])) {
            return $response['message']['content'];
        }

        throw new Exception("Invalid API Response Structure");
    }

    /**
     * Execute cURL request with optimized settings
     */
    private function sendRequest(string $url, array $data): array
    {
        $ch = curl_init($url);
        $jsonData = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  // 5s connection timeout
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);  // HTTP/1.1 for keep-alive
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Connection: keep-alive'  // Reuse connection
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($result === false) {
            throw new Exception("Ollama Connection Error: $error");
        }

        if ($httpCode >= 400) {
            throw new Exception("Ollama API Error (HTTP $httpCode): $result");
        }

        $decoded = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Build system context with Albashiro information
     * Uses session caching for static data (services, therapists)
     */
    private function buildSystemContext($userMessage = '', &$perfData = null)
    {
        $servicesInfo = '';
        $therapistsInfo = '';
        $scheduleInfo = '';
        $relevantKnowledge = '';

        // Cache services info in session (static data, rarely changes)
        try {
            $dbStart = microtime(true);
            if (!isset($_SESSION['cached_services_info'])) {
                $_SESSION['cached_services_info'] = $this->getServicesInfo();
            }
            $servicesInfo = $_SESSION['cached_services_info'];
            if ($perfData)
                $perfData['db_services_time_ms'] = round((microtime(true) - $dbStart) * 1000);
        } catch (Exception $e) {
            $servicesInfo = "⚠️ DB ERROR\n";
        }

        // Cache therapists info in session (static data, rarely changes)
        try {
            $dbStart = microtime(true);
            if (!isset($_SESSION['cached_therapists_info'])) {
                $_SESSION['cached_therapists_info'] = $this->getTherapistsInfo();
            }
            $therapistsInfo = $_SESSION['cached_therapists_info'];
            if ($perfData)
                $perfData['db_therapists_time_ms'] = round((microtime(true) - $dbStart) * 1000);
        } catch (Exception $e) {
            $therapistsInfo = "⚠️ DB ERROR\n";
        }
        // Knowledge search - only if message is substantial (skip for very short queries)
        if (strlen(trim($userMessage)) > 10) {
            try {
                $dbStart = microtime(true);
                $relevantKnowledge = $this->searchRelevantKnowledge($userMessage);
                if ($perfData) {
                    $perfData['db_knowledge_time_ms'] = round((microtime(true) - $dbStart) * 1000);
                    $perfData['knowledge_matched'] = $this->lastKnowledgeMatchCount;
                    $perfData['keywords_searched'] = $this->lastSearchKeywords;
                }
            } catch (Exception $e) {
            }
        }

        if (preg_match('/(jadwal|tersedia|booking|slot|kosong)/i', $userMessage)) {
            try {
                $dbStart = microtime(true);
                $queryDate = $this->extractDateFromMessage($userMessage) ?? date('Y-m-d');
                $scheduleInfo = $this->getAvailableSchedules($queryDate, null);
                if ($perfData)
                    $perfData['db_schedule_time_ms'] = round((microtime(true) - $dbStart) * 1000);
            } catch (Exception $e) {
            }
        }

        $context = "
Anda adalah asisten AI Albashiro - Islamic Spiritual Hypnotherapy.

[INSTRUKSI]
1. Bahasa Indonesia.
2. Jawab sopan & Islami.
3. Gunakan data di bawah ini.

[KLINIK]
WA: " . ADMIN_WHATSAPP . "
Email: " . ADMIN_EMAIL . "

[LAYANAN]
" . $servicesInfo . "

[TERAPIS]
" . $therapistsInfo . "
";

        if ($scheduleInfo)
            $context .= "\n[JADWAL]\n$scheduleInfo\n";
        if ($relevantKnowledge)
            $context .= "\n[INFO]\n$relevantKnowledge\n";

        return $context;
    }

    private function getServicesInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT name, description, price FROM services ORDER BY sort_order");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($services as $s) {
            $out .= "- {$s['name']} (Rp " . number_format($s['price'], 0, ',', '.') . ")\n";
        }
        return $out ?: "Data kosong.";
    }

    private function getTherapistsInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT name, specialty FROM therapists WHERE is_active=1");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($res as $r)
            $out .= "- {$r['name']} ({$r['specialty']})\n";
        return $out ?: "Data kosong.";
    }

    private function searchRelevantKnowledge($msg)
    {
        $pdo = $this->db->getPdo();
        $kws = $this->extractKeywords($msg);
        if (!$kws)
            return "";
        $q = [];
        $p = [];
        foreach ($kws as $k) {
            $q[] = "question LIKE ? OR answer LIKE ?";
            $p[] = "%$k%";
            $p[] = "%$k%";
        }
        $sql = "SELECT question, answer FROM ai_knowledge_base WHERE is_active=1 AND (" . implode(" OR ", $q) . ") LIMIT 3";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($p);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->lastKnowledgeMatchCount = count($res);
        $this->lastSearchKeywords = implode(',', $kws);
        $out = "";
        foreach ($res as $r)
            $out .= "Q: {$r['question']}\nA: {$r['answer']}\n";
        return $out;
    }

    private function extractKeywords($msg)
    {
        $s = ['apa', 'yang', 'dan', 'atau', 'saya', 'bisa', 'tidak'];
        return array_filter(explode(' ', strtolower($msg)), fn($w) => strlen($w) > 3 && !in_array($w, $s));
    }

    private function getAvailableSchedules($date, $tid)
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE DATE(appointment_date)=? AND status IN ('confirmed','pending')");
        $stmt->execute([$date]);
        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $slots = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];
        $out = "Tgl $date:\n";
        foreach ($slots as $s)
            $out .= (in_array("$s:00", $booked) || in_array($s, $booked)) ? "❌ $s Penuh\n" : "✅ $s Ada\n";
        return $out;
    }

    private function extractDateFromMessage($msg)
    {
        if (strpos($msg, 'besok') !== false)
            return date('Y-m-d', strtotime('+1 day'));
        if (strpos($msg, 'hari ini') !== false)
            return date('Y-m-d');
        return null;
    }

    /**
     * Log performance data to database
     */
    private function logPerformance($perfData)
    {
        try {
            require_once __DIR__ . '/../models/AiLog.php';
            $aiLog = new AiLog();
            $aiLog->logPerformance($perfData);
        } catch (Exception $e) {
            // Silent fail - don't break chat if logging fails
            error_log("Performance logging failed: " . $e->getMessage());
        }
    }
}
