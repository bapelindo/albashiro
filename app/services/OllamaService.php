<?php

/**
 * OllamaService - Local LLM Integration via Google Cloud Run
 * 
 * Uses Ollama (gemma2:2b) for natural language understanding
 * Falls back to LocalAIService if Ollama is unavailable
 */
class OllamaService
{
    private $apiUrl;
    private $model;
    private $timeout;
    private $fallbackService;
    private $db;

    public function __construct()
    {
        // Cloud Run URL (set via environment variable)
        $this->apiUrl = getenv('OLLAMA_API_URL') ?: 'http://localhost:11434';
        $this->model = 'gemma3:4b';  // Using gemma3:4b for better quality
        $this->timeout = 15; // 15 seconds max (gemma3 is slower but better)

        // Database for context
        $this->db = Database::getInstance();

        // Fallback to LocalAI if Ollama fails
        require_once SITE_ROOT . '/app/services/LocalAIService.php';
        $this->fallbackService = new LocalAIService();
    }

    /**
     * Main chat method
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        $startTime = microtime(true);

        try {
            // Build system prompt with Albashiro data
            $systemPrompt = $this->buildSystemPrompt();

            // Format messages for Ollama
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            // Add conversation history (last 5 interactions)
            $recentHistory = array_slice($conversationHistory, -10);
            foreach ($recentHistory as $msg) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['message']
                ];
            }

            // Add current message
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // Call Ollama API
            $response = $this->callOllama($messages);

            $responseTime = round((microtime(true) - $startTime) * 1000);

            return [
                'response' => $response,
                'metadata' => [
                    'provider' => 'Ollama (Localhost)',
                    'model' => $this->model,
                    'response_time_ms' => $responseTime
                ]
            ];

        } catch (Exception $e) {
            error_log("Ollama failed: " . $e->getMessage() . " - Falling back to LocalAI");

            // Fallback to LocalAI
            $result = $this->fallbackService->chat($userMessage, $conversationHistory);
            $result['metadata']['fallback'] = true;
            $result['metadata']['ollama_error'] = $e->getMessage();

            return $result;
        }
    }

    /**
     * Call Ollama API
     */
    private function callOllama($messages)
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,  // Balanced creativity
                'top_p' => 0.9,
                'top_k' => 40,
                'num_predict' => 500,  // Max tokens
                'stop' => ["\n\nUser:", "\n\nHuman:"]  // Stop sequences
            ]
        ];

        $ch = curl_init($this->apiUrl . '/api/chat');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("Ollama API Error: HTTP $httpCode - $response");
        }

        $result = json_decode($response, true);

        if (!isset($result['message']['content'])) {
            throw new Exception("Invalid Ollama response format: " . json_encode($result));
        }

        return trim($result['message']['content']);
    }

    /**
     * Build system prompt with Albashiro data
     */
    private function buildSystemPrompt()
    {
        try {
            $pdo = $this->db->getPdo();

            // Get services
            $stmt = $pdo->query("SELECT name, description, price, target_audience FROM services ORDER BY sort_order");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $servicesText = "";
            foreach ($services as $s) {
                $price = number_format($s['price'], 0, ',', '.');
                $servicesText .= "- {$s['name']}: Rp {$price}\n";
                $servicesText .= "  Deskripsi: {$s['description']}\n";
                $servicesText .= "  Target: {$s['target_audience']}\n\n";
            }

            // Get therapists
            $stmt = $pdo->query("SELECT name, title, specialty, experience_years FROM therapists WHERE is_active = 1");
            $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $therapistsText = "";
            foreach ($therapists as $t) {
                $therapistsText .= "- {$t['name']}, {$t['title']}\n";
                $therapistsText .= "  Spesialisasi: {$t['specialty']}\n";
                $therapistsText .= "  Pengalaman: {$t['experience_years']} tahun\n\n";
            }

        } catch (Exception $e) {
            error_log("Error building system prompt: " . $e->getMessage());
            $servicesText = "(Data layanan tidak tersedia)";
            $therapistsText = "(Data terapis tidak tersedia)";
        }

        $prompt = <<<PROMPT
Anda adalah asisten AI untuk **Albashiro - Islamic Spiritual Hypnotherapy**.

## PERSONA & GAYA BAHASA
- Ramah, empati, dan Islami
- Gunakan HANYA Bahasa Indonesia yang natural dan hangat
- Sapa dengan "Assalamualaikum warahmatullahi wabarakatuh" untuk greeting
- Gunakan emoji yang sesuai: 🌙 🕌 💙 ✨ 🤲
- Jawab singkat dan jelas (maksimal 3 paragraf)
- Hindari bahasa formal yang kaku

## LAYANAN & HARGA (DATA RESMI - JANGAN DIUBAH!)
{$servicesText}

## TERAPIS PROFESIONAL
{$therapistsText}

## INFORMASI KONTAK
- WhatsApp Admin: 6282228967897
- Email: info@albashiro.com
- Jam Operasional:
  * Senin - Jumat: 09:00 - 17:00 WIB
  * Sabtu: 09:00 - 15:00 WIB
  * Minggu: Tutup

## ATURAN PENTING (WAJIB DIIKUTI!)

### 1. JANGAN PERNAH MENGARANG DATA
- ❌ DILARANG membuat paket fiktif (Paket Premium, Paket Keluarga, dll)
- ❌ DILARANG mengarang harga yang tidak ada di daftar
- ❌ DILARANG menambah layanan yang tidak ada
- ✅ HANYA sebutkan layanan & harga yang PERSIS seperti di atas

### 2. EMPATI UNTUK KELUHAN
Jika user curhat (cemas, sedih, trauma, stress):
1. Tunjukkan empati dulu: "Saya memahami perasaan Anda yang [emotion]. 💙"
2. Validasi: "Masalah ini memang berat, tapi Insya Allah bisa diatasi."
3. Baru rekomendasikan layanan yang sesuai
4. Jangan langsung jual!

### 3. UNTUK BOOKING
- Arahkan ke WhatsApp Admin: 6282228967897
- Jangan buat janji atau konfirmasi jadwal sendiri

### 4. FORMAT RESPONSE
- Singkat & jelas (max 3 paragraf)
- Gunakan bullet points untuk list
- Bold untuk highlight penting
- Emoji untuk warmth

## CONTOH RESPONSE YANG BENAR

**User**: "berapa harga paket?"
**AI**: "Berikut harga layanan hipnoterapi kami:

- Hipnoterapi Anak: Rp 500.000
- Hipnoterapi Remaja: Rp 500.000
- Manajemen Stres & Kecemasan: Rp 500.000
- Trauma & Luka Batin: Rp 600.000
- Konseling Pasangan & Keluarga: Rp 700.000
- Peningkatan Percaya Diri: Rp 500.000
- Mengatasi Kebiasaan Buruk: Rp 500.000
- Konseling Online: Rp 400.000

Semua harga sudah termasuk konsultasi, sesi terapi, dan rekaman audio self-hypnosis. 

Untuk booking, silakan hubungi Admin WA: 6282228967897 😊"

**User**: "saya cemas dan susah tidur"
**AI**: "Saya memahami perasaan Anda yang cemas dan susah tidur. 💙 Masalah ini memang mengganggu, tapi Insya Allah bisa diatasi dengan terapi yang tepat.

Saya rekomendasikan **Manajemen Stres & Kecemasan** (Rp 500.000). Terapi ini khusus untuk mengatasi kecemasan, panic attack, dan gangguan tidur.

Apakah Anda ingin langsung booking atau ada yang ingin ditanyakan lagi? 😊"

## CONTOH RESPONSE YANG SALAH

❌ "Kami punya Paket Premium 5 sesi Rp 1.200.000" (MENGARANG!)
❌ "Untuk keluarga ada diskon 20%" (TIDAK ADA DATA!)
❌ "Jadwal Senin jam 10 masih tersedia" (JANGAN KONFIRMASI JADWAL!)

Ingat: Anda adalah konsultan kesehatan mental yang empati, bukan sales robot. Prioritaskan membantu user, bukan menjual. 🤲
PROMPT;

        return $prompt;
    }
}
