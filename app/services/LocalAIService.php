<?php

/**
 * LocalAIService - Standalone Rule-Based AI Chatbot
 * 
 * No external API dependencies - 100% local processing
 * Fast, reliable, and never hallucinates
 */
class LocalAIService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Main chat method - processes user message and returns response
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        $startTime = microtime(true);

        // Normalize message
        $message = trim(strtolower($userMessage));

        // Detect intent
        $intent = $this->detectIntent($message);

        // Build response based on intent
        $response = '';
        switch ($intent) {
            case 'greeting':
                $response = $this->respondGreeting();
                break;
            case 'harga':
                $response = $this->respondHarga($message);
                break;
            case 'layanan':
                $response = $this->respondLayanan($message);
                break;
            case 'terapis':
                $response = $this->respondTerapis($message);
                break;
            case 'jadwal':
                $response = $this->respondJadwal($message);
                break;
            case 'keluhan':
                $response = $this->respondKeluhan($message);
                break;
            case 'kontak':
                $response = $this->respondKontak();
                break;
            case 'lokasi':
                $response = $this->respondLokasi();
                break;
            default:
                $response = $this->respondGeneral($message);
        }

        $responseTime = round((microtime(true) - $startTime) * 1000);

        return [
            'response' => $response,
            'metadata' => [
                'intent' => $intent,
                'response_time_ms' => $responseTime,
                'provider' => 'Local AI (Rule-Based)',
                'api_calls' => 0
            ]
        ];
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent($message)
    {
        // Greeting
        if (preg_match('/(assalam|salam|halo|hai|hi|hello|selamat|pagi|siang|sore|malam)/i', $message)) {
            return 'greeting';
        }

        // Harga / Biaya
        if (preg_match('/(harga|biaya|tarif|paket|berapa|cost|bayar|pembayaran|ongkos)/i', $message)) {
            return 'harga';
        }

        // Jadwal / Booking
        if (preg_match('/(jadwal|tersedia|booking|reservasi|kapan|jam|hari|tanggal|slot|kosong|buka|tutup)/i', $message)) {
            return 'jadwal';
        }

        // Layanan / Service
        if (preg_match('/(layanan|terapi|hipnoterapi|service|apa saja|jenis|macam|treatment)/i', $message)) {
            return 'layanan';
        }

        // Terapis
        if (preg_match('/(terapis|therapist|dokter|ustadz|ustadzah|praktisi|ahli)/i', $message)) {
            return 'terapis';
        }

        // Keluhan (needs empathy)
        if (preg_match('/(cemas|takut|sedih|depresi|trauma|stress|susah tidur|insomnia|panik|galau|bingung|putus asa)/i', $message)) {
            return 'keluhan';
        }

        // Kontak
        if (preg_match('/(kontak|hubungi|whatsapp|wa|telepon|email|contact)/i', $message)) {
            return 'kontak';
        }

        // Lokasi
        if (preg_match('/(lokasi|alamat|dimana|tempat|klinik|address|maps)/i', $message)) {
            return 'lokasi';
        }

        return 'general';
    }

    /**
     * Respond to greeting
     */
    private function respondGreeting()
    {
        return "السلام عليكم ورحمة الله وبركاته\n\n" .
            "Selamat datang di **Albashiro - Islamic Spiritual Hypnotherapy** 🌙\n\n" .
            "Saya siap membantu Anda dengan:\n" .
            "💰 Informasi harga & paket\n" .
            "🕌 Layanan hipnoterapi Islami\n" .
            "👨‍⚕️ Terapis profesional\n" .
            "📅 Jadwal tersedia\n" .
            "💬 Konsultasi keluhan\n\n" .
            "Silakan ketik pertanyaan Anda atau ceritakan keluhan yang Anda alami. Insya Allah saya akan membantu. 🤲";
    }

    /**
     * Respond to price inquiry
     */
    private function respondHarga($message)
    {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->query("SELECT name, description, price FROM services ORDER BY sort_order");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($services)) {
                return "Maaf, data harga belum tersedia. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
            }

            $response = "**Berikut harga layanan hipnoterapi kami:**\n\n";

            foreach ($services as $service) {
                $price = number_format($service['price'], 0, ',', '.');
                $response .= "**{$service['name']}**\n";
                $response .= "💰 Rp {$price}\n";
                $response .= "📝 {$service['description']}\n\n";
            }

            $response .= "**Semua harga sudah termasuk:**\n";
            $response .= "✅ Konsultasi mendalam\n";
            $response .= "✅ Sesi terapi lengkap\n";
            $response .= "✅ Rekaman audio self-hypnosis\n\n";

            $response .= "Untuk booking atau pertanyaan lebih lanjut:\n";
            $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP . "\n";
            $response .= "📧 Email: " . ADMIN_EMAIL;

            return $response;

        } catch (Exception $e) {
            error_log("LocalAI Error (harga): " . $e->getMessage());
            return "Maaf, terjadi kesalahan saat mengambil data harga. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
        }
    }

    /**
     * Respond to service inquiry
     */
    private function respondLayanan($message)
    {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->query("SELECT name, description, price, target_audience FROM services ORDER BY is_featured DESC, sort_order");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($services)) {
                return "Maaf, data layanan belum tersedia. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
            }

            $response = "**Layanan Hipnoterapi Islami Kami:**\n\n";

            foreach ($services as $service) {
                $price = number_format($service['price'], 0, ',', '.');
                $response .= "🕌 **{$service['name']}**\n";
                $response .= "   {$service['description']}\n";
                $response .= "   💰 Rp {$price} | 👥 {$service['target_audience']}\n\n";
            }

            $response .= "Semua terapi dilakukan sesuai syariat Islam dengan pendekatan yang lembut dan profesional.\n\n";
            $response .= "Ingin tahu lebih detail tentang layanan tertentu? Silakan tanyakan! 😊";

            return $response;

        } catch (Exception $e) {
            error_log("LocalAI Error (layanan): " . $e->getMessage());
            return "Maaf, terjadi kesalahan saat mengambil data layanan. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
        }
    }

    /**
     * Respond to therapist inquiry
     */
    private function respondTerapis($message)
    {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->query("SELECT name, title, specialty, bio, experience_years FROM therapists WHERE is_active = 1 ORDER BY id");
            $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($therapists)) {
                return "Maaf, data terapis belum tersedia. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
            }

            $response = "**Terapis Profesional Kami:**\n\n";

            foreach ($therapists as $therapist) {
                $response .= "👨‍⚕️ **{$therapist['name']}, {$therapist['title']}**\n";
                $response .= "   🎯 Spesialisasi: {$therapist['specialty']}\n";
                $response .= "   📚 Pengalaman: {$therapist['experience_years']} tahun\n";
                $response .= "   📝 {$therapist['bio']}\n\n";
            }

            $response .= "Semua terapis kami bersertifikat dan berpengalaman dalam hipnoterapi Islami.\n\n";
            $response .= "Untuk booking dengan terapis tertentu, silakan hubungi:\n";
            $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP;

            return $response;

        } catch (Exception $e) {
            error_log("LocalAI Error (terapis): " . $e->getMessage());
            return "Maaf, terjadi kesalahan saat mengambil data terapis. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP;
        }
    }

    /**
     * Respond to schedule inquiry
     */
    private function respondJadwal($message)
    {
        $response = "**Jadwal Operasional Albashiro:**\n\n";
        $response .= "📅 **Senin - Jumat**: 09:00 - 17:00 WIB\n";
        $response .= "📅 **Sabtu**: 09:00 - 15:00 WIB\n";
        $response .= "📅 **Minggu**: Tutup\n\n";

        $response .= "Untuk booking jadwal terapi, silakan hubungi Admin kami:\n";
        $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP . "\n\n";

        $response .= "Admin akan membantu Anda:\n";
        $response .= "✅ Cek slot tersedia\n";
        $response .= "✅ Pilih terapis\n";
        $response .= "✅ Konfirmasi booking\n\n";

        $response .= "Kami merekomendasikan booking H-2 untuk memastikan slot tersedia. 😊";

        return $response;
    }

    /**
     * Respond to complaint/problem (with empathy)
     */
    private function respondKeluhan($message)
    {
        // Extract emotion keywords
        $emotions = [];
        if (preg_match('/cemas|khawatir|worry/i', $message))
            $emotions[] = 'cemas';
        if (preg_match('/takut|fobia|phobia/i', $message))
            $emotions[] = 'takut';
        if (preg_match('/sedih|depresi|down/i', $message))
            $emotions[] = 'sedih';
        if (preg_match('/trauma/i', $message))
            $emotions[] = 'trauma';
        if (preg_match('/stress|tertekan/i', $message))
            $emotions[] = 'stress';
        if (preg_match('/susah tidur|insomnia/i', $message))
            $emotions[] = 'susah tidur';

        $emotionText = !empty($emotions) ? implode(', ', $emotions) : 'tidak nyaman';

        $response = "Saya memahami perasaan Anda yang {$emotionText}. 💙\n\n";
        $response .= "Masalah yang Anda alami memang berat, tapi Insya Allah bisa diatasi dengan terapi yang tepat.\n\n";

        // Recommend service based on keywords
        $recommendedService = '';
        if (preg_match('/cemas|panik|khawatir/i', $message)) {
            $recommendedService = "**Manajemen Stres & Kecemasan** (Rp 500.000)\nTerapi khusus untuk mengatasi kecemasan, panic attack, dan gangguan psikosomatis.";
        } elseif (preg_match('/trauma|luka batin/i', $message)) {
            $recommendedService = "**Trauma & Luka Batin** (Rp 600.000)\nMembantu menyembuhkan trauma masa lalu dan PTSD dengan metode yang aman.";
        } elseif (preg_match('/sedih|depresi/i', $message)) {
            $recommendedService = "**Manajemen Stres & Kecemasan** (Rp 500.000)\nTerapi untuk mengatasi depresi dan membangun kembali semangat hidup.";
        } else {
            $recommendedService = "**Konseling Online** (Rp 400.000)\nSesi konsultasi awal untuk memahami masalah Anda lebih dalam.";
        }

        $response .= "**Rekomendasi untuk Anda:**\n{$recommendedService}\n\n";
        $response .= "Apakah Anda ingin:\n";
        $response .= "1️⃣ Tahu lebih detail tentang layanan ini?\n";
        $response .= "2️⃣ Langsung booking sesi terapi?\n";
        $response .= "3️⃣ Konsultasi dengan Admin terlebih dahulu?\n\n";
        $response .= "Silakan ketik pilihan Anda atau hubungi:\n";
        $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP;

        return $response;
    }

    /**
     * Respond to contact inquiry
     */
    private function respondKontak()
    {
        $response = "**Hubungi Kami:**\n\n";
        $response .= "📱 **WhatsApp**: " . ADMIN_WHATSAPP . "\n";
        $response .= "📧 **Email**: " . ADMIN_EMAIL . "\n";
        $response .= "🌐 **Website**: albashiro.vercel.app\n\n";

        $response .= "**Jam Operasional:**\n";
        $response .= "Senin - Jumat: 09:00 - 17:00 WIB\n";
        $response .= "Sabtu: 09:00 - 15:00 WIB\n";
        $response .= "Minggu: Tutup\n\n";

        $response .= "Admin kami siap membantu Anda! 😊";

        return $response;
    }

    /**
     * Respond to location inquiry
     */
    private function respondLokasi()
    {
        $response = "**Lokasi Klinik Albashiro:**\n\n";
        $response .= "📍 Jl. Imam Bonjol No. 123\n";
        $response .= "   Jakarta Pusat, DKI Jakarta 10310\n";
        $response .= "   Indonesia\n\n";

        $response .= "**Cara ke Lokasi:**\n";
        $response .= "🚇 Stasiun Terdekat: Gondangdia (10 menit jalan kaki)\n";
        $response .= "🚌 Bus: Koridor 1 TransJakarta\n";
        $response .= "🚗 Parkir tersedia\n\n";

        $response .= "Untuk petunjuk arah lebih detail, silakan hubungi:\n";
        $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP;

        return $response;
    }

    /**
     * General response (fallback)
     */
    private function respondGeneral($message)
    {
        // Search knowledge base
        try {
            $keywords = $this->extractKeywords($message);
            $knowledge = $this->searchKnowledge($keywords);

            if (!empty($knowledge)) {
                return $knowledge;
            }
        } catch (Exception $e) {
            error_log("LocalAI Error (knowledge): " . $e->getMessage());
        }

        // Default helpful response
        $response = "Terima kasih atas pertanyaan Anda. 😊\n\n";
        $response .= "Saya dapat membantu Anda dengan:\n";
        $response .= "💰 **Harga & Paket** - Ketik: \"berapa harga?\"\n";
        $response .= "🕌 **Layanan** - Ketik: \"apa saja layanannya?\"\n";
        $response .= "👨‍⚕️ **Terapis** - Ketik: \"siapa terapisnya?\"\n";
        $response .= "📅 **Jadwal** - Ketik: \"jadwal buka kapan?\"\n";
        $response .= "📍 **Lokasi** - Ketik: \"dimana lokasinya?\"\n\n";

        $response .= "Atau langsung hubungi Admin kami:\n";
        $response .= "📱 WhatsApp: " . ADMIN_WHATSAPP;

        return $response;
    }

    /**
     * Extract keywords from message
     */
    private function extractKeywords($message)
    {
        // Remove common words
        $stopwords = ['apa', 'yang', 'adalah', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'ini', 'itu', 'saya', 'kamu', 'mereka'];
        $words = explode(' ', strtolower($message));
        $keywords = array_diff($words, $stopwords);

        return array_values($keywords);
    }

    /**
     * Search knowledge base
     */
    private function searchKnowledge($keywords)
    {
        if (empty($keywords))
            return '';

        try {
            $pdo = $this->db->getPdo();
            $searchPattern = '%' . implode('%', $keywords) . '%';

            $stmt = $pdo->prepare("SELECT question, answer FROM faqs WHERE is_active = 1 AND (question LIKE ? OR answer LIKE ?) LIMIT 1");
            $stmt->execute([$searchPattern, $searchPattern]);
            $faq = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($faq) {
                return "**{$faq['question']}**\n\n{$faq['answer']}\n\nAda pertanyaan lain? Silakan tanyakan! 😊";
            }
        } catch (Exception $e) {
            error_log("LocalAI Error (searchKnowledge): " . $e->getMessage());
        }

        return '';
    }
}
