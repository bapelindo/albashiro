<?php
/**
 * Albashiro - Ollama AI Service (Local Standalone)
 * Handles interactions with Local Ollama API (gemma3:1b)
 */

class OllamaService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;
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
        $defaultModel = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'gemma3:1b';

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
        $startTime = microtime(true);
        $systemContext = $this->buildSystemContext($userMessage);

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
            $response = $this->generateChat($messages);
            $responseTime = round((microtime(true) - $startTime) * 1000);

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
            error_log("Ollama Failed: " . $e->getMessage());
            return [
                'response' => "Maaf, sistem sedang sibuk. " . $e->getMessage(),
                'metadata' => ['error' => true, 'debug_last_error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Mengirim chat prompt ke Ollama (api/chat)
     */
    public function generateChat(array $messages, array $options = []): string
    {
        $endpoint = $this->baseUrl . '/api/chat';

        // Optimized for Speed: Use 4 threads, keep_alive 30m
        $cpuThreads = 4;

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => '30m',
            'options' => [
                'temperature' => 0.6,
                'num_ctx' => 2048,
                'num_predict' => 200,
                'num_thread' => $cpuThreads,
                'top_k' => 40,
                'top_p' => 0.9,
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
     * Fungsi internal untuk mengeksekusi cURL.
     */
    private function sendRequest(string $url, array $data): array
    {
        $ch = curl_init($url);

        $jsonData = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
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
     */
    private function buildSystemContext($userMessage = '')
    {
        $servicesInfo = '';
        $therapistsInfo = '';
        $scheduleInfo = '';
        $relevantKnowledge = '';

        try {
            $servicesInfo = $this->getServicesInfo();
        } catch (Exception $e) {
            $servicesInfo = "⚠️ DB ERROR\n";
        }
        try {
            $therapistsInfo = $this->getTherapistsInfo();
        } catch (Exception $e) {
            $therapistsInfo = "⚠️ DB ERROR\n";
        }
        try {
            $relevantKnowledge = $this->searchRelevantKnowledge($userMessage);
        } catch (Exception $e) {
        }

        if (preg_match('/(jadwal|tersedia|booking|slot|kosong)/i', $userMessage)) {
            try {
                $queryDate = $this->extractDateFromMessage($userMessage) ?? date('Y-m-d');
                $scheduleInfo = $this->getAvailableSchedules($queryDate, null);
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
}
