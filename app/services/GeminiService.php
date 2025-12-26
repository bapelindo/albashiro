<?php
/**
 * Albashiro - Gemini AI Service (Google Direct)
 * Handles interactions with Google Gemini API directly
 */

class GeminiService
{
    private $apiKey;
    private $model;
    private $db;

    // Auto-learning metadata
    private $lastKnowledgeMatchCount = 0;
    private $lastSearchKeywords = '';

    // Fallback models (Hugging Face - Optimized for Vercel 10s limit)
    private $fallbackModels = [
        'Qwen/Qwen2.5-72B-Instruct',        // Prioritas 1: Paling pintar, support Indonesia
        'meta-llama/Meta-Llama-3-8B-Instruct'   // Prioritas 2: Backup ringan & cepat
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->apiKey = GOOGLE_API_KEY;
        $this->model = defined('GOOGLE_MODEL') ? GOOGLE_MODEL : 'gemini-1.5-flash';
    }

    /**
     * Smart Multi-Provider Chat (Google Primary → HF Fallback)
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        $startTime = microtime(true);
        $lastError = "Unknown error";
        $systemContext = $this->buildSystemContext($userMessage);

        // Try Google First (if configured)
        if (!empty($this->apiKey)) {
            try {
                $response = $this->tryGoogle($userMessage, $conversationHistory, $systemContext);
                $responseTime = round((microtime(true) - $startTime) * 1000);
                return [
                    'response' => $response,
                    'metadata' => [
                        'knowledge_matched' => $this->lastKnowledgeMatchCount,
                        'keywords_searched' => $this->lastSearchKeywords,
                        'response_time_ms' => $responseTime,
                        'provider' => 'Google Gemini'
                    ]
                ];
            } catch (Exception $e) {
                // Google failed (quota/error), try fallback
                if (strpos($e->getMessage(), '429') !== false || strpos($e->getMessage(), 'quota') !== false) {
                    // Quota exceeded - try Hugging Face
                }
            }
        }

        // Fallback: Hugging Face
        if (defined('HUGGINGFACE_API_KEY') && !empty(HUGGINGFACE_API_KEY)) {
            foreach ($this->fallbackModels as $model) {
                try {
                    $response = $this->tryHuggingFace($userMessage, $conversationHistory, $systemContext, $model);
                    $responseTime = round((microtime(true) - $startTime) * 1000);
                    return [
                        'response' => $response,
                        'metadata' => [
                            'knowledge_matched' => $this->lastKnowledgeMatchCount,
                            'keywords_searched' => $this->lastSearchKeywords,
                            'response_time_ms' => $responseTime,
                            'provider' => 'Hugging Face (' . $model . ')'
                        ]
                    ];
                } catch (Exception $e) {
                    $lastError = "Hugging Face ({$model}) Failed: " . $e->getMessage();
                    continue; // Try next model
                }
            }
        } else {
            $lastError = "Hugging Face API Key not configured.";
        }

        // All providers failed
        error_log("ALL AI MODELS FAILED. Final Error: " . $lastError);
        return [
            'response' => "Maaf, sistem sedang sangat sibuk. Silakan coba lagi dalam beberapa saat.",
            'metadata' => ['error' => true, 'debug_last_error' => $lastError, 'google_key_set' => !empty($this->apiKey), 'hf_key_set' => defined('HUGGINGFACE_API_KEY') && !empty(HUGGINGFACE_API_KEY)]
        ];
    }

    /**
     * Try Google Gemini API
     */
    private function tryGoogle($userMessage, $conversationHistory, $systemContext)
    {
        if (empty($this->apiKey)) {
            throw new Exception("GOOGLE_API_KEY belum dikonfigurasi.");
        }

        $contents = [];
        foreach ($conversationHistory as $msg) {
            $role = $msg['role'] === 'ai' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['message']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $payload = [
            'contents' => $contents,
            'systemInstruction' => ['parts' => [['text' => $systemContext]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1000]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Connection timeout: 3s
        curl_setopt($ch, CURLOPT_TIMEOUT, 7); // Total timeout: 7s
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError)
            throw new Exception("cURL Error: $curlError");

        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            $errMsg = isset($result['error']['message']) ? $result['error']['message'] : $response;
            throw new Exception("Google API Error ($httpCode): $errMsg");
        }

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            // Handle Finish Reason (e.g. SAFETY)
            if (isset($result['candidates'][0]['finishReason'])) {
                throw new Exception("Response blocked: " . $result['candidates'][0]['finishReason']);
            }
            throw new Exception("Invalid Google API Response");
        }
    }

    /**
     * Try Hugging Face API
     */
    private function tryHuggingFace($userMessage, $conversationHistory, $systemContext, $model)
    {
        $messages = [['role' => 'system', 'content' => $systemContext]];
        foreach ($conversationHistory as $msg) {
            $role = $msg['role'] === 'ai' ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $msg['message']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => 1024,
            'temperature' => 0.7
        ];

        $ch = curl_init(HUGGINGFACE_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . HUGGINGFACE_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 6); // Vercel limit: fast fallback
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError)
            throw new Exception("cURL Error (Hugging Face): $curlError");

        if ($httpCode !== 200) {
            throw new Exception("HF Error ($httpCode): " . $response);
        }

        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        throw new Exception("Invalid HF response");
    }

    /**
     * Build system context with Albashiro information
     */
    private function buildSystemContext($userMessage = '')
    {
        // Fast-fail database queries (max 2 seconds total for all DB calls)
        $servicesInfo = '';
        $therapistsInfo = '';
        $relevantKnowledge = '';
        $scheduleInfo = '';

        try {
            // Quick timeout for services (500ms max)
            $servicesInfo = $this->getServicesInfo();
        } catch (Exception $e) {
            // CRITICAL: Tell AI that data failed to load
            $servicesInfo = "⚠️ ERROR: Database tidak dapat diakses. JANGAN MENGARANG HARGA! Arahkan user ke Admin WA: " . ADMIN_WHATSAPP . "\n";
            error_log("CRITICAL: getServicesInfo failed - " . $e->getMessage());
        }

        try {
            // Quick timeout for therapists (500ms max)
            $therapistsInfo = $this->getTherapistsInfo();
        } catch (Exception $e) {
            $therapistsInfo = "(Data terapis tidak tersedia - hubungi admin)\n";
            error_log("WARNING: getTherapistsInfo failed - " . $e->getMessage());
        }

        try {
            // Knowledge base (500ms max)
            $relevantKnowledge = $this->searchRelevantKnowledge($userMessage);
        } catch (Exception $e) {
            // Silently fail
        }

        // Only check schedule if explicitly asked (this is slow)
        if (preg_match('/(jadwal|tersedia|available|booking|reservasi|slot|kosong|jam|tanggal)/i', $userMessage)) {
            try {
                $queryDate = $this->extractDateFromMessage($userMessage) ?? date('Y-m-d');
                $therapistId = $this->extractTherapistFromMessage($userMessage);
                $scheduleInfo = $this->getAvailableSchedules($queryDate, $therapistId);
            } catch (Exception $e) {
                // Silently fail
            }
        }

        $context = "
السلام عليكم ورحمة الله وبركاته
Assalamu'alaikum Warahmatullahi Wabarakatuh 🌙

Selamat datang di Albashiro - Islamic Spiritual Hypnotherapy

بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ

Saya adalah asisten AI yang siap membantu Anda dengan penuh empati. Silakan konsultasikan:

✨ Keluhan & Gejala - Ceritakan apa yang Anda rasakan
🕌 Layanan Hipnoterapi Islami - Terapi sesuai syariat
💰 Harga & Paket - Informasi investasi kesehatan jiwa
👨‍⚕️ Terapis Profesional - Ustadz/Ustadzah berpengalaman
📅 Jadwal Tersedia - Cek slot real-time
📍 Lokasi & Kontak - Informasi klinik

💬 Silakan ketik pertanyaan Anda, atau ceritakan keluhan yang Anda alami. Insya Allah saya akan membantu menemukan solusi terbaik.

جزاك الله خيرا

[INSTRUKSI KHUSUS]
- Jawab SELALU dalam Bahasa Indonesia.
- Jangan pernah menggunakan Bahasa Inggris kecuali untuk istilah teknis yang tidak ada padanannya.
- Jika user mengetik sembarangan (seperti \"asd\"), tetap jawab dengan sopan dalam Bahasa Indonesia, arahkan kembali ke topik hipnoterapi.

[HIERARKI SUMBER DATA - WAJIB DIIKUTI]
1. **PRIORITAS UTAMA**: Gunakan data dari [LAYANAN & HARGA RESMI], [TERAPIS], [DATA JADWAL REAL-TIME], dan [KNOWLEDGE BASE] yang disediakan di bawah.
2. **JANGAN MENGARANG**: Untuk pertanyaan tentang harga, layanan, terapis, atau jadwal → WAJIB pakai data yang tersedia.
3. **Pengetahuan Umum**: HANYA untuk topik di luar data yang tersedia (misal: penjelasan umum tentang hipnoterapi, psikologi, kesehatan mental) → Boleh pakai pengetahuan AI sendiri.
4. **Contoh**:
   - SALAH: Paket Individual 5 sesi Rp 2.500.000 (MENGARANG - tidak ada di data)
   - BENAR: Hipnoterapi Anak Rp 500.000 (sesuai data LAYANAN & HARGA RESMI)
   - BENAR: Hipnoterapi adalah metode terapi... (pengetahuan umum, OK)

[INFORMASI PENTING & LOGIKA]
- Anda adalah KONSULTAN KESEHATAN MENTAL (bukan sekadar bot).
- Jika user curhat (cemas, takut, sedih), berempati lah. Jangan langsung jual produk. Validasi perasaannya dulu ('Saya mengerti perasaan Anda...').
- Setelah berempati, baru sarankan LAYANAN yang cocok dari daftar di bawah.

[DATA JADWAL REAL-TIME]
";

        if (!empty($scheduleInfo)) {
            $context .= "⚠️ USER BERTANYA JADWAL. DATA INI ADALAH FAKTA REAL-TIME DR DATABASE. JANGAN MENGARANG.\n";
            $context .= $scheduleInfo . "\n";
            $context .= "Instruksi Jadwal:\n";
            $context .= "1. Tampilkan slot yang '✅ TERSEDIA' kepada user.\n";
            $context .= "2. Jika slot yang diminta penuh, tawarkan slot lain yang kosong.\n";
            $context .= "3. Jika semua penuh, arahkan ke Hari Lain atau ke Admin WA.\n\n";
        }

        $context .= "[DATA KLINIK]\n";
        $context .= "- Admin WhatsApp: " . ADMIN_WHATSAPP . "\n";
        $context .= "- Email: " . ADMIN_EMAIL . "\n";
        $context .= "- Lokasi: [Lokasi Klinik Albashiro]\n";
        $context .= "- Jam Buka: Senin-Jumat 09:00-17:00\n\n";

        $context .= "[LAYANAN & HARGA RESMI]\n" . $servicesInfo . "\n";
        $context .= "⚠️ PENTING: HARGA DI ATAS ADALAH DATA RESMI DARI DATABASE. JANGAN MENGARANG ATAU MEMBUAT PAKET FIKTIF.\n\n";

        $context .= "[TERAPIS]\n" . $therapistsInfo . "\n\n";

        if (!empty($relevantKnowledge)) {
            $context .= "[KNOWLEDGE BASE - Q&A]\n" . $relevantKnowledge . "\n\n";
        }

        return $context;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    private function getServicesInfo()
    {
        try {
            // Use PDO directly for better error visibility
            $pdo = $this->db->getPdo();
            error_log("DEBUG getServicesInfo: PDO connection obtained");

            // Query all services (no is_active column in services table)
            $stmt = $pdo->query("SELECT name, description, price FROM services ORDER BY sort_order");
            error_log("DEBUG getServicesInfo: Query executed");

            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG getServicesInfo: Fetched " . count($services) . " services");

            if (empty($services)) {
                error_log("WARNING: No services found in database!");
                // Return explicit message so AI knows data is missing
                return "⚠️ DATA LAYANAN KOSONG DI DATABASE - JANGAN MENGARANG HARGA!\n";
            }

            $output = "";
            foreach ($services as $s) {
                // Format price as range
                $price = number_format($s['price'], 0, ',', '.');
                $output .= "- **{$s['name']}**: {$s['description']} (Harga: Rp $price)\n";
                error_log("DEBUG: Service added: {$s['name']} - Rp $price");
            }
            error_log("DEBUG getServicesInfo: Returning " . strlen($output) . " chars");
            return $output;
        } catch (Exception $e) {
            error_log("ERROR getServicesInfo: " . $e->getMessage());
            error_log("ERROR Stack trace: " . $e->getTraceAsString());
            return "⚠️ ERROR DATABASE: " . $e->getMessage() . " - JANGAN MENGARANG!\n";
        }
    }

    private function getTherapistsInfo()
    {
        try {
            $pdo = $this->db->getPdo();
            // Use correct column name: specialty (not specialization)
            $stmt = $pdo->query("SELECT name, specialty FROM therapists WHERE is_active = 1");
            $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($therapists)) {
                error_log("WARNING: No therapists found in database!");
                return "(Data terapis tidak tersedia - hubungi admin)\n";
            }

            $output = "";
            foreach ($therapists as $t) {
                $output .= "- **{$t['name']}** (Spesialisasi: {$t['specialty']})\n";
            }
            return $output;
        } catch (Exception $e) {
            error_log("ERROR getTherapistsInfo: " . $e->getMessage());
            return "(Error mengambil data terapis: " . $e->getMessage() . ")\n";
        }
    }

    private function searchRelevantKnowledge($userMessage, $limit = 5)
    {
        if (empty($userMessage))
            return "";
        try {
            $pdo = $this->db->getPdo();
            $keywords = $this->extractKeywords($userMessage);
            if (empty($keywords))
                return "";

            $clause = [];
            $params = [];
            foreach ($keywords as $k) {
                $clause[] = "question LIKE ?";
                $clause[] = "answer LIKE ?";
                $params[] = "%$k%";
                $params[] = "%$k%";
            }
            if (empty($clause))
                return "";

            $sql = "SELECT question, answer FROM ai_knowledge_base WHERE is_active=1 AND (" . implode(' OR ', $clause) . ") LIMIT $limit";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Native PDO fetchAll still accepts args

            $this->lastKnowledgeMatchCount = count($results);
            $this->lastSearchKeywords = implode(',', $keywords);

            if (empty($results))
                return "";

            $out = "";
            foreach ($results as $r) {
                $out .= "Q: {$r['question']}\nA: {$r['answer']}\n---\n";
            }
            return $out;

        } catch (Exception $e) {
            return "";
        }
    }

    private function extractKeywords($message)
    {
        $message = strtolower($message);
        $stopwords = ['apa', 'yang', 'di', 'ke', 'dari', 'untuk', 'dengan', 'adalah', 'pada', 'dan', 'atau', 'saya', 'bisa', 'tidak', 'ada', 'ini', 'itu', 'ya', 'bagaimana', 'kapan', 'dimana', 'siapa', 'berapa', 'apakah'];
        $words = preg_split('/\s+/', $message);
        $keywords = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 3 && !in_array($word, $stopwords);
        });
        return array_unique($keywords);
    }


    private function getAvailableSchedules($date, $therapistId)
    {
        try {
            $dateFormatted = date('d M Y', strtotime($date));
            $allSlots = [
                '09:00' => '09:00 - 10:00',
                '10:00' => '10:00 - 11:00',
                '11:00' => '11:00 - 12:00',
                '13:00' => '13:00 - 14:00',
                '14:00' => '14:00 - 15:00',
                '15:00' => '15:00 - 16:00',
                '16:00' => '16:00 - 17:00'
            ];

            $pdo = $this->db->getPdo();
            $sql = "SELECT appointment_time FROM bookings WHERE DATE(appointment_date) = ? AND status IN ('confirmed', 'pending', 'paid')";
            $params = [$date];

            if ($therapistId) {
                $sql .= " AND therapist_id = ?";
                $params[] = $therapistId;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $bookedRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bookedTimes = [];
            foreach ($bookedRaw as $b) {
                $bookedTimes[] = substr($b['appointment_time'], 0, 5);
            }

            $out = "Status Jadwal Real-time Tanggal $dateFormatted:\n";
            $count = 0;
            foreach ($allSlots as $time => $label) {
                $isBooked = in_array($time, $bookedTimes);

                if ($isBooked) {
                    $out .= "❌ $label: PENUH\n";
                } else {
                    $out .= "✅ $label: TERSEDIA\n";
                    $count++;
                }
            }

            if ($count == 0)
                $out .= "\n(Semua slot penuh, silakan cek hari lain)";
            return $out;

        } catch (Exception $e) {
            error_log("ERROR getAvailableSchedules: " . $e->getMessage());
            return "(Error mengambil jadwal: " . $e->getMessage() . ")\n";
        }
    }

    private function extractDateFromMessage($message)
    {
        $message = strtolower($message);
        if (strpos($message, 'besok') !== false)
            return date('Y-m-d', strtotime('+1 day'));
        if (strpos($message, 'lusa') !== false)
            return date('Y-m-d', strtotime('+2 days'));
        if (strpos($message, 'hari ini') !== false)
            return date('Y-m-d');
        if (preg_match('/(tanggal|tgl)\s+(\d{1,2})/i', $message, $m)) {
            return date('Y-m-') . str_pad($m[2], 2, '0', STR_PAD_LEFT);
        }
        return null;
    }

    private function extractTherapistFromMessage($message)
    {
        return null;
    }
}
