<?php
/**
 * Albashiro - WhatsApp Service
 * Handles WhatsApp message sending (unofficial API)
 */

class WhatsAppService
{
    private $apiUrl;
    private $apiKey;
    private $enabled;

    public function __construct()
    {
        // Configuration - set these in config.php or environment
        $this->apiUrl = defined('WHATSAPP_API_URL') ? WHATSAPP_API_URL : 'http://localhost:3000'; // Local wwebjs server
        $this->apiKey = defined('WHATSAPP_API_KEY') ? WHATSAPP_API_KEY : '';
        $this->enabled = defined('WHATSAPP_ENABLED') ? WHATSAPP_ENABLED : false;
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage($phoneNumber, $message)
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'WhatsApp service is disabled'];
        }

        // Format phone number (remove +, spaces, dashes)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if not present (Indonesia: 62)
        if (!str_starts_with($phoneNumber, '62')) {
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = '62' . substr($phoneNumber, 1);
            } else {
                $phoneNumber = '62' . $phoneNumber;
            }
        }

        $phoneNumber .= '@c.us'; // WhatsApp format

        try {
            $data = [
                'chatId' => $phoneNumber,
                'message' => $message,
                'apiKey' => $this->apiKey
            ];

            $ch = curl_init($this->apiUrl . '/send-message');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // curl_close is No-Op in PHP 8.0+

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return [
                    'success' => true,
                    'message_id' => $result['messageId'] ?? null,
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "API returned HTTP $httpCode",
                    'response' => $response
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
     * Send booking confirmation
     */
    public function sendBookingConfirmation($booking)
    {
        $message = "🌙 Konfirmasi Booking Albashiro\n\n";
        $message .= "Halo {$booking->client_name},\n";
        $message .= "Booking Anda telah dikonfirmasi!\n\n";
        $message .= "📅 Tanggal: " . date('d M Y', strtotime($booking->appointment_date)) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($booking->appointment_time)) . " WIB\n";
        $message .= "👤 Terapis: {$booking->therapist_name}\n";
        $message .= "🔖 Kode Booking: {$booking->booking_code}\n\n";
        $message .= "Jika ada perubahan, hubungi kami.\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send 24-hour reminder
     */
    public function send24HourReminder($booking)
    {
        $message = "⏰ Pengingat Booking Albashiro\n\n";
        $message .= "Halo {$booking->client_name},\n";
        $message .= "Pengingat: Anda memiliki sesi hipnoterapi besok!\n\n";
        $message .= "📅 Tanggal: " . date('d M Y', strtotime($booking->appointment_date)) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($booking->appointment_time)) . " WIB\n";
        $message .= "👤 Terapis: {$booking->therapist_name}\n\n";
        $message .= "Sampai jumpa! 🌙";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send 1-hour reminder
     */
    public function send1HourReminder($booking)
    {
        $message = "🔔 Pengingat Terakhir - Albashiro\n\n";
        $message .= "Halo {$booking->client_name},\n";
        $message .= "Sesi Anda akan dimulai dalam 1 jam!\n\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($booking->appointment_time)) . " WIB\n";
        $message .= "👤 Terapis: {$booking->therapist_name}\n\n";
        $message .= "Kami tunggu kedatangan Anda! 🙏";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send reschedule notification
     */
    public function sendRescheduleNotification($booking, $oldDate, $oldTime)
    {
        $message = "📅 Perubahan Jadwal - Albashiro\n\n";
        $message .= "Halo {$booking->client_name},\n";
        $message .= "Jadwal booking Anda telah diubah:\n\n";
        $message .= "❌ Jadwal Lama:\n";
        $message .= "   " . date('d M Y', strtotime($oldDate)) . " - " . date('H:i', strtotime($oldTime)) . " WIB\n\n";
        $message .= "✅ Jadwal Baru:\n";
        $message .= "   " . date('d M Y', strtotime($booking->appointment_date)) . " - " . date('H:i', strtotime($booking->appointment_time)) . " WIB\n\n";
        $message .= "👤 Terapis: {$booking->therapist_name}\n";
        $message .= "🔖 Kode Booking: {$booking->booking_code}\n\n";
        $message .= "Terima kasih atas pengertiannya! 🙏";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Check if service is enabled
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
