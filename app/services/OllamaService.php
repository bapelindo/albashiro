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
    public function chatStream($userMessage, $conversationHistory = [], $onToken = null, $onStatus = null)
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
     * Generate Vector Embeddings using Ollama
     * Model required: nomic-embed-text
     */
    public function generateEmbedding($text)
    {
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
            error_log("Ollama Embedding Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);
        return $data['embedding'] ?? null;
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
     * Uses session caching for static data (services, therapists)
     */
    private function buildSystemContext($userMessage = '', &$perfData = null, $onStatus = null)
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
                // Pass status callback to search
                $relevantKnowledge = $this->searchRelevantKnowledge($userMessage, $onStatus);
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

        // ANALYZE SENTIMENT (EQ Engine)
        $mood = $this->analyzeSentiment($userMessage);
        $emotionalDirective = "";

        switch ($mood) {
            case 'ANXIETY':
                $emotionalDirective = "MODE: PENENANG (CALMING). Fokus pada validasi ketakutan user. Gunakan kalimat yang sangat lembut. Yakinkan bahwa kondisi ini bisa disembuhkan. Kurangi terminologi teknis yang menakutkan.";
                break;
            case 'SADNESS':
                $emotionalDirective = "MODE: EMPATI MENDALAM. User sedang sedih/lelah. Berikan harapan (Hope). Gunakan pendekatan spiritual yang menyentuh hati. Jangan terlalu 'pushy' jualan.";
                break;
            case 'ANGER':
                $emotionalDirective = "MODE: DE-ESKALASI. User sedang kesal. Jangan defensif. Minta maaf secara profesional jika ada ketidaknyamanan. Tawarkan solusi cepat.";
                break;
            case 'CURIOUS':
                $emotionalDirective = "MODE: SALES ASSISTANT. User tertarik membeli. Jelaskan value for money. Arahkan untuk booking segera. Gunakan teknik closing yang sopan.";
                break;
            case 'URGENT':
                $emotionalDirective = "MODE: DARURAT. Berikan respon singkat dan padat. Arahkan segera ke WhatsApp atau UGD jika membahayakan fisik. Jangan bertele-tele.";
                break;
            default:
                $emotionalDirective = "MODE: STANDARD. Seimbang antara empati dan edukasi.";
        }

        // TIME AWARENESS ENGINE (Chronobiology)
        $hour = (int) date('H');
        $timeDirective = "";

        if ($hour >= 22 || $hour < 4) {
            $timeDirective = "WAKTU: LARUT MALAM (Jam $hour). User mungkin mengalami INSOMNIA atau Overthinking. Gunakan nada suara 'berbisik' (sangat tenang). Prioritaskan saran relaksasi tidur/dzikir malam.";
        } elseif ($hour >= 4 && $hour < 10) {
            $timeDirective = "WAKTU: PAGI HARI. Berikan afirmasi positif untuk memulai hari. Semangat dan optimis.";
        } elseif ($hour >= 11 && $hour < 15) {
            $timeDirective = "WAKTU: SIANG HARI. Keep it professional & energic.";
        } else {
            $timeDirective = "WAKTU: SORE/MALAM. Mode santai dan reflektif.";
        }

        // Build the Context String (Persona Upgrade: The Empathetic Expert)
        $context = "
PERAN ANDA:
Anda adalah 'Albashiro Intelligence', Konsultan Digital Profesional & Empatik untuk Klinik Albashiro (Islamic Spiritual Hypnotherapy).
Misi Anda: Memberikan ketenangan (Sakinah), edukasi ilmiah, dan solusi praktis berbasis Islam kepada setiap penanya.

KONTEKS REAL-TIME:
[EMOTIONAL_STATE]: $emotionalDirective
[TIME_CONTEXT]: $timeDirective

INSTRUKSI KECERDASAN BUATAN (ADVANCED):
1. **NLP Pacing & Leading**: Samakan frekuensi/kata-kata dengan user dulu (Pacing), baru arahkan ke solusi (Leading). Jangan langsung menasehati beda frekuensi.
2. **Hipnotik Language User**: Gunakan pola bahasa persuasif halus (e.g., 'Dan saat Anda mulai merasa lebih tenang, Anda bisa menyadari bahwa...').
3. **Fact Checking**: Pastikan setiap saran medis/hukum Islam sesuai dengan [SOURCES]. Jangan berhalusinasi.

CORE VALUES (4 Pilar):
1. TASAMUH (Empati): Validasi perasaan user terlebih dahulu. Jangan langsung menasehati.
2. HIKMAH (Bijaksana): Gabungkan logika medis/psikologis dengan dalil agama yang menenangkan.
3. SOLUTIF: Berikan langkah konkret, bukan teori abstrak.
4. PROFESIONAL: Gunakan bahasa Indonesia yang sopan, hangat, dan berwibawa.

STRUKTUR JAWABAN (ALUR BERPIKIR):
Gunakan alur ini, tapi **JANGAN TULIS LABELNYA** (seperti [EMPATHY]) di dalam chat. Tulislah mengalir natural seperti manusia.

1. (Validasi Emosi): 'Saya mengerti perasaan Anda...', 'Tentu berat mengalami hal itu...'
2. (Penjelasan): Jelaskan fenomena tersebut dari sisi Psikologi & Islam.
3. (Solusi): Berikan 1-2 tips terapi mandiri ringan.
4. (Action): Ajak konsultasi jika masalah berat.
5. (Referensi): Wajib cantumkan sumber jika ada. Format: *(Sumber: Alodokter - Anxiety)*.
6. (Engagement): Akhiri dengan 1 pertanyaan ringan untuk menjaga diskusi.

DATA KLINIK:
Nama: " . SITE_NAME . "
Tagline: " . SITE_TAGLINE . "
Alamat: " . ($settings['address'] ?? 'Jl. Imam Bonjol No. 123') . "
Buka: " . ($settings['operating_hours'] ?? '09:00 - 17:00') . "
Kontak: " . ADMIN_WHATSAPP . "

DATA PENDUKUNG:
[SERVICES]: " . $servicesInfo . "
[THERAPISTS]: " . $therapistsInfo . "
[TESTIMONIALS]: " . $testimonialsInfo . "
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

    private function searchRelevantKnowledge($msg, $onStatus = null)
    {
        $contextMatches = [];
        $seenContent = [];

        // 1. Vector Search (Semantic) - Limit 5
        // 1. Vector Search (Semantic) - Limit 5
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
            error_log("Vector Search Error: " . $e->getMessage());
        }

        // 2. Keyword Search (Literal) - Limit 3
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
        $s = ['apa', 'yang', 'dan', 'atau', 'saya', 'bisa', 'tidak'];
        return array_filter(explode(' ', strtolower($msg)), fn($w) => strlen($w) > 3 && !in_array($w, $s));
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
            // Silent fail - don't break chat if logging fails
            error_log("Performance logging failed: " . $e->getMessage());
        }
    }
}
