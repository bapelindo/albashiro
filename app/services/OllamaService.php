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
     * Streaming Chat Adapter for Real-time Response
     * Sends Server-Sent Events (SSE) to frontend as tokens arrive
     * @param string $userMessage User's message
     * @param array $conversationHistory Previous conversation context
     * @param callable $onToken Callback function to handle each token
     * @return array Final response with metadata
     */
    public function chatStream($userMessage, $conversationHistory = [], $onToken = null)
    {
        // Increase execution time for streaming
        set_time_limit(150);

        $startTime = microtime(true);

        // Performance tracking
        $perfData = [
            'session_id' => session_id(),
            'user_message' => substr($userMessage, 0, 500),
            'ai_response' => '',
            'provider' => 'Local Ollama (Streaming)',
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

        // 1. Build System Context (Dynamic)
        $contextStart = microtime(true);
        $systemContext = $this->buildSystemContext($userMessage, $perfData);
        $perfData['context_build_time_ms'] = round((microtime(true) - $contextStart) * 1000);

        // 2. Prepare Messages
        $messages = [];

        // Add System Prompt first
        $messages[] = ['role' => 'system', 'content' => $systemContext];

        // Add Conversation History (Ensure limit, just in case)
        if (!empty($conversationHistory)) {
            // Take only last 20 messages if not already limited
            $historyToUse = array_slice($conversationHistory, -20);
            foreach ($historyToUse as $msg) {
                // Map 'ai' role to 'assistant' for Ollama
                $role = ($msg['role'] === 'ai') ? 'assistant' : $msg['role'];
                $messages[] = ['role' => $role, 'content' => $msg['message']];
            }
        }

        // Add Current User Message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            // Call Streaming API
            $apiStart = microtime(true);
            $fullResponse = $this->generateChatStream($messages, $onToken);
            $apiTime = round((microtime(true) - $apiStart) * 1000);
            $perfData['api_call_time_ms'] = $apiTime;

            $perfData['ai_response'] = substr($fullResponse, 0, 1000);

            $responseTime = round((microtime(true) - $startTime) * 1000);
            $perfData['total_time_ms'] = $responseTime;

            // Log performance
            $this->logPerformance($perfData);

            return [
                'response' => $fullResponse,
                'metadata' => [
                    'knowledge_matched' => $this->lastKnowledgeMatchCount,
                    'keywords_searched' => $this->lastSearchKeywords,
                    'response_time_ms' => $responseTime,
                    'provider' => 'Local Ollama (' . $this->model . ') - Streaming'
                ]
            ];
        } catch (Exception $e) {
            error_log("Ollama Streaming Error: " . $e->getMessage());

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
     * Generate streaming chat response from Ollama
     * Processes NDJSON stream and calls callback for each token
     */
    private function generateChatStream(array $messages, $onToken = null): string
    {
        $endpoint = $this->baseUrl . '/api/chat';

        // Optimized for Speed: Use 8 threads, keep_alive 5m
        $cpuThreads = 8;

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => true,  // Enable streaming!
            'keep_alive' => '5m',
            'options' => [
                'temperature' => 0.7,
                'num_ctx' => 2048,
                'num_predict' => 300,
                'num_thread' => $cpuThreads,
                'top_k' => 40,
                'top_p' => 0.9,
                'repeat_penalty' => 1.1
            ]
        ];

        $fullResponse = '';
        $buffer = '';

        // cURL streaming handler
        $ch = curl_init($endpoint);
        $jsonData = json_encode($payload);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);  // Don't buffer
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Connection: keep-alive'
        ]);

        // Write callback - called for each chunk received
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$fullResponse, &$buffer, $onToken) {
            $buffer .= $data;

            // Process complete lines (NDJSON format)
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                $line = trim($line);
                if (empty($line))
                    continue;

                // Parse JSON line
                $chunk = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Extract token from message content
                    if (isset($chunk['message']['content'])) {
                        $token = $chunk['message']['content'];
                        $fullResponse .= $token;

                        // Call callback if provided
                        if ($onToken && is_callable($onToken)) {
                            $onToken($token, $chunk['done'] ?? false);
                        }
                    }
                }
            }

            return strlen($data);
        });

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception("Ollama Streaming Connection Error: $error");
        }

        if ($httpCode >= 400) {
            throw new Exception("Ollama Streaming API Error (HTTP $httpCode)");
        }

        return $fullResponse;
    }

    /**
     * Build system context with Albashiro information
     * Uses session caching for static data (services, therapists)
     */
    private function buildSystemContext($userMessage = '', &$perfData = null)
    {
        // Cache services info in session
        if (!isset($_SESSION['cached_services_info'])) {
            $_SESSION['cached_services_info'] = $this->getServicesInfo();
        }
        $servicesInfo = $_SESSION['cached_services_info'];

        // Cache therapists info in session
        if (!isset($_SESSION['cached_therapists_info'])) {
            $_SESSION['cached_therapists_info'] = $this->getTherapistsInfo();
        }
        $therapistsInfo = $_SESSION['cached_therapists_info'];

        // Cache testimonials info (new)
        if (!isset($_SESSION['cached_testimonials_info'])) {
            $_SESSION['cached_testimonials_info'] = $this->getTestimonialsInfo();
        }
        $testimonialsInfo = $_SESSION['cached_testimonials_info'];

        $scheduleInfo = '';
        $relevantKnowledge = '';

        // Relevant Knowledge Search (FAQ/Blog)
        if (!empty($userMessage)) {
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

        if (preg_match('/(jadwal|tersedia|booking|slot|kosong|kapan|waktu|jam|reservasi|praktek|janji|bisa)/i', $userMessage)) {
            try {
                $dbStart = microtime(true);
                $queryDate = $this->extractDateFromMessage($userMessage) ?? date('Y-m-d');
                $therapistName = $this->extractTherapistFromMessage($userMessage);

                // If no specific therapist mentioned, show all therapists
                if ($therapistName === null) {
                    $scheduleInfo = $this->getAllTherapistsSchedules($queryDate);
                } else {
                    $scheduleInfo = $this->getAvailableSchedules($queryDate, $therapistName);
                }

                if ($perfData)
                    $perfData['db_schedule_time_ms'] = round((microtime(true) - $dbStart) * 1000);
            } catch (Exception $e) {
            }
        }

        // Get Site Settings
        $settings = $this->getSiteSettings();

        // Build the Context String
        $context = "
Anda adalah asisten AI Albashiro - Islamic Spiritual Hypnotherapy.
Tagline: " . SITE_TAGLINE . "
Waktu Server: " . format_date_id(date('Y-m-d')) . " pukul " . date('H:i') . "

[INSTRUKSI]
1. Bahasa Indonesia.
2. Jawab sopan & Islami.
3. Gunakan data di bawah ini.
4. PENTING: Jika ditanya jadwal, tampilkan SEMUA waktu yang TERSEDIA. Jangan pilih-pilih atau skip waktu tertentu.
5. SAFETY: Jika informasi tidak ada di data, katakan tidak tahu & arahkan ke Admin WA. JANGAN MENGARANG.

[KLINIK]
Nama: " . SITE_NAME . "
Alamat: " . ($settings['address'] ?? 'Jl. Imam Bonjol No. 123') . "
Jam Buka: " . ($settings['operating_hours'] ?? '09:00 - 17:00') . "
WA Admin: " . ADMIN_WHATSAPP . "
Email: " . ADMIN_EMAIL . "
Sosmed: Instagram (" . ($settings['instagram'] ?? '-') . "), TikTok (" . ($settings['tiktok'] ?? '-') . ")

[LAYANAN]
" . $servicesInfo . "

[TERAPIS]
" . $therapistsInfo . "

[TESTIMONI]
" . $testimonialsInfo . "
";

        if ($scheduleInfo)
            $context .= "\n[JADWAL]\n$scheduleInfo\nINSTRUKSI KHUSUS:\n1. Tampilkan jadwal dalam SATU BARIS dipisahkan koma.\n2. JANGAN gunakan list/poin ke bawah.\n3. Contoh: '09:00, 10:00, 11:00' (Compact).\n";
        if ($relevantKnowledge)
            $context .= "\n[INFO]\n$relevantKnowledge\n";

        return $context;
    }

    private function getSiteSettings()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $results ?: [];
    }

    private function getServicesInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT name, description, price, duration FROM services ORDER BY sort_order");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($services as $s) {
            $out .= "- {$s['name']} (Rp " . number_format($s['price'], 0, ',', '.') . ", durasi {$s['duration']})\n  {$s['description']}\n";
        }
        return $out ?: "Data kosong.";
    }

    private function getTherapistsInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT id, name, title, specialty, bio FROM therapists WHERE is_active=1");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";

        $wa_config = defined('THERAPIST_WHATSAPP') ? THERAPIST_WHATSAPP : [];

        foreach ($res as $r) {
            $wa = $wa_config[$r['id']] ?? '-';
            $out .= "- {$r['name']} {$r['title']} ({$r['specialty']})\n  Bio: {$r['bio']}\n  WA: $wa\n";
        }
        return $out ?: "Data kosong.";
    }

    private function getTestimonialsInfo()
    {
        $pdo = $this->db->getPdo();
        // Get 3 random featured testimonials
        $stmt = $pdo->query("SELECT client_name, content, rating FROM testimonials WHERE is_featured=1 ORDER BY RAND() LIMIT 3");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($res as $r) {
            $stars = str_repeat("⭐", $r['rating']);
            $out .= "- \"{$r['content']}\" - {$r['client_name']} ($stars)\n";
        }
        return $out ?: "Belum ada testimoni.";
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
        // Also search in FAQs table if ai_knowledge_base is empty or separate
        $sql = "SELECT question, answer FROM faqs WHERE is_active=1 AND (" . implode(" OR ", $q) . ") LIMIT 3";
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

    private function getAllTherapistsSchedules($date)
    {
        $pdo = $this->db->getPdo();

        // Get all active therapists
        $stmt = $pdo->query("SELECT id, name FROM therapists WHERE is_active=1 ORDER BY id");
        $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = "Jadwal semua terapis tgl $date:\n\n";

        foreach ($therapists as $therapist) {
            $therapistId = $therapist['id'];
            $therapistName = $therapist['name'];

            // Get booked slots for this therapist
            $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE DATE(appointment_date)=? AND therapist_id=? AND status IN ('confirmed','pending')");
            $stmt->execute([$date, $therapistId]);
            $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $slots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
            $available = [];
            $bookedSlots = [];

            foreach ($slots as $s) {
                $isBooked = in_array("$s:00", $booked) || in_array($s, $booked);
                if ($isBooked) {
                    $bookedSlots[] = $s;
                } else {
                    $available[] = $s;
                }
            }

            $output .= "[$therapistName]\n";

            if (!empty($available)) {
                $output .= "TERSEDIA: " . implode(', ', $available) . "\n";
            } else {
                $output .= "TERSEDIA: Tidak ada\n";
            }

            if (!empty($bookedSlots)) {
                $output .= "PENUH: " . implode(', ', $bookedSlots) . "\n";
            }
        }

        return $output;
    }

    private function getAvailableSchedules($date, $therapistName = null)
    {
        $pdo = $this->db->getPdo();

        // Get therapist ID if name is provided
        $therapistId = null;
        if ($therapistName) {
            $stmt = $pdo->prepare("SELECT id FROM therapists WHERE name LIKE ? AND is_active=1 LIMIT 1");
            $stmt->execute(["%$therapistName%"]);
            $therapist = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($therapist) {
                $therapistId = $therapist['id'];
            }
        }

        // Get booked slots for this date (and therapist if specified)
        if ($therapistId) {
            $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE DATE(appointment_date)=? AND therapist_id=? AND status IN ('confirmed','pending')");
            $stmt->execute([$date, $therapistId]);
        } else {
            $stmt = $pdo->prepare("SELECT appointment_time FROM bookings WHERE DATE(appointment_date)=? AND status IN ('confirmed','pending')");
            $stmt->execute([$date]);
        }

        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $slots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

        $available = [];
        $bookedSlots = [];

        foreach ($slots as $s) {
            $isBooked = in_array("$s:00", $booked) || in_array($s, $booked);
            if ($isBooked) {
                $bookedSlots[] = $s;
            } else {
                $available[] = $s;
            }
        }

        $out = $therapistName ? "Jadwal $therapistName tgl $date:\n" : "Jadwal tgl $date:\n";

        if (!empty($available)) {
            $out .= "TERSEDIA: " . implode(', ', $available) . "\n";
        }

        if (!empty($bookedSlots)) {
            $out .= "PENUH: " . implode(', ', $bookedSlots) . "\n";
        }

        if (empty($available) && empty($bookedSlots)) {
            $out .= "Tidak ada slot tersedia.\n";
        }

        return $out;
    }

    private function extractTherapistFromMessage($msg)
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT name FROM therapists WHERE is_active=1");
        $therapists = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $msgLower = strtolower($msg);

        // Check if any therapist name is mentioned in the message
        foreach ($therapists as $name) {
            // Check for full name match first
            if (stripos($msgLower, strtolower($name)) !== false) {
                return $name;
            }

            // Check for partial name (each word in therapist name)
            $nameParts = explode(' ', $name);
            foreach ($nameParts as $part) {
                // Skip titles and short words
                if (strlen($part) <= 2 || in_array(strtolower($part), ['hj', 'dr', 's.psi', 'ch.t', 'ci', 'spd.i'])) {
                    continue;
                }

                // Check if this part appears in message
                if (stripos($msgLower, strtolower($part)) !== false) {
                    return $name;
                }
            }

            // Check for nickname/shortened version (e.g., "muza" for "Muzayanah")
            foreach ($nameParts as $part) {
                if (strlen($part) > 4) {
                    // Check if message contains first 4+ characters of name part
                    $shortName = substr(strtolower($part), 0, 4);
                    if (stripos($msgLower, $shortName) !== false) {
                        return $name;
                    }
                }
            }
        }

        return null;
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
