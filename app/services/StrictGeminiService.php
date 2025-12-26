<?php
/**
 * Strict Data Validator Wrapper for GeminiService
 * Forces AI to use ONLY real database data
 */

class StrictGeminiService extends GeminiService
{
    /**
     * Override chat method with strict validation
     */
    public function chat($userMessage, $conversationHistory = [])
    {
        // STEP 1: PRE-VALIDATION - Force check database
        $servicesData = $this->getServicesInfo();

        if (strpos($servicesData, 'ERROR') !== false || strpos($servicesData, 'KOSONG') !== false) {
            // Database unavailable - don't call AI
            error_log("CRITICAL: Database unavailable, rejecting AI call");
            return [
                'response' => "Maaf, sistem database sedang tidak tersedia. Silakan hubungi Admin WA: " . ADMIN_WHATSAPP,
                'metadata' => ['error' => true, 'reason' => 'database_unavailable']
            ];
        }

        // STEP 2: Call parent (original AI logic)
        $result = parent::chat($userMessage, $conversationHistory);

        // STEP 3: POST-VALIDATION - Check for hallucination
        if (isset($result['response'])) {
            $hallucinated = $this->detectHallucination($result['response'], $servicesData);

            if ($hallucinated) {
                error_log("HALLUCINATION DETECTED! Forcing retry...");

                // Force retry with ULTRA strict prompt
                $strictMessage = $userMessage . "\n\n[SYSTEM OVERRIDE: Previous response contained FAKE data. Use ONLY exact prices from database!]";
                $result = parent::chat($strictMessage, $conversationHistory);

                // If still hallucinating, return error
                if ($this->detectHallucination($result['response'], $servicesData)) {
                    error_log("CRITICAL: AI still hallucinating after retry!");
                    return [
                        'response' => "Maaf, sistem AI mengalami gangguan. Untuk informasi harga yang akurat, silakan hubungi Admin WA: " . ADMIN_WHATSAPP,
                        'metadata' => ['error' => true, 'reason' => 'ai_hallucination']
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Detect hallucinated prices or packages
     */
    private function detectHallucination($response, $realData)
    {
        // Extract all prices from response
        preg_match_all('/Rp\s*[\d.,]+/', $response, $responseMatches);
        $responsePrices = array_map(function ($p) {
            return preg_replace('/[^\d]/', '', $p); // Remove formatting
        }, $responseMatches[0]);

        // Extract all real prices from database
        preg_match_all('/Rp\s*[\d.,]+/', $realData, $realMatches);
        $realPrices = array_map(function ($p) {
            return preg_replace('/[^\d]/', '', $p);
        }, $realMatches[0]);

        // Check if any response price is NOT in database
        foreach ($responsePrices as $price) {
            if (!in_array($price, $realPrices)) {
                error_log("FAKE PRICE DETECTED: Rp " . number_format($price, 0, ',', '.'));
                return true;
            }
        }

        // Check for forbidden package names
        $forbiddenPackages = [
            'Paket Premium',
            'Paket Keluarga',
            'Paket Corporate',
            'Paket Paksaan',
            'Paket Intensif',
            'Paket Standar',
            'Paket Individual' // Only if followed by price not in DB
        ];

        foreach ($forbiddenPackages as $forbidden) {
            if (stripos($response, $forbidden) !== false) {
                error_log("FAKE PACKAGE DETECTED: $forbidden");
                return true;
            }
        }

        return false;
    }
}
