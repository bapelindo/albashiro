<?php
/**
 * Auto-Learning Service
 * Automatically detects knowledge gaps and suggests improvements to RAG database
 */

class AutoLearningService
{
    private $db;
    private $minMessageLength = 20;
    private $knowledgeMatchThreshold = 2;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log conversation for analytics and learning
     */
    public function logConversation($sessionId, $userMessage, $aiResponse, $metadata = [])
    {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                INSERT INTO chat_conversations 
                (session_id, user_message, ai_response, knowledge_matched, keywords_searched, response_time_ms)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $sessionId,
                substr($userMessage, 0, 1000), // Limit to prevent bloat
                substr($aiResponse, 0, 2000),
                $metadata['knowledge_matched'] ?? 0,
                $metadata['keywords_searched'] ?? '',
                $metadata['response_time_ms'] ?? 0
            ]);

            // Update daily analytics
            $this->updateDailyAnalytics($metadata['knowledge_matched'] ?? 0, $sessionId);
        } catch (Exception $e) {
            // Error logic removed for production
        }
    }

    /**
     * Detect knowledge gap and create suggestion
     */
    public function detectKnowledgeGap($userMessage, $knowledgeMatchCount, $keywords)
    {
        // Skip if message too short or already has good knowledge
        if (strlen($userMessage) < $this->minMessageLength) {
            return false;
        }

        if ($knowledgeMatchCount >= $this->knowledgeMatchThreshold) {
            return false;
        }

        // Skip common greetings and small talk
        $skipPatterns = [
            '/^(hai|halo|hi|hello|assalamualaikum|selamat)/i',
            '/^(terima kasih|thanks|makasih)/i',
            '/^(oke|ok|baik|siap)/i'
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, trim($userMessage))) {
                return false;
            }
        }

        try {
            $pdo = $this->db->getPdo();

            // Create hash for deduplication
            $questionHash = md5(strtolower(trim($userMessage)));

            // Check if similar question already exists
            $stmt = $pdo->prepare("
                SELECT id, frequency FROM knowledge_suggestions 
                WHERE question_hash = ? AND status = 'pending'
            ");
            $stmt->execute([$questionHash]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Increment frequency
                $stmt = $pdo->prepare("
                    UPDATE knowledge_suggestions 
                    SET frequency = frequency + 1, last_asked = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$existing['id']]);

                return $existing['id'];
            } else {
                // Create new suggestion
                $category = $this->detectCategory($userMessage);
                $priority = $this->calculatePriority($userMessage, $keywords);

                $stmt = $pdo->prepare("
                    INSERT INTO knowledge_suggestions 
                    (question, question_hash, keywords, category, priority, frequency)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");

                $stmt->execute([
                    substr($userMessage, 0, 500),
                    $questionHash,
                    is_array($keywords) ? implode(', ', $keywords) : $keywords,
                    $category,
                    $priority
                ]);

                // Update analytics
                $this->incrementNewSuggestions();

                return $pdo->lastInsertId();
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get top knowledge suggestions for admin review
     */
    public function getTopSuggestions($limit = 20, $status = 'pending')
    {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    question,
                    keywords,
                    frequency,
                    category,
                    priority,
                    first_asked,
                    last_asked,
                    DATEDIFF(NOW(), first_asked) as days_pending
                FROM knowledge_suggestions
                WHERE status = :status
                ORDER BY 
                    priority ASC,
                    frequency DESC,
                    first_asked ASC
                LIMIT :limit
            ");

            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Approve suggestion and optionally add to RAG
     */
    public function approveSuggestion($id, $answer = null, $category = null)
    {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                UPDATE knowledge_suggestions 
                SET 
                    status = 'approved',
                    suggested_answer = ?,
                    category = COALESCE(?, category),
                    approved_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$answer, $category, $id]);

            // TODO: Optionally add to RAG database if answer provided
            // This would require embedding generation and vector insertion

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reject suggestion
     */
    public function rejectSuggestion($id)
    {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                UPDATE knowledge_suggestions 
                SET status = 'rejected'
                WHERE id = ?
            ");

            return $stmt->execute([$id]);

        } catch (Exception $e) {
            // No-Op: Diagnostic logging removed for production
            return false;
        }
    }

    /**
     * Generate AI-assisted answer for a knowledge suggestion
     * Uses Ollama to create a draft answer based on existing knowledge
     */
    public function generateSuggestedAnswer($questionId)
    {
        try {
            $pdo = $this->db->getPdo();

            // Get the question
            $stmt = $pdo->prepare("SELECT question, category, keywords FROM knowledge_suggestions WHERE id = ?");
            $stmt->execute([$questionId]);
            $suggestion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$suggestion) {
                return ['success' => false, 'message' => 'Suggestion not found'];
            }

            // Load Ollama Service with shorter timeout
            require_once __DIR__ . '/OllamaService.php';
            $ollama = new OllamaService(null, null, 15); // 15 second timeout

            // Create concise prompt for faster generation
            $prompt = "Jawab singkat dalam 2 paragraf (max 150 kata):\n\n";
            $prompt .= "Q: {$suggestion['question']}\n\n";
            $prompt .= "Jawab sebagai ahli hipnoterapi Islam. Gunakan bahasa Indonesia profesional. Akhiri dengan ajakan konsultasi.";

            // Generate answer using Ollama with token limit (skip auto-learning)
            $fullResponse = '';

            try {
                $result = $ollama->chatStream($prompt, [], function ($token, $done) use (&$fullResponse) {
                    $fullResponse .= $token;
                }, null, true); // Skip auto-learning for internal AI call
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Timeout: AI generation took too long. Please try again.'
                ];
            }

            if (!empty($fullResponse)) {
                $generatedAnswer = trim($fullResponse);

                // Save suggested answer to database
                $stmt = $pdo->prepare("
                    UPDATE knowledge_suggestions 
                    SET suggested_answer = ?
                    WHERE id = ?
                ");
                $stmt->execute([$generatedAnswer, $questionId]);

                return [
                    'success' => true,
                    'answer' => $generatedAnswer,
                    'message' => 'AI answer generated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to generate answer'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get analytics data for dashboard
     */
    public function getAnalytics($days = 7)
    {
        try {
            $pdo = $this->db->getPdo();

            // Get daily stats
            $stmt = $pdo->prepare("
                SELECT 
                    date,
                    total_conversations,
                    knowledge_matched,
                    knowledge_not_matched,
                    avg_response_time_ms,
                    unique_sessions,
                    new_suggestions,
                    ROUND((knowledge_matched / NULLIF(total_conversations, 0)) * 100, 1) as match_rate
                FROM chat_analytics
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY date DESC
            ");

            $stmt->execute([$days]);
            $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get summary stats
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_suggestions,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(frequency) as total_frequency
                FROM knowledge_suggestions
            ");

            $stmt->execute();
            $suggestionStats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'daily_stats' => $dailyStats,
                'suggestion_stats' => $suggestionStats
            ];
        } catch (Exception $e) {
            return ['daily_stats' => [], 'suggestion_stats' => []];
        }
    }

    /**
     * Detect category from question content
     */
    private function detectCategory($message)
    {
        $message = strtolower($message);

        $categories = [
            'Pricing' => ['harga', 'biaya', 'tarif', 'bayar', 'mahal', 'murah'],
            'Booking' => ['jadwal', 'booking', 'reservasi', 'janji', 'daftar', 'kapan'],
            'Services' => ['layanan', 'terapi', 'treatment', 'sesi', 'program'],
            'Medical' => ['gejala', 'sakit', 'penyakit', 'obat', 'diagnosis'],
            'Islamic' => ['hukum', 'halal', 'haram', 'doa', 'islam', 'syariat'],
            'General' => []
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'General';
    }

    /**
     * Calculate priority score (1 = highest, 10 = lowest)
     */
    private function calculatePriority($message, $keywords)
    {
        $priority = 5; // Default medium priority

        // High priority: Pricing, booking, urgent
        if (preg_match('/(harga|biaya|booking|jadwal|darurat|urgent)/i', $message)) {
            $priority = 2;
        }

        // Medium-high: Medical, services
        if (preg_match('/(terapi|treatment|gejala|sakit)/i', $message)) {
            // No-Op: Diagnostic logging removed for production
        }

        // Low priority: Very short or vague
        if (strlen($message) < 30) {
            $priority = 7;
        }

        return $priority;
    }

    /**
     * Update daily analytics
     */
    private function updateDailyAnalytics($knowledgeMatched, $sessionId)
    {
        try {
            $pdo = $this->db->getPdo();

            $hasMatch = $knowledgeMatched >= $this->knowledgeMatchThreshold ? 1 : 0;
            $noMatch = $hasMatch ? 0 : 1;

            $stmt = $pdo->prepare("
                INSERT INTO chat_analytics 
                (date, total_conversations, knowledge_matched, knowledge_not_matched, unique_sessions)
                VALUES (CURDATE(), 1, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                    total_conversations = total_conversations + 1,
                    knowledge_matched = knowledge_matched + ?,
                    knowledge_not_matched = knowledge_not_matched + ?
            ");

            $stmt->execute([$hasMatch, $noMatch, $hasMatch, $noMatch]);
        } catch (Exception $e) {
            // Error logic removed for production
        }
    }

    /**
     * Increment new suggestions counter
     */
    private function incrementNewSuggestions()
    {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                UPDATE chat_analytics 
                SET new_suggestions = new_suggestions + 1
                WHERE date = CURDATE()
            ");

            $stmt->execute();
        } catch (Exception $e) {
            // Error logic removed for production
        }
    }
}
