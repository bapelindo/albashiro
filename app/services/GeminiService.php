<?php
/**
 * Albashiro - Gemini AI Service
 * Handles all interactions with Google Gemini API for chatbot functionality
 */

class GeminiService
{
    private $apiKey;
    private $apiUrl;
    private $model;

    // Auto-learning tracking
    private $lastKnowledgeMatchCount = 0;
    private $lastSearchKeywords = '';

    public function __construct()
    {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_URL;
        $this->model = GEMINI_MODEL;
    }

    /**
     * Send message to Gemini AI and get response
     * 
     * @param string $userMessage The user's message
     * @param array $conversationHistory Previous messages for context
     * @return array Response with 'success', 'message', and optional 'error'
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        try {
            $startTime = microtime(true);

            // Build system context with Albashiro information (dynamic based on user question)
            $systemContext = $this->buildSystemContext($userMessage);

            // Prepare conversation contents
            $contents = [];

            // Add conversation history if exists
            foreach ($conversationHistory as $message) {
                $contents[] = [
                    'role' => $message['role'],
                    'parts' => [['text' => $message['content']]]
                ];
            }

            // Add current user message
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]]
            ];

            // Prepare request body
            $requestBody = [
                'system_instruction' => [
                    'parts' => [['text' => $systemContext]]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 4096,
                ]
            ];

            // Make API request with API key in URL
            $apiUrlWithKey = $this->apiUrl . '?key=' . $this->apiKey;

            $ch = curl_init($apiUrlWithKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new Exception("cURL Error: $curlError");
            }

            if ($httpCode !== 200) {
                $errorMsg = "API request failed with code: $httpCode";
                if ($response) {
                    error_log("GEMINI API ERROR - Full response: " . $response);
                    $errorMsg .= " - Response: " . substr($response, 0, 500);
                }
                throw new Exception($errorMsg);
            }

            $result = json_decode($response, true);

            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                throw new Exception("Invalid API response format");
            }

            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];

            // Calculate response time
            $responseTime = round((microtime(true) - $startTime) * 1000); // in milliseconds

            // Get knowledge metadata from search
            $knowledgeMatched = $this->lastKnowledgeMatchCount ?? 0;
            $keywordsSearched = $this->lastSearchKeywords ?? '';

            // Return response with metadata for auto-learning
            return [
                'response' => $aiResponse,
                'metadata' => [
                    'knowledge_matched' => $knowledgeMatched,
                    'keywords_searched' => $keywordsSearched,
                    'response_time_ms' => $responseTime
                ]
            ];

        } catch (Exception $e) {
            error_log("Gemini API Error: " . $e->getMessage());
            return [
                'response' => "Maaf, saya mengalami kesulitan teknis. Silakan hubungi admin via WhatsApp: " . ADMIN_WHATSAPP,
                'metadata' => [
                    'knowledge_matched' => 0,
                    'keywords_searched' => '',
                    'response_time_ms' => 0,
                    'error' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Build system context with Albashiro information
     */
    private function buildSystemContext($userMessage = '')
    {
        // Get services information
        $servicesInfo = $this->getServicesInfo();

        // Get therapists information
        $therapistsInfo = $this->getTherapistsInfo();

        // Get relevant knowledge based on user question
        $relevantKnowledge = $this->searchRelevantKnowledge($userMessage);

        // Check if user is asking about schedule availability
        $scheduleInfo = '';
        if (preg_match('/(jadwal|tersedia|available|booking|reservasi|slot)/i', $userMessage)) {
            error_log("Schedule keyword detected in message: $userMessage");

            // Advanced date parsing
            $queryDate = $this->extractDateFromMessage($userMessage);
            $queryDate = $queryDate ?? date('Y-m-d'); // Default to today if no date found

            $scheduleInfo = $this->getAvailableSchedules($queryDate);
            error_log("Schedule info returned for " . ($queryDate ?? 'today') . ": " . ($scheduleInfo ? "YES (" . strlen($scheduleInfo) . " chars)" : "EMPTY"));
        }

        $context = "Anda adalah asisten AI untuk Albashiro - Islamic Spiritual Hypnotherapy.\n\n";
        $context .= "INFORMASI PENTING:\n";
        $context .= "- Anda harus selalu ramah, profesional, dan menggunakan bahasa Indonesia yang sopan\n";
        $context .= "- Gunakan sapaan Islami seperti 'Assalamu'alaikum' jika sesuai konteks\n";
        $context .= "- Berikan informasi yang akurat. Prioritaskan data di bawah ini, namun GUNAKAN pengetahuan umum Anda untuk menjelaskan konsep psikologi/terapi yang belum ada di database.\n";
        $context .= "- Gunakan markdown formatting untuk membuat response lebih mudah dibaca (bold, list, dll)\n";
        $context .= "- Jika ditanya tentang HARGA, JADWAL, atau LOKASI spesifik yag tidak ada di data, arahkan ke admin.\n";
        $context .= "- Jika ditanya tentang KONSEP atau ISTILAH medis/psikologi umum yang tidak ada di data, jelaskan dengan pengetahuan Anda sebagai AI.\n";

        $context .= "\nPERAN KHUSUS (SYMPTOM CHECKER):\n";
        $context .= "- Anda juga adalah KONSULTAN KESEHATAN MENTAL yang empatik.\n";
        $context .= "- Jika user menceritakan keluhan/gejala (symptoms) seperti 'cemas', 'susah tidur', 'takut', ANALISA keluhan tersebut.\n";
        $context .= "- COCOKKAN keluhan dengan LAYANAN YANG TERSEDIA di bawah ini.\n";
        $context .= "- REKOMENDASIKAN layanan yang paling sesuai. Jelaskan kenapa layanan itu cocok.\n";
        $context .= "- CRITICAL RULE: Setelah merekomendasikan layanan, ANDA WAJIB BERTANYA: 'Apakah Anda ingin melihat jadwal tersedia untuk layanan ini?'\n";

        $context .= "\nCONTOH INTERAKSI:\n";
        $context .= "User: 'Saya sering merasa takut tanpa sebab dan jantung berdebar.'\n";
        $context .= "AI: 'Wa'alaikumussalam. Berdasarkan keluhan Anda, sepertinya Anda mengalami gejala kecemasan (Anxiety). Di Albashiro, kami memiliki layanan **Anxiety & Panic Attack Therapy** yang insya Allah cocok untuk membantu Anda tenang kembali.\\n\\nApakah Anda ingin melihat jadwal tersedia untuk layanan ini?'\n";

        $context .= "- Nomor WhatsApp admin: " . ADMIN_WHATSAPP . "\n";
        $context .= "- Email: " . ADMIN_EMAIL . "\n\n";

        $context .= "TENTANG ALBASHIRO:\n";
        $context .= "Albashiro adalah layanan hipnoterapi profesional dengan pendekatan Islami. ";
        $context .= "Kami membantu klien menemukan kedamaian jiwa dan mengatasi berbagai masalah psikologis ";
        $context .= "sesuai dengan nilai-nilai syariat Islam. Hipnoterapi Islami menggabungkan teknik hipnoterapi modern ";
        $context .= "dengan nilai-nilai spiritual Islam, menggunakan dzikir, doa, dan ayat-ayat Al-Quran dalam proses terapi.\n\n";

        $context .= "LOKASI & KONTAK:\n";
        $context .= "- Alamat: [Alamat lengkap klinik - perlu diisi]\n";
        $context .= "- WhatsApp: " . ADMIN_WHATSAPP . "\n";
        $context .= "- Email: " . ADMIN_EMAIL . "\n";
        $context .= "- Jam Operasional: Senin - Jumat, 09:00 - 17:00 WIB (Sabtu & Minggu Tutup)\n\n";

        // 🚨 SCHEDULE DATA FIRST - HIGHEST PRIORITY! 🚨
        if (!empty($scheduleInfo)) {
            $context .= "🚨🚨🚨 CRITICAL - REAL-TIME SCHEDULE FROM DATABASE 🚨🚨🚨\n";
            $context .= "==========================================\n";
            $context .= $scheduleInfo . "\n";
            $context .= "==========================================\n\n";
            $context .= "⚠️ MANDATORY INSTRUCTION:\n";
            $context .= "User asked about schedule. Real-time data is above.\n";
            $context .= "YOU MUST show the schedule EXACTLY as provided.\n";
            $context .= "DO NOT say 'technical difficulties' or redirect to WhatsApp for schedule.\n";
            $context .= "COPY the schedule slots above and show them to user.\n\n";

            $context .= "🎯 SMART NEXT AVAILABLE SLOT:\n";
            $context .= "- Jika user menanyakan JAM SPESIFIK (contoh: 'Jadwal jam 10?', 'Ada slot jam 14?') dan slot tersebut SUDAH BOOKED:\n";
            $context .= "  1. Beritahu bahwa jam tersebut penuh\n";
            $context .= "  2. LANGSUNG cari slot TERSEDIA terdekat (sebelum atau sesudah jam yang diminta)\n";
            $context .= "  3. TAWARKAN slot tersebut dengan pertanyaan langsung: 'Jam X penuh, tapi jam Y masih kosong. Mau ambil yang jam Y?'\n";
            $context .= "- Contoh: User tanya 'Jadwal jam 10?' → Jika jam 10 penuh, jawab: 'Jam 10:00 sudah penuh. Namun jam 11:00 masih tersedia. Apakah Anda mau booking jam 11:00?'\n\n";
        }

        $context .= "LAYANAN YANG TERSEDIA:\n";
        $context .= $servicesInfo . "\n";

        $context .= "TERAPIS KAMI:\n";
        $context .= $therapistsInfo . "\n";

        // Add relevant knowledge based on user question
        if (!empty($relevantKnowledge)) {
            $context .= "INFORMASI RELEVAN UNTUK PERTANYAAN ANDA:\n\n";
            $context .= $relevantKnowledge . "\n";
        }

        $context .= "CARA BOOKING:\n";
        $context .= "1. Pilih layanan dan terapis yang diinginkan\n";
        $context .= "2. Hubungi admin via WhatsApp (" . ADMIN_WHATSAPP . ") untuk cek ketersediaan jadwal\n";
        $context .= "3. Konfirmasi tanggal dan waktu sesi\n";
        $context .= "4. Lakukan pembayaran sesuai instruksi admin\n";
        $context .= "5. Dapatkan konfirmasi booking via WhatsApp\n\n";

        $context .= "CATATAN PENTING:\n";
        $context .= "- Untuk pertanyaan medis spesifik atau kondisi khusus, sarankan konsultasi langsung dengan terapis\n";
        $context .= "- Untuk booking final dan pembayaran, selalu arahkan ke admin WhatsApp\n";
        $context .= "- Jika ada pertanyaan yang tidak bisa dijawab, arahkan ke admin dengan sopan\n";
        $context .= "- Selalu berikan informasi yang akurat dan jangan membuat asumsi\n";
        $context .= "- Gunakan markdown formatting untuk membuat response lebih mudah dibaca\n";

        return $context;
    }

    /**
     * Search relevant knowledge based on user message
     */
    private function searchRelevantKnowledge($userMessage, $limit = 10)
    {
        if (empty($userMessage)) {
            return "";
        }

        try {
            $db = Database::getInstance();

            // Extract keywords from user message (simple approach)
            $keywords = $this->extractKeywords($userMessage);

            if (empty($keywords)) {
                return "";
            }

            // Build search query
            $searchTerms = implode('|', array_map('preg_quote', $keywords));

            // Search in question, answer, topic, and keywords fields
            $query = "
                SELECT category, topic, question, answer, priority
                FROM ai_knowledge_base 
                WHERE is_active = 1 
                AND (
                    question REGEXP ?
                    OR answer REGEXP ?
                    OR topic REGEXP ?
                    OR keywords REGEXP ?
                )
                ORDER BY priority DESC
                LIMIT ?
            ";

            // Use PDO directly for prepared statement with bind
            $pdo = $db->getPdo();
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(1, $searchTerms, PDO::PARAM_STR);
            $stmt->bindValue(2, $searchTerms, PDO::PARAM_STR);
            $stmt->bindValue(3, $searchTerms, PDO::PARAM_STR);
            $stmt->bindValue(4, $searchTerms, PDO::PARAM_STR);
            $stmt->bindValue(5, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

            if (empty($results)) {
                $this->lastKnowledgeMatchCount = 0;
                $this->lastSearchKeywords = implode(',', $keywords);
                return "";
            }

            // Track for auto-learning
            $this->lastKnowledgeMatchCount = count($results);
            $this->lastSearchKeywords = implode(',', $keywords);

            $info = "";
            foreach ($results as $item) {
                if (!empty($item->question)) {
                    $info .= "Q: {$item->question}\n";
                    $info .= "A: {$item->answer}\n\n";
                } else {
                    $info .= "{$item->topic}:\n{$item->answer}\n\n";
                }
            }

            return $info;
        } catch (Exception $e) {
            // If search fails, return empty
            return "";
        }
    }

    /**
     * Extract keywords from user message
     */
    private function extractKeywords($message)
    {
        // Convert to lowercase
        $message = strtolower($message);

        // Remove common words (stopwords)
        $stopwords = ['apa', 'yang', 'di', 'ke', 'dari', 'untuk', 'dengan', 'adalah', 'pada', 'dan', 'atau', 'saya', 'bisa', 'tidak', 'ada', 'ini', 'itu', 'ya', 'bagaimana', 'kapan', 'dimana', 'siapa', 'berapa', 'apakah'];

        // Split into words
        $words = preg_split('/\s+/', $message);

        // Filter out stopwords and short words
        $keywords = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 3 && !in_array($word, $stopwords);
        });

        // Return unique keywords
        return array_unique($keywords);
    }

    /**
     * Get available schedules for booking
     * 
     * @param string|null $date Date to check (Y-m-d format), defaults to today
     * @param int|null $therapistId Specific therapist ID to check
     * @return string Formatted schedule information
     */
    private function getAvailableSchedules($date = null, $therapistId = null)
    {
        try {
            $db = Database::getInstance();

            // Default to today if no date provided
            $date = $date ?? date('Y-m-d');
            $dateFormatted = date('d M Y', strtotime($date));

            // Define all possible time slots (Hourly 9 AM - 5 PM)
            $allSlots = [
                '09:00' => '09:00 - 10:00',
                '10:00' => '10:00 - 11:00',
                '11:00' => '11:00 - 12:00',
                '12:00' => '12:00 - 13:00',
                '13:00' => '13:00 - 14:00',
                '14:00' => '14:00 - 15:00',
                '15:00' => '15:00 - 16:00',
                '16:00' => '16:00 - 17:00'
            ];

            // Query booked slots for the date (NO personal info for privacy)
            $sql = "SELECT appointment_time
                    FROM bookings
                    WHERE DATE(appointment_date) = ?
                    AND status IN ('confirmed', 'pending')";

            $params = [$date];

            if ($therapistId) {
                $sql .= " AND therapist_id = ?";
                $params[] = $therapistId;
            }

            // Use Database wrapper's query method
            $bookedSlots = $db->query($sql, $params)->fetchAll();

            // Get booked time slots (Database returns objects, not arrays)
            $bookedTimes = [];
            foreach ($bookedSlots as $slot) {
                $bookedTimes[] = $slot->appointment_time;
            }

            // CHECK AVAILABILITY OVERRIDES (e.g. Sick Leave, Holiday)
            $sqlOverride = "SELECT * FROM availability_overrides 
                           WHERE override_date = ? AND is_available = 0";
            $overrideParams = [$date];
            if ($therapistId) {
                $sqlOverride .= " AND therapist_id = ?";
                $overrideParams[] = $therapistId;
            }

            $overrides = $db->query($sqlOverride, $overrideParams)->fetchAll();
            $blockedRanges = [];
            foreach ($overrides as $ovr) {
                // If start/end time is null/empty, it means FULL DAY BLOCK
                if (empty($ovr->start_time) || empty($ovr->end_time)) {
                    $blockedRanges[] = ['start' => '00:00', 'end' => '23:59', 'reason' => $ovr->reason ?? 'Tidak Tersedia'];
                } else {
                    $blockedRanges[] = ['start' => $ovr->start_time, 'end' => $ovr->end_time, 'reason' => $ovr->reason ?? 'Tidak Tersedia'];
                }
            }

            // Build schedule information
            $scheduleText = "JADWAL TERSEDIA ($dateFormatted):\n";
            $scheduleText .= "==========================================\n\n";

            $availableCount = 0;
            foreach ($allSlots as $time => $label) {
                // Check Overrides first
                $isBlocked = false;
                $blockReason = '';
                foreach ($blockedRanges as $range) {
                    // Check if slot start time falls within blocked range
                    if ($time >= $range['start'] && $time < $range['end']) {
                        $isBlocked = true;
                        $blockReason = $range['reason'];
                        break;
                    }
                }

                if ($isBlocked) {
                    $scheduleText .= "❌ $label - TIDAK TERSEDIA ($blockReason)\n";
                    continue; // Skip booking check if blocked
                }

                // Check Bookings
                $isBooked = false;
                $slotHour = substr($time, 0, 2); // Get "09", "10", etc.

                foreach ($bookedTimes as $bookedTime) {
                    // Check if booking starts in this hour (e.g. 10:30 matches 10:00 slot)
                    // OR exact match
                    if (substr($bookedTime, 0, 2) === $slotHour) {
                        $isBooked = true;
                        break;
                    }
                }

                if ($isBooked) {
                    $scheduleText .= "❌ $label - SUDAH BOOKED\n";
                } else {
                    $scheduleText .= "✅ $label - TERSEDIA\n";
                    $availableCount++;
                }
            }

            $scheduleText .= "\n";
            $scheduleText .= "Total slot tersedia: $availableCount dari " . count($allSlots) . " slot\n\n";

            if ($availableCount > 0) {
                $scheduleText .= "Untuk booking, silakan hubungi admin via WhatsApp: " . ADMIN_WHATSAPP . "\n";
            } else {
                $scheduleText .= "Maaf, semua slot sudah penuh untuk tanggal ini. Silakan pilih tanggal lain.\n";
            }

            return $scheduleText;

        } catch (Exception $e) {
            error_log("Schedule check error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // Return a fallback message instead of empty string
            return "JADWAL: Terjadi error saat mengecek jadwal. Silakan hubungi admin via WhatsApp: " . ADMIN_WHATSAPP . "\n";
        }
    }

    /**
     * Get comprehensive knowledge base from database
     */
    private function getKnowledgeBase()
    {
        try {
            $db = Database::getInstance();
            $knowledge = $db->query("
                SELECT category, topic, question, answer 
                FROM ai_knowledge_base 
                WHERE is_active = 1 
                ORDER BY priority DESC, category, topic
            ")->fetchAll();

            if (empty($knowledge)) {
                return ""; // Return empty if no knowledge base yet
            }

            $info = "KNOWLEDGE BASE - INFORMASI LENGKAP:\n\n";
            $currentCategory = "";

            foreach ($knowledge as $item) {
                // Add category header if changed
                if ($item->category !== $currentCategory) {
                    $currentCategory = $item->category;
                    $categoryName = strtoupper(str_replace('_', ' ', $currentCategory));
                    $info .= "\n=== {$categoryName} ===\n\n";
                }

                // Add Q&A
                if (!empty($item->question)) {
                    $info .= "Q: {$item->question}\n";
                    $info .= "A: {$item->answer}\n\n";
                } else {
                    $info .= "{$item->topic}:\n{$item->answer}\n\n";
                }
            }

            return $info;
        } catch (Exception $e) {
            // If table doesn't exist yet, return empty
            return "";
        }
    }

    /**
     * Get services information from database
     */
    private function getServicesInfo()
    {
        try {
            $db = Database::getInstance();
            $services = $db->query("SELECT name, description, price, duration FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();

            if (empty($services)) {
                return "Informasi layanan sedang tidak tersedia. Silakan hubungi admin.";
            }

            $info = "";
            foreach ($services as $service) {
                $info .= "- {$service->name}\n";
                $info .= "  Deskripsi: {$service->description}\n";
                $info .= "  Harga: " . format_rupiah($service->price) . "\n";
                $info .= "  Durasi: {$service->duration} menit\n\n";
            }

            return $info;
        } catch (Exception $e) {
            return "Informasi layanan sedang tidak tersedia.";
        }
    }

    /**
     * Get therapists information from database
     */
    private function getTherapistsInfo()
    {
        try {
            $db = Database::getInstance();
            $therapists = $db->query("SELECT name, title, specialty, bio, experience_years FROM therapists WHERE is_active = 1 ORDER BY name")->fetchAll();

            if (empty($therapists)) {
                return "Informasi terapis sedang tidak tersedia. Silakan hubungi admin.";
            }

            $info = "";
            foreach ($therapists as $therapist) {
                $info .= "- {$therapist->name}, {$therapist->title}\n";
                $info .= "  Spesialisasi: {$therapist->specialty}\n";
                $info .= "  Pengalaman: {$therapist->experience_years} tahun\n";
                $info .= "  Bio: {$therapist->bio}\n\n";
            }

            return $info;
        } catch (Exception $e) {
            return "Informasi terapis sedang tidak tersedia.";
        }
    }

    /**
     * Check therapist availability for a specific date
     * 
     * @param int $therapistId
     * @param string $date Format: Y-m-d
     * @return string Formatted availability information
     */
    public function checkAvailability($therapistId, $date)
    {
        try {
            require_once SITE_ROOT . '/app/models/Availability.php';
            $availabilityModel = new Availability();

            $slots = $availabilityModel->getAvailableSlots($therapistId, $date);

            if (empty($slots)) {
                return "Maaf, tidak ada slot tersedia pada tanggal tersebut.";
            }

            $info = "Slot tersedia pada " . format_date_id($date) . ":\n";
            foreach ($slots as $slot) {
                $info .= "- {$slot['display']}\n";
            }

            return $info;
        } catch (Exception $e) {
            return "Maaf, tidak dapat mengecek ketersediaan saat ini. Silakan hubungi admin.";
        }
    }

    /**
     * Make HTTP request to Gemini API
     */
    private function makeRequest($payload)
    {
        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Disable SSL verification for localhost development
        // IMPORTANT: Remove these lines in production!
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        // Note: curl_close() is deprecated in PHP 8.5+ and called automatically


        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode . ' - ' . $response
            ];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'JSON Parse Error: ' . json_last_error_msg()
            ];
        }

        // Extract message from Gemini response
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'message' => $data['candidates'][0]['content']['parts'][0]['text']
            ];
        }

        return [
            'success' => false,
            'error' => 'Unexpected API response format'
        ];
    }

    /**
     * Extract date from user message supporting Indonesian formats
     */
    private function extractDateFromMessage($message)
    {
        $message = strtolower($message);

        // 1. Keywords: Besok, Lusa
        if (strpos($message, 'besok') !== false) {
            return date('Y-m-d', strtotime('+1 day'));
        }
        if (strpos($message, 'lusa') !== false) {
            return date('Y-m-d', strtotime('+2 days'));
        }
        if (strpos($message, 'hari ini') !== false) {
            return date('Y-m-d');
        }

        // 2. Pattern: "27 Desember" (Date + Month) - CHECK FIRST!
        // Map ID months to EN
        $months = [
            'januari' => 'january',
            'februari' => 'february',
            'maret' => 'march',
            'april' => 'april',
            'mei' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'agustus' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'nopember' => 'november',
            'desember' => 'december'
        ];

        foreach ($months as $id => $en) {
            if (strpos($message, $id) !== false) {
                // Found a month, look for the day before it
                if (preg_match('/(\d{1,2})\s+' . $id . '/i', $message, $matches)) {
                    $day = $matches[1];
                    $dateString = "$day $en " . date('Y');
                    return date('Y-m-d', strtotime($dateString));
                }
            }
        }

        // 3. Pattern: "Tanggal 27" or "Tgl 27" (Only day specified)
        if (preg_match('/(tanggal|tgl)\s+(\d{1,2})/i', $message, $matches)) {
            $day = $matches[2];
            $month = date('m');
            $year = date('Y');
            return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        }

        return null;
    }
}
