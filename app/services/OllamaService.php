<?php
/**
 * Albashiro - Ollama AI Service (Local Standalone)
 * Handles interactions with Local Ollama API (gemma3:4b - Optimized)
 */

class OllamaService
{
    private $baseUrl;
    private $model;
    private $timeout;
    private $db;

    // Auto-learning metadata
    private $lastKnowledgeMatchCount = 0;
    private $lastSearchKeywords = '';
    private $autoLearning;

    // Performance: Caching
    private $scheduleCache = [];
    private $embeddingCache = [];
    private $knowledgeCache = [];

    // Static caches (shared across instances)
    private static $greetingPattern = '/^(halo|hai|assalamualaikum|selamat|hello|hi|horas|apa kabar)(\s|!|\?)*$/i';
    private static $staticSettingsCache = null;
    private static $staticSettingsCacheTime = 0;

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
        $defaultModel = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'gemma3:4b';

        $this->baseUrl = rtrim($host ?? $defaultHost, '/');
        $this->model = $model ?? $defaultModel;
        $this->timeout = 120; // 120s for long responses with large context

        $this->db = Database::getInstance();

        // Load AutoLearningService
        require_once __DIR__ . '/AutoLearningService.php';
        $this->autoLearning = new AutoLearningService();
    }

    /**
     * Get Database instance
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Streaming Chat Adapter for Real-time Response
     * Sends Server-Sent Events (SSE) to frontend as tokens arrive
     * @param string $userMessage User's message
     * @param array $conversationHistory Previous conversation context
     * @param callable $onToken Callback function to handle each token
     * @param callable $onStatus Callback function for status updates
     * @param bool $skipAutoLearning Skip auto-learning logging (for internal AI calls)
     * @return array Final response with metadata
     */
    public function chatStream($userMessage, $conversationHistory = [], $onToken = null, $onStatus = null, $skipAutoLearning = false)
    {
        // Increase execution time for streaming (long responses with large context)
        set_time_limit(180);

        // Early return for empty messages (prevents wasted processing)
        if (empty(trim($userMessage))) {
            return [
                'response' => 'Maaf, pesan Anda kosong. Silakan kirim pertanyaan.',
                'metadata' => [
                    'provider' => 'validation',
                    'response_time_ms' => 0
                ]
            ];
        }

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
            'user_sentiment' => 'NEUTRAL', // Track detected sentiment

            'error_occurred' => 0,
            'error_message' => null,
            'fallback_used' => 0,
            'fallback_reason' => null
        ];

        // 1. Send Thinking Status
        if ($onStatus && is_callable($onStatus))
            $onStatus("Sedang memproses dengan penuh perhatian...");

        // 2. Prepare Messages Array & Build History FIRST
        $messages = [];

        // Add Conversation History
        if (!empty($conversationHistory)) {
            // Limit to last 8 messages (4 pairs) for 10k context
            $historyToUse = array_slice($conversationHistory, -8);
            foreach ($historyToUse as $msg) {
                // Map 'ai' role to 'assistant'
                $role = (($msg['role'] ?? '') === 'ai') ? 'assistant' : ($msg['role'] ?? 'user');
                $content = $msg['message'] ?? ($msg['content'] ?? '');

                // Clean assistant responses
                // Note: Don't strip leading characters - they may be intentional
                // (e.g., "Assalamualaikum" should not become "ssalamualaikum")

                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        // 3. Build System Context ONCE with correct hasHistory
        $contextStart = microtime(true);
        $hasHistory = !empty($messages); // True if we have history messages
        $systemContext = $this->buildSystemContext($userMessage, $perfData, $onStatus, $hasHistory);
        $perfData['context_build_time_ms'] = round((microtime(true) - $contextStart) * 1000);

        // 4. For custom model (albashiro-assistant), SYSTEM prompt is already embedded in Modelfile
        // Don't override with system role - instead, prepend dynamic data to user message
        $isCustomModel = (strpos($this->model, 'albashiro') !== false);

        if (!$isCustomModel) {
            // Standard model: send system context as system role
            array_unshift($messages, ['role' => 'system', 'content' => $systemContext]);
        }

        // --- PERBAIKAN PENTING: CEK DUPLIKASI ---
        // Cek apakah pesan terakhir di history SAMA PERSIS dengan pesan user sekarang?
        // Jika ya, JANGAN ditambahkan lagi.
        $lastMsg = end($messages);
        $isDuplicate = ($lastMsg && $lastMsg['role'] === 'user' && trim($lastMsg['content']) === trim($userMessage));

        if (!$isDuplicate) {
            // For custom model, prepend dynamic context to user message
            if ($isCustomModel && !empty($systemContext)) {
                $userMessageWithContext = "<context>\n$systemContext\n</context>\n\n<user_query>\n$userMessage\n</user_query>";
                $messages[] = ['role' => 'user', 'content' => $userMessageWithContext];
            } else {
                $messages[] = ['role' => 'user', 'content' => $userMessage];
            }
        }

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

            // AUTO-LEARNING: Log conversation and detect knowledge gaps (skip for internal AI calls)
            if (!$skipAutoLearning) {
                try {
                    // Log conversation for analytics
                    $this->autoLearning->logConversation(
                        session_id(),
                        $userMessage,
                        $fullResponse,
                        [
                            'knowledge_matched' => $this->lastKnowledgeMatchCount,
                            'keywords_searched' => $this->lastSearchKeywords,
                            'response_time_ms' => $responseTime
                        ]
                    );

                    // Detect knowledge gap if low match
                    if ($this->lastKnowledgeMatchCount < 2) {
                        $this->autoLearning->detectKnowledgeGap(
                            $userMessage,
                            $this->lastKnowledgeMatchCount,
                            $this->extractKeywords($userMessage)
                        );
                    }
                } catch (Exception $e) {
                    // Ignore
                }
            }

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
            $perfData['error_occurred'] = 1;
            $perfData['error_message'] = substr($e->getMessage(), 0, 500);
            $perfData['ai_response'] = 'Error: ' . substr($e->getMessage(), 0, 100);
            $perfData['total_time_ms'] = round((microtime(true) - $startTime) * 1000);

            // Log failure
            $this->logPerformance($perfData);
            throw $e; // Rethrow for controller
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
            'stream' => true,
            'keep_alive' => '60m',  // Keep model loaded for 10 mins (faster subsequent)
            'options' => [
                // Quality Settings (Optimized for Qwen3:4b Instruct)
                'temperature' => 0.5,       // Balanced creativity (Qwen3 recommended)
                'top_k' => 20,              // Focused diversity (Qwen3 recommended)
                'top_p' => 0.8,             // Natural language flow (Qwen3 recommended)
                'repeat_penalty' => 1.0,    // Prevent repetition (Qwen3 recommended)

                // Speed Settings
                'num_ctx' => 8192,          // Context window (smaller = faster)
                'num_predict' => 2048,      // Max response tokens (shorter = faster)
                'num_thread' => $cpuThreads,
                'num_batch' => 2048,         // Batch size for throughput
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

        // State for Clean Start Filter
        $isResponseStarted = false;

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
                    if (isset($chunk['message']['content'])) {
                        $token = $chunk['message']['content'];

                        // --- PERBAIKAN: HAPUS SEMUA FILTER! ---
                        // Karena Prompt sudah benar, kita tidak butuh ltrim/filter lagi.
                        // Biarkan token mengalir apa adanya agar "Assalamualaikum" tidak hilang.

                        $fullResponse .= $token;

                        // Call callback if provided
                        if ($onToken && is_callable($onToken)) {
                            $onToken($token, $chunk['done'] ?? false);
                        }
                        // --------------------------------------
                    }
                }
            }

            return strlen($data);
        });

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        // Note: curl_close() deprecated in PHP 8.5+ (auto-closed by PHP)

        if ($result === false) {
            throw new Exception("Ollama Streaming Connection Error: $error");
        }

        if ($httpCode >= 400) {
            throw new Exception("Ollama Streaming API Error (HTTP $httpCode)");
        }

        return $fullResponse;
    }

    /**
     * Generate Vector Embeddings using Ollama
     * Model required: nomic-embed-text
     */
    public function generateEmbedding($text)
    {
        // Check cache first (Phase 3 optimization)
        $cacheKey = md5($text);
        if (isset($this->embeddingCache[$cacheKey])) {
            return $this->embeddingCache[$cacheKey];
        }

        $endpoint = $this->baseUrl . '/api/embeddings';

        $payload = [
            'model' => 'nomic-embed-text', // Specialized embedding model
            'prompt' => $text
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Embedding error ignored
            return null;
        }


        $data = json_decode($response, true);
        $embedding = $data['embedding'] ?? null;

        // Cache the result
        if ($embedding) {
            $this->embeddingCache[$cacheKey] = $embedding;
        }

        return $embedding;
    }

    /**
     * Vector Search (Semantic Search)
     * Finds most similar knowledge using TiDB Native Vector Search
     */
    private function vectorSearch($queryText, $limit = 3)
    {
        // 1. Generate Embedding for Query
        $queryVector = $this->generateEmbedding($queryText);
        if (!$queryVector)
            return [];

        // Convert array to string '[0.1, 0.2, ...]' for SQL
        $vectorStr = json_encode($queryVector);

        // 2. Perform Native Vector Search in TiDB
        $pdo = $this->db->getPdo();

        // Use VEC_COSINE_DISTANCE function (TiDB Specific)
        // Ensure your TiDB cluster has Vector Search enabled
        $sql = "SELECT content_text, 1 - VEC_COSINE_DISTANCE(embedding, ?) AS score 
                FROM knowledge_vectors 
                ORDER BY score DESC 
                LIMIT ?";

        $stmt = $pdo->prepare($sql);

        // Bind parameters. Note: Limit must be integer in PDO emulation sometimes, strict binding
        $stmt->bindParam(1, $vectorStr);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter by relevance threshold (e.g., > 0.4)
        return array_filter($results, fn($r) => $r['score'] > 0.4);
    }

    /**
     * Update/Insert Vector for a specific source
     * Used for Real-time RAG Sync (Auto-Sync)
     */
    public function upsertVector($table, $id, $text)
    {
        $vector = $this->generateEmbedding($text);
        if (!$vector)
            return false;

        $vectorStr = json_encode($vector);
        $pdo = $this->db->getPdo();

        // TiDB Upsert
        $sql = "INSERT INTO knowledge_vectors (source_table, source_id, content_text, embedding) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE content_text=?, embedding=?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $table,
            $id,
            $text,
            $vectorStr,
            $text,
            $vectorStr
        ]);
    }

    /**
     * Delete Vector
     */
    public function deleteVector($table, $id)
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("DELETE FROM knowledge_vectors WHERE source_table = ? AND source_id = ?");
        return $stmt->execute([$table, $id]);
    }

    /**
     * Test Retrieval for Debugging
     */
    public function testRetrieval($query)
    {
        echo "🔍 Testing RAG for query: '$query'\n";

        // 1. Check Embedding
        $vec = $this->generateEmbedding($query);
        if (!$vec) {
            echo "❌ Embedding Generation Failed.\n";
            return;
        }
        echo "✅ Embedding Generated (Dim: " . count($vec) . ")\n";

        // 2. Check Vector Search
        $results = $this->vectorSearch($query);
        echo "📊 Vector Search Results: " . count($results) . " matches.\n";
        foreach ($results as $r) {
            echo "   - Score: " . number_format($r['score'], 4) . " | Content: " . substr($r['content_text'], 0, 50) . "...\n";
        }

        // 3. Check Keyword Fallback (Just to see)
        $kws = $this->extractKeywords($query);
        echo "🔑 Keywords extracted: " . implode(', ', $kws) . "\n";
    }

    /**
     * Build system context with Albashiro information
     * SMART INJECTION: Only include relevant data based on user's question
     */
    private function buildSystemContext($userMessage = '', &$perfData = null, $onStatus = null, $hasHistory = false)
    {
        // Safety: Ensure perfData is an array if passed as null
        if ($perfData === null)
            $perfData = [];

        $needsServices = preg_match('/(layanan|service|terapi|paket|harga|biaya|price|berapa.*biaya|berapa.*harga)/i', $userMessage);
        $needsTherapists = preg_match('/(terapis|therapist|dokter|bunda|ustadzah|siapa.*terapis|profil.*terapis)/i', $userMessage);

        // FIXED: More specific schedule pattern - removed generic words like "bisa", "waktu"  
        // Only trigger if explicitly asking about schedule/booking
        $needsSchedule = preg_match('/(jadwal|tersedia.*jadwal|booking|slot|kosong.*jadwal|kapan.*tersedia|jam.*praktek|jam.*buka|reservasi|janji.*temu)/i', $userMessage);

        $needsTestimonials = preg_match('/(testimoni|review|pengalaman|hasil.*terapi|berhasil.*terapi)/i', $userMessage);

        // Only fetch what's needed
        $servicesInfo = '';
        $therapistsInfo = '';
        $testimonialsInfo = '';
        $scheduleInfo = '';
        $relevantKnowledge = '';

        // Services (only if asking about services/pricing)
        if ($needsServices) {
            if (!isset($_SESSION['cached_services_info'])) {
                $_SESSION['cached_services_info'] = $this->getServicesInfo();
            }
            $servicesInfo = $_SESSION['cached_services_info'];
        }

        // Therapists (only if asking about therapists)
        if ($needsTherapists) {
            if (!isset($_SESSION['cached_therapists_info'])) {
                $_SESSION['cached_therapists_info'] = $this->getTherapistsInfo();
            }
            $therapistsInfo = $_SESSION['cached_therapists_info'];
        }

        // Testimonials (only if explicitly asked)
        if ($needsTestimonials) {
            if (!isset($_SESSION['cached_testimonials_info'])) {
                $_SESSION['cached_testimonials_info'] = $this->getTestimonialsInfo();
            }
            $testimonialsInfo = $_SESSION['cached_testimonials_info'];
        }

        // Relevant Knowledge Search (FAQ/Blog) - Skip for pure greetings
        $isGreeting = preg_match(self::$greetingPattern, trim($userMessage));

        if (!empty($userMessage) && !$isGreeting) {
            try {
                $dbStart = microtime(true);
                $relevantKnowledge = $this->searchRelevantKnowledge($userMessage, $onStatus);
                if ($perfData) {
                    $perfData['db_knowledge_time_ms'] = round((microtime(true) - $dbStart) * 1000);
                    $perfData['knowledge_matched'] = $this->lastKnowledgeMatchCount;
                    $perfData['keywords_searched'] = $this->lastSearchKeywords;
                }
            } catch (Exception $e) {
            }
        }

        // Schedule (only if asking about schedule) - ALSO skip for greetings!
        if ($needsSchedule && !$isGreeting) {
            try {
                $dbStart = microtime(true);
                $queryDate = $this->extractDateFromMessage($userMessage) ?? date('Y-m-d');
                $therapistName = $this->extractTherapistFromMessage($userMessage);

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

        // Get Site Settings (always needed - minimal)
        $settings = $this->getSiteSettings();

        // ANALYZE SENTIMENT (EQ Engine) - Simplified
        $mood = $this->analyzeSentiment($userMessage);
        // SIMPLIFIED MOOD - Only if critical
        $moodHint = "";
        if ($mood === 'ANXIETY')
            $moodHint = "Nada: Tenang";
        elseif ($mood === 'URGENT')
            $moodHint = "Nada: Cepat, arahkan WA";
        elseif ($mood === 'ANGER')
            $moodHint = "Nada: Netral, solusi";

        // SITE IDENTITY - Fallback to constants if DB keys missing
        $siteName = $settings['site_name'] ?? $settings['name'] ?? SITE_NAME;
        $siteTagline = $settings['site_tagline'] ?? $settings['tagline'] ?? SITE_TAGLINE;
        $whatsappAdmin = $settings['admin_whatsapp'] ?? ADMIN_WHATSAPP;


        // Context suppression logic removed as per user request to handle priority in Modelfile.
        $context = ""; // Initialize context variable

        // Inject current date/time (so AI knows the real date)
        $currentDate = date('d F Y'); // e.g., "28 Desember 2025"
        $currentTime = date('H:i'); // e.g., "12:30"
        $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
        $context .= "WAKTU: $dayName, $currentDate pukul $currentTime WIB\n\n";

        // SENTIMENT-BASED RESPONSE ADJUSTMENT
        $sentiment = $this->analyzeSentiment($userMessage);

        switch ($sentiment) {
            case 'URGENT':
                $context .= "PENTING: User tampak membutuhkan bantuan segera. Berikan respons yang sangat empatik dan tawarkan solusi cepat. Prioritaskan keselamatan dan kesehatan mental mereka. Gunakan nada yang menenangkan namun responsif.\n\n";
                break;

            case 'ANXIETY':
                $context .= "PERHATIAN: User tampak cemas atau khawatir. Berikan respons yang menenangkan dan reassuring. Fokus pada solusi praktis. Hindari informasi yang bisa menambah kecemasan. Gunakan bahasa yang lembut dan supportif.\n\n";
                break;

            case 'SADNESS':
                $context .= "EMPATI: User tampak sedih atau down. Berikan dukungan emosional yang lembut. Tunjukkan empati dan pengertian mendalam. Tawarkan bantuan dengan cara yang supportif dan non-judgmental. Validasi perasaan mereka.\n\n";
                break;

            case 'ANGER':
                $context .= "TENANG: User tampak frustrasi atau kecewa. Tetap tenang dan profesional. Akui perasaan mereka dengan validasi. Fokus pada solusi konstruktif. Hindari nada defensif. Tunjukkan bahwa Anda memahami dan siap membantu.\n\n";
                break;

            case 'CURIOUS':
                $context .= "INFORMATIF: User sedang mencari informasi (harga/jadwal/booking). Berikan jawaban yang jelas, terstruktur, dan lengkap. Sertakan detail praktis. Proaktif tawarkan bantuan lebih lanjut. Gunakan nada yang helpful dan encouraging.\n\n";
                break;

            case 'NEUTRAL':
            default:
                // No special adjustment for neutral sentiment
                break;
        }

        // Store sentiment for analytics
        if (isset($perfData)) {
            $perfData['user_sentiment'] = $sentiment;
        }

        // Mood context if applicable
        if ($moodHint) {
            $context .= "$moodHint\n\n";
        }
        // Inject data (compact format)
        // Inject data (compact format) - PRIORITY ORDER (Least to Most Important)

        // 1. Knowledge Base (General Info) - Lowest Priority
        if ($relevantKnowledge)
            $context .= "\n\nKB:\n$relevantKnowledge";

        // 2. Static Data
        if ($servicesInfo)
            $context .= "\n\nLAYANAN:\n$servicesInfo";
        if ($therapistsInfo)
            $context .= "\n\nTERAPIS:\n$therapistsInfo";
        if ($testimonialsInfo)
            $context .= "\n\nTESTI:\n$testimonialsInfo";

        // 3. Real-time Schedule (HIGHEST PRIORITY - Must override KB)
        if ($scheduleInfo) {
            $context .= "\n\nJADWAL (INFO TERKINI - PRIORITAS UTAMA):\n$scheduleInfo";
            // Add warning to reinforced model attention
            $context .= "\n[INSTRUKSI: Gunakan data JADWAL di atas sebagai kebenaran mutlak. Abaikan info jadwal lain di bagian KB jika bertentangan.]";
        }

        return $context;
    }

    private function getSiteSettings()
    {
        // Static cache for 1 hour (site settings rarely change)
        if (self::$staticSettingsCache !== null && (time() - self::$staticSettingsCacheTime) < 3600) {
            return self::$staticSettingsCache;
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // Cache the result
        self::$staticSettingsCache = $settings;
        self::$staticSettingsCacheTime = time();

        return $settings;
    }

    private function getServicesInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT name, price, duration FROM services ORDER BY sort_order");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($services as $s) {
            $formattedPrice = number_format($s['price'], 0, ',', '.');
            $out .= "- {$s['name']}: Rp {$formattedPrice} ({$s['duration']})\n";
        }
        return $out ?: "Data layanan belum tersedia.";
    }

    private function getTherapistsInfo()
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT id, name, title, specialty FROM therapists WHERE is_active=1");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";

        $wa_config = defined('THERAPIST_WHATSAPP') ? THERAPIST_WHATSAPP : [];

        foreach ($res as $r) {
            $wa = $wa_config[$r['id']] ?? '-';
            $out .= "- {$r['name']} {$r['title']} ({$r['specialty']}) - WA: $wa\n";
        }
        return $out ?: "Data kosong.";
    }

    private function getTestimonialsInfo()
    {
        $pdo = $this->db->getPdo();
        // Get 2 featured testimonials (reduced from 3)
        $stmt = $pdo->query("SELECT client_name, rating FROM testimonials WHERE is_featured=1 LIMIT 2");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($res as $r) {
            $stars = str_repeat("⭐", $r['rating']);
            $out .= "- {$r['client_name']}: $stars\n";
        }
        return $out ?: "Belum ada testimoni.";
    }

    private function searchRelevantKnowledge($msg, $onStatus = null)
    {
        // Cache check (5-10ms saved on repeated queries)
        $cacheKey = md5(strtolower(trim($msg)));
        if (isset($this->knowledgeCache[$cacheKey])) {
            // Update stats from cache
            $cached = $this->knowledgeCache[$cacheKey];
            $this->lastKnowledgeMatchCount = $cached['count'];
            $this->lastSearchKeywords = $cached['keywords'];
            return $cached['result'];
        }

        $contextMatches = [];
        $seenContent = [];

        // 1. Vector Search (Semantic) - Skip if table doesn't exist
        try {
            $vectorResults = $this->vectorSearch($msg, 5);
            foreach ($vectorResults as $r) {
                // Deduplicate by content hash or direct string
                $hash = md5($r['content_text']);
                if (!isset($seenContent[$hash])) {
                    $contextMatches[] = $r['content_text']; // Vector returns 'content_text'
                    $seenContent[$hash] = true;
                }
            }
        } catch (Exception $e) {
            // Table doesn't exist or vector search failed - continue with keyword search
        }

        // 2. Keyword Search (Optimized LIKE) - TiDB Compatible
        $kws = $this->extractKeywords($msg);
        if ($kws) {
            $pdo = $this->db->getPdo();

            // Limit to top 3 keywords for performance
            $topKeywords = array_slice($kws, 0, 3);

            // 2a. Search FAQs (Optimized with prefix index)
            $p = [];
            foreach ($topKeywords as $k) {
                $p[] = "$k%";  // Prefix match (faster with index)
                $p[] = "$k%";
            }
            $q_faq = array_fill(0, count($topKeywords), "question LIKE ? OR answer LIKE ?");
            $sql_faq = "SELECT question, answer FROM faqs WHERE is_active=1 AND (" . implode(" OR ", $q_faq) . ") LIMIT 3";
            $stmt = $pdo->prepare($sql_faq);
            $stmt->execute($p);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $content = "Q: {$r['question']}\nA: {$r['answer']}";
                $hash = md5($content);
                if (!isset($seenContent[$hash])) {
                    $contextMatches[] = $content;
                    $seenContent[$hash] = true;
                }
            }


            // 2b. Search Knowledge Vectors (5050 rows - optimized with index)
            try {
                $p_vec = array_map(fn($k) => "$k%", $topKeywords);
                $q_vec = array_fill(0, count($topKeywords), "content_text LIKE ?");
                $sql_vec = "SELECT content_text FROM knowledge_vectors WHERE " . implode(" OR ", $q_vec) . " LIMIT 3";
                $stmt_vec = $pdo->prepare($sql_vec);
                $stmt_vec->execute($p_vec);

                foreach ($stmt_vec->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $content = $r['content_text'];
                    $hash = md5($content);
                    if (!isset($seenContent[$hash])) {
                        $contextMatches[] = $content;
                        $seenContent[$hash] = true;
                    }
                }
            } catch (Exception $e) {
                // Table doesn't exist or query failed, skip
            }

            // 2c. Search AI Knowledge Base (531 rows - optimized with index)
            try {
                $p_kb = [];
                foreach ($topKeywords as $k) {
                    $p_kb[] = "$k%";
                    $p_kb[] = "$k%";
                }
                $q_kb = array_fill(0, count($topKeywords), "(question LIKE ? OR answer LIKE ?)");
                $sql_kb = "SELECT question, answer FROM ai_knowledge_base WHERE is_active=1 AND (" . implode(" OR ", $q_kb) . ") LIMIT 3";
                $stmt_kb = $pdo->prepare($sql_kb);
                $stmt_kb->execute($p_kb);

                foreach ($stmt_kb->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $content = "Q: {$r['question']}\nA: {$r['answer']}";
                    $hash = md5($content);
                    if (!isset($seenContent[$hash])) {
                        $contextMatches[] = $content;
                        $seenContent[$hash] = true;
                    }
                }
            } catch (Exception $e) {
                // Table doesn't exist or query failed, skip
            }

            // 2d. Search Training Examples (Few-shot learning from DB)
            try {
                $q_train = array_fill(0, count($kws), "(keywords LIKE ? OR user_input LIKE ?)");
                $p_train = [];
                foreach ($kws as $k) {
                    $p_train[] = "%$k%";
                    $p_train[] = "%$k%";
                }
                $sql_train = "SELECT user_input, assistant_response FROM ai_training_examples WHERE is_active=1 AND (" . implode(" OR ", $q_train) . ") ORDER BY priority DESC LIMIT 2";
                $stmt_train = $pdo->prepare($sql_train);
                $stmt_train->execute($p_train);

                foreach ($stmt_train->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $content = "CONTOH:\nUser: {$r['user_input']}\nAssistant: {$r['assistant_response']}";
                    $hash = md5($content);
                    if (!isset($seenContent[$hash])) {
                        $contextMatches[] = $content;
                        $seenContent[$hash] = true;
                    }
                }
            } catch (Exception $e) {
                // Table might not exist yet, ignore
            }
        }

        // 3. Format Combined Result
        $this->lastKnowledgeMatchCount = count($contextMatches);
        $this->lastSearchKeywords = "HYBRID: " . count($contextMatches) . " items";

        $result = empty($contextMatches) ? "" : implode("\n---\n", array_slice($contextMatches, 0, 8));

        // Cache the result (limit cache size to 50 entries)
        if (count($this->knowledgeCache) > 50) {
            array_shift($this->knowledgeCache); // Remove oldest entry
        }
        $this->knowledgeCache[$cacheKey] = [
            'result' => $result,
            'count' => $this->lastKnowledgeMatchCount,
            'keywords' => $this->lastSearchKeywords
        ];

        return $result;
    }

    private function extractKeywords($msg)
    {
        // Expanded Indonesian stopwords (Phase 3 optimization)
        $stopwords = [
            'apa',
            'yang',
            'dan',
            'atau',
            'saya',
            'bisa',
            'tidak',
            'ini',
            'itu',
            'untuk',
            'dari',
            'dengan',
            'pada',
            'adalah',
            'akan',
            'ada',
            'juga',
            'sudah',
            'belum',
            'kalau',
            'kalo',
            'jika',
            'bila',
            'mau',
            'ingin',
            'bisa',
            'dapat',
            'harus',
            'perlu',
            'maka',
            'jadi',
            'lalu',
            'kemudian',
            'sangat',
            'sekali',
            'lebih',
            'paling',
            'sama',
            'lain',
            'semua',
            'setiap'
        ];

        $words = explode(' ', strtolower($msg));
        $keywords = [];

        foreach ($words as $word) {
            // Remove punctuation
            $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);

            // Filter: length > 3 and not stopword
            if (strlen($word) > 3 && !in_array($word, $stopwords)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Analyze User Sentiment (EQ Engine)
     * Detects emotional state to adjust system persona
     */
    private function analyzeSentiment($msg)
    {
        $msg = strtolower($msg);

        // PRIORITY 1: Curiosity / Buying Intent (Override emotions if asking for price/info)
        if (preg_match('/(harga|biaya|lokasi|alamat|jadwal|pesan|booking|daftar)/', $msg))
            return 'CURIOUS';

        // Anxiety / Fear
        if (preg_match('/(cemas|takut|panik|khawatir|gelisah|deg-degan|mati|bahaya)/', $msg))
            return 'ANXIETY';

        // Sadness / Depression
        if (preg_match('/(sedih|nangis|putus asa|lelah|capek|sendiri|sepi|hampa)/', $msg))
            return 'SADNESS';

        // Anger / Frustration
        if (preg_match('/(marah|kesal|benci|dendam|kecewa|bohong|penipu)/', $msg))
            return 'ANGER';

        // Critical / Urgent
        if (preg_match('/(darurat|bantu|tolong|sakit|parah)/', $msg))
            return 'URGENT';

        return 'NEUTRAL';
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
        // Check cache first (Phase 2 optimization)
        $cacheKey = $date . '_' . ($therapistName ?? 'all');
        if (isset($this->scheduleCache[$cacheKey])) {
            return $this->scheduleCache[$cacheKey];
        }

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

        // Cache the result
        $this->scheduleCache[$cacheKey] = $out;

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
        if (empty($msg))
            return null;
        if (strpos($msg, 'besok') !== false)
            return date('Y-m-d', strtotime('+1 day'));
        if (strpos($msg, 'hari ini') !== false)
            return date('Y-m-d');
        return null;
    }

    /**
     * Get statistics of last knowledge search
     */
    public function getLastKnowledgeMatchCount()
    {
        return $this->lastKnowledgeMatchCount;
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
            // Ignore
        }
    }
}
