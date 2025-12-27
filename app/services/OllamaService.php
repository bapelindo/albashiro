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
    private $autoLearning;

    // Performance: Context caching
    private $contextCache = null;
    private $cacheExpiry = 0;
    private $cacheTTL = 300; // 5 minutes

    // Performance: Schedule caching
    private $scheduleCache = [];

    // Performance: Embedding caching
    private $embeddingCache = [];

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
        $this->timeout = $timeout === 120 ? 30 : $timeout; // EMERGENCY: Reduce default timeout to 30s

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

        // 1. Send Thinking Status
        if ($onStatus && is_callable($onStatus))
            $onStatus("Sedang memproses dengan penuh perhatian...");

        // 1. Build System Context (Dynamic)
        $contextStart = microtime(true);
        $systemContext = $this->buildSystemContext($userMessage, $perfData, $onStatus);
        $perfData['context_build_time_ms'] = round((microtime(true) - $contextStart) * 1000);

        // 2. Prepare Messages
        $messages = [];

        // Add System Prompt first
        $messages[] = ['role' => 'system', 'content' => $systemContext];

        // Add Conversation History
        if (!empty($conversationHistory)) {
            $historyToUse = array_slice($conversationHistory, -4);
            foreach ($historyToUse as $msg) {
                // Map 'ai' role to 'assistant'
                $role = (($msg['role'] ?? '') === 'ai') ? 'assistant' : ($msg['role'] ?? 'user');
                $content = $msg['message'] ?? ($msg['content'] ?? '');

                // Pastikan history bersih
                if ($role === 'assistant') {
                    // Hapus artifacts dari history masa lalu agar tidak menular
                    $content = ltrim($content, "!?. \t\n\r");
                }

                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        // --- PERBAIKAN PENTING: CEK DUPLIKASI ---
        // Cek apakah pesan terakhir di history SAMA PERSIS dengan pesan user sekarang?
        // Jika ya, JANGAN ditambahkan lagi.
        $lastMsg = end($messages);
        $isDuplicate = ($lastMsg && $lastMsg['role'] === 'user' && trim($lastMsg['content']) === trim($userMessage));

        if (!$isDuplicate) {
            $messages[] = ['role' => 'user', 'content' => $userMessage];
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
            'stream' => true,  // Enable streaming!
            'keep_alive' => '5m',
            'options' => [
                'temperature' => 0.3,
                'num_ctx' => 2048,
                'num_predict' => 1500,   // Increased to 1500 to fix long response truncation
                'num_thread' => $cpuThreads,
                'num_batch' => 256,
                'top_k' => 40,
                'top_p' => 0.6,
                'repeat_penalty' => 1.05
            ]
        ];

        $fullResponse = '';
        $buffer = '';

        // DEBUG: Log the full payload to catch "!" injection

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

        // No curl_close() needed in PHP 8.0+ (and deprecated in 8.4+)

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
    private function buildSystemContext($userMessage = '', &$perfData = null, $onStatus = null)
    {
        // Safety: Ensure perfData is an array if passed as null
        if ($perfData === null)
            $perfData = [];

        $needsServices = preg_match('/(layanan|service|terapi|paket|harga|biaya|price|berapa)/i', $userMessage);
        $needsTherapists = preg_match('/(terapis|therapist|dokter|bunda|ustadzah|siapa|profil)/i', $userMessage);
        $needsSchedule = preg_match('/(jadwal|tersedia|booking|slot|kosong|kapan|waktu|jam|reservasi|praktek|janji|bisa)/i', $userMessage);
        $needsTestimonials = preg_match('/(testimoni|review|pengalaman|hasil|berhasil)/i', $userMessage);

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

        // Relevant Knowledge Search (FAQ/Blog) - UNCONDITIONAL
        if (!empty($userMessage)) {
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

        // Schedule (only if asking about schedule)
        if ($needsSchedule) {
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
        $emotionalDirective = "";

        switch ($mood) {
            case 'ANXIETY':
                $emotionalDirective = "MODE: CALMING. Fokus validasi ketakutan. Sangat lembut.";
                break;
            case 'SADNESS':
                $emotionalDirective = "MODE: EMPATI. Berikan harapan. Pendekatan spiritual.";
                break;
            case 'ANGER':
                $emotionalDirective = "MODE: DE-ESKALASI. Jangan defensif. Tawarkan solusi.";
                break;
            case 'CURIOUS':
                $emotionalDirective = "MODE: SALES. Jelaskan value. Arahkan booking.";
                break;
            case 'URGENT':
                $emotionalDirective = "MODE: DARURAT. Singkat padat. Arahkan WhatsApp.";
                break;
            default:
                $emotionalDirective = "MODE: STANDARD. Seimbang empati & edukasi.";
        }

        // TIME AWARENESS - Simplified
        $hour = (int) date('H');
        $timeDirective = "";

        if ($hour >= 22 || $hour < 4) {
            $timeDirective = "WAKTU: LARUT MALAM. User mungkin insomnia. Nada tenang.";
        } elseif ($hour >= 4 && $hour < 10) {
            $timeDirective = "WAKTU: PAGI. Afirmasi positif.";
        } elseif ($hour >= 11 && $hour < 15) {
            $timeDirective = "WAKTU: SIANG. Professional & energic.";
        } else {
            $timeDirective = "WAKTU: SORE/MALAM. Santai & reflektif.";
        }

        // KEUNGGULAN (USPs)
        $usps = "KEUNGGULAN KLINIK:
- Pendekatan Syar'i: Terapi berdasarkan Al-Quran & Sunnah (Bebas klenik/syirik).
- Terapis Bersertifikasi: Profesional, berpengalaman, dan amanah.
- Privasi Terjaga: Kerahasiaan klien adalah prioritas mutlak.
- Metode Modern: Menggabungkan Client-Centered Therapy dengan Spiritual Hypnotherapy.";

        // TONE OF VOICE & STYLE
        $toneOfVoice = "TONE OF VOICE (GAYA BAHASA):
- Islami & Sejuk: Gunakan salam (Assalamualaikum) dan istilah yang santun.
- Empati Tinggi: Tunjukkan kepedulian pada masalah klien (Validasi perasaan mereka).
- Profesional & Solutif: Berikan jawaban yang jelas, terstruktur, dan mengarah ke solusi (Reservasi).
- Persuasif Lembut: Ajak klien untuk berubah dengan bahasa yang santun.

ATURAN RESPON:
- Fokus hanya pada jawaban inti yang relevan.
- Awali respon langsung dengan kata atau kalimat.
- Gunakan bahasa teks standar. HINDARI penggunaan simbol grafis, ikon, atau emoji.
- Pastikan nada bicara sesuai dengan TONE OF VOICE di atas.";

        // TARGET AUDIENCE
        $targetAudience = "TARGET AUDIENCE:
- Individu dengan masalah mental/emosional (Cemas, Depresi, Trauma, LGBT, Narkoba).
- Pasangan suami istri (Konflik rumah tangga, perselingkuhan).
- Orang tua (Masalah pengasuhan/parenting).";

        // SITE IDENTITY - Fallback to constants if DB keys missing
        $siteName = $settings['site_name'] ?? $settings['name'] ?? SITE_NAME;
        $siteTagline = $settings['site_tagline'] ?? $settings['tagline'] ?? SITE_TAGLINE;
        $whatsappAdmin = $settings['admin_whatsapp'] ?? ADMIN_WHATSAPP;
        $definition = $settings['description'] ?? "Albashiro adalah pusat layanan Islamic Spiritual Hypnotherapy terpercaya yang menggabungkan metode hipnoterapi klinis modern dengan pendekatan Al-Quran dan Sunnah. Kami membantu Anda mengatasi masalah mental, emosional, dan psikosomatis secara syar'i, aman, dan menenangkan.";

        // Build COMPACT Context - TRIMMED & UNIFIED
        $context = "PERAN: AI Assistant - $siteName ($siteTagline).
Misi: Memberikan ketenangan, edukasi, dan solusi praktis berbasis psikologi Islam.

$emotionalDirective
$timeDirective

SITE INFO:
- Nama: AI $siteName
- Tagline: $siteTagline
- WhatsApp Admin: $whatsappAdmin
- Definisi: $definition

$usps

$targetAudience

$toneOfVoice";

        // Inject only relevant sections
        if (!empty($servicesInfo)) {
            $context .= "\nLAYANAN:\n$servicesInfo\n";
        }

        if (!empty($therapistsInfo)) {
            $context .= "\nTERAPIS:\n$therapistsInfo\n";
        }

        if (!empty($testimonialsInfo)) {
            $context .= "\nTESTIMONI:\n$testimonialsInfo\n";
        }

        if (!empty($scheduleInfo)) {
            $context .= "\nJADWAL:\n$scheduleInfo\n";
        }

        if (!empty($relevantKnowledge)) {
            $context .= "\nKNOWLEDGE BASE:\n$relevantKnowledge\n";
        }

        $context .= "
ATURAN:
1. Jawab dalam Bahasa Indonesia yang hangat & profesional
2. Jika tidak tahu, arahkan ke WhatsApp: {$settings['admin_whatsapp']}
3. Untuk booking, arahkan ke WhatsApp terapis
4. Maksimal 3 paragraf singkat
5. Jika user menanyakan harga, sebutkan harga lengkap dari data layanan
6. Jika user menanyakan jadwal, berikan info jadwal yang tersedia
7. Jika user membutuhkan terapis tertentu, sebutkan nama dan WhatsApp-nya

TONE OF VOICE:
- Empati tinggi (validasi perasaan user)
- Profesional tapi tidak kaku
- Spiritual (Islam) tapi tidak menggurui
- Fokus pada solusi praktis

Jawablah dengan ramah, natural, dan solutif. Anda boleh mengawali dengan sapaan singkat jika perlu.";

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
        $stmt = $pdo->query("SELECT name, description, price, duration, target_audience FROM services ORDER BY sort_order");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($services as $s) {
            $formattedPrice = number_format($s['price'], 0, ',', '.');
            $out .= "- LAYANAN: {$s['name']}\n";
            $out .= "  Harga: Rp {$formattedPrice}\n";
            $out .= "  Durasi: {$s['duration']} menit\n";
            $out .= "  Target: {$s['target_audience']}\n";
            $out .= "  Deskripsi: {$s['description']}\n\n";
        }
        return $out ?: "Data layanan belum tersedia.";
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
        // Get 3 featured testimonials (removed RAND() for performance)
        $stmt = $pdo->query("SELECT client_name, content, rating FROM testimonials WHERE is_featured=1 LIMIT 3");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = "";
        foreach ($res as $r) {
            $stars = str_repeat("⭐", $r['rating']);
            $out .= "- \"{$r['content']}\" - {$r['client_name']} ($stars)\n";
        }
        return $out ?: "Belum ada testimoni.";
    }

    private function searchRelevantKnowledge($msg, $onStatus = null)
    {
        $contextMatches = [];
        $seenContent = [];

        // 1. Vector Search (Semantic) - Limit 5 (Restored)
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
            return [];
        }

        // 2. Keyword Search (Literal) - Limit 3 (Restored)
        $kws = $this->extractKeywords($msg);
        if ($kws) {
            $pdo = $this->db->getPdo();
            $p = [];
            foreach ($kws as $k) {
                // Prepare params for FAQ search
                $p[] = "%$k%";
                $p[] = "%$k%";
            }

            // 2a. Search FAQs
            $q_faq = array_fill(0, count($kws), "question LIKE ? OR answer LIKE ?");
            $sql_faq = "SELECT question, answer FROM faqs WHERE is_active=1 AND (" . implode(" OR ", $q_faq) . ") LIMIT 3";
            $stmt = $pdo->prepare($sql_faq);
            $stmt->execute($p); // Execute with duplicate params for Q and A

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $content = "Q: {$r['question']}\nA: {$r['answer']}";
                $hash = md5($content);
                if (!isset($seenContent[$hash])) {
                    $contextMatches[] = $content;
                    $seenContent[$hash] = true;
                }
            }

            // 2b. Search RAG Vectors (Text Match) - Hybrid Boost
            // Improved: Also search the massive knowledge base for exact keyword matches
            $q_vec = array_fill(0, count($kws), "content_text LIKE ?");
            $p_vec = array_map(fn($k) => "%$k%", $kws);

            $sql_vec = "SELECT content_text FROM knowledge_vectors WHERE " . implode(" OR ", $q_vec) . " LIMIT 3";
            $stmt_vec = $pdo->prepare($sql_vec);
            $stmt_vec->execute($p_vec);

            foreach ($stmt_vec->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $content = $r['content_text'];
                $hash = md5($content);
                if (!isset($seenContent[$hash])) {
                    $contextMatches[] = $content; // Add text match result
                    $seenContent[$hash] = true;
                }
            }
        }

        // 3. Format Combined Result
        $this->lastKnowledgeMatchCount = count($contextMatches);
        $this->lastSearchKeywords = "HYBRID: " . count($contextMatches) . " items";

        if (empty($contextMatches))
            return "";

        return implode("\n---\n", array_slice($contextMatches, 0, 8)); // Return max 8 unique items
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

        // Anxiety / Fear
        if (preg_match('/(cemas|takut|panik|khawatir|gelisah|deg-degan|mati|bahaya)/', $msg))
            return 'ANXIETY';

        // Sadness / Depression
        if (preg_match('/(sedih|nangis|putus asa|lelah|capek|sendiri|sepi|hampa)/', $msg))
            return 'SADNESS';

        // Anger / Frustration
        if (preg_match('/(marah|kesal|benci|dendam|kecewa|bohong|penipu)/', $msg))
            return 'ANGER';

        // Curiosity / Buying Intent
        if (preg_match('/(harga|biaya|lokasi|alamat|jadwal|pesan|booking|daftar)/', $msg))
            return 'CURIOUS';

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
