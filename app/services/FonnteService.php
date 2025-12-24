<?php
/**
 * Fonnte WhatsApp Service
 * 
 * Integration with Fonnte.com for sending WhatsApp messages
 * Documentation: https://docs.fonnte.com/
 */

class FonnteService
{
    private $apiUrl = 'https://api.fonnte.com/send';
    private $apiToken;
    private $logFile;

    public function __construct()
    {
        $this->apiToken = FONNTE_API_TOKEN;
        $this->logFile = __DIR__ . '/../../logs/fonnte.log';

        // Ensure logs directory exists
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage($phoneNumber, $message)
    {
        try {
            $this->log("Sending message to {$phoneNumber}");

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $phoneNumber,
                    'message' => $message,
                    'countryCode' => '62' // Indonesia
                ],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiToken
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            if ($error) {
                $this->log("CURL Error: {$error}", 'ERROR');
                return [
                    'success' => false,
                    'message' => $error
                ];
            }

            $result = json_decode($response, true);

            if ($httpCode == 200 && isset($result['status']) && $result['status']) {
                $this->log("Message sent successfully to {$phoneNumber}");
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'response' => $result
                ];
            } else {
                $errorMsg = $result['reason'] ?? $result['message'] ?? 'Unknown error';
                $this->log("Failed to send to {$phoneNumber}: {$errorMsg}", 'ERROR');
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'response' => $result
                ];
            }

        } catch (Exception $e) {
            $this->log("Exception: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message to group
     * 
     * @param string $groupId Group ID (format: 120363XXXXXXXXXX@g.us)
     * @param string $message Message to send
     * @return array Response
     */
    public function sendToGroup($groupId, $message)
    {
        try {
            $this->log("Sending message to group {$groupId}");

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $groupId,
                    'message' => $message
                ],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiToken
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            if ($error) {
                $this->log("CURL Error: {$error}", 'ERROR');
                return [
                    'success' => false,
                    'message' => $error
                ];
            }

            $result = json_decode($response, true);

            if ($httpCode == 200 && isset($result['status']) && $result['status']) {
                $this->log("Message sent successfully to group {$groupId}");
                return [
                    'success' => true,
                    'message' => 'Message sent to group successfully',
                    'response' => $result
                ];
            } else {
                $errorMsg = $result['reason'] ?? $result['message'] ?? 'Unknown error';
                $this->log("Failed to send to group {$groupId}: {$errorMsg}", 'ERROR');
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'response' => $result
                ];
            }

        } catch (Exception $e) {
            $this->log("Exception: " . $e->getMessage(), 'ERROR');
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
        $message = "Assalamu'alaikum {$booking->client_name},\n\n";
        $message .= "✅ *KONFIRMASI RESERVASI*\n\n";
        $message .= "Terima kasih telah melakukan reservasi hipnoterapi di Al-Bashiro.\n\n";
        $message .= "📋 *Detail Reservasi:*\n";
        $message .= "👤 Nama: {$booking->client_name}\n";
        $message .= "👳 Terapis: {$booking->therapist_name}\n";
        $message .= "✨ Layanan: " . ($booking->service_name ?? 'Konsultasi Umum') . "\n";
        $message .= "📅 Tanggal: " . format_date_id($booking->appointment_date) . "\n";
        $message .= "⏰ Waktu: " . substr($booking->appointment_time, 0, 5) . " WIB\n";
        $message .= "📍 Lokasi: Al-Bashiro Hypnotherapy Center\n";
        $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
        $message .= "Kami akan mengirimkan pengingat pada hari dan jam yang sama dengan jadwal Anda.\n\n";
        $message .= "Jika ada perubahan, silakan hubungi kami segera.\n\n";
        $message .= "Jazakallahu khairan. 🤲";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send appointment reminder (same day, same hour)
     */
    public function sendAppointmentReminder($booking)
    {
        $message = "Assalamu'alaikum {$booking->client_name},\n\n";
        $message .= "🔔 *PENGINGAT JANJI TEMU*\n\n";
        $message .= "Anda memiliki jadwal hipnoterapi *HARI INI*:\n\n";
        $message .= "👳 Terapis: {$booking->therapist_name}\n";
        $message .= "✨ Layanan: " . ($booking->service_name ?? 'Konsultasi Umum') . "\n";
        $message .= "📅 Tanggal: " . format_date_id($booking->appointment_date) . "\n";
        $message .= "⏰ Waktu: " . substr($booking->appointment_time, 0, 5) . " WIB\n";
        $message .= "📍 Lokasi: Al-Bashiro Hypnotherapy Center\n";
        $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
        $message .= "⚠️ Mohon datang 10 menit lebih awal.\n\n";
        $message .= "Jika berhalangan hadir, silakan hubungi kami segera.\n\n";
        $message .= "Jazakallahu khairan. 🤲";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send reschedule notification
     */
    public function sendRescheduleNotification($booking, $oldDate, $oldTime, $reason)
    {
        $message = "Assalamu'alaikum {$booking->client_name},\n\n";
        $message .= "🔄 *PEMBERITAHUAN RESCHEDULE*\n\n";
        $message .= "Jadwal Anda telah diubah:\n\n";
        $message .= "❌ *Jadwal Lama:*\n";
        $message .= "📅 " . format_date_id($oldDate) . "\n";
        $message .= "⏰ " . substr($oldTime, 0, 5) . " WIB\n\n";
        $message .= "✅ *Jadwal Baru:*\n";
        $message .= "📅 " . format_date_id($booking->appointment_date) . "\n";
        $message .= "⏰ " . substr($booking->appointment_time, 0, 5) . " WIB\n\n";
        if ($reason) {
            $message .= "📝 Alasan: {$reason}\n\n";
        }
        $message .= "👳 Terapis: {$booking->therapist_name}\n";
        $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
        $message .= "Mohon konfirmasi jika Anda setuju dengan jadwal baru.\n\n";
        $message .= "Jazakallahu khairan. 🤲";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Send cancellation notification
     */
    public function sendCancellationNotification($booking, $reason = null)
    {
        $message = "Assalamu'alaikum {$booking->client_name},\n\n";
        $message .= "❌ *PEMBATALAN RESERVASI*\n\n";
        $message .= "Reservasi Anda telah dibatalkan:\n\n";
        $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n";
        $message .= "📅 Tanggal: " . format_date_id($booking->appointment_date) . "\n";
        $message .= "⏰ Waktu: " . substr($booking->appointment_time, 0, 5) . " WIB\n";
        $message .= "👳 Terapis: {$booking->therapist_name}\n\n";
        if ($reason) {
            $message .= "📝 Alasan: {$reason}\n\n";
        }
        $message .= "Jika Anda ingin membuat reservasi baru, silakan hubungi kami.\n\n";
        $message .= "Jazakallahu khairan. 🤲";

        return $this->sendMessage($booking->wa_number, $message);
    }

    /**
     * Check account balance
     */
    public function checkBalance()
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/get-devices',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiToken
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);

            return json_decode($response, true);

        } catch (Exception $e) {
            $this->log("Error checking balance: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }

    /**
     * Get WhatsApp groups
     * Returns list of all groups with their IDs
     */
    public function getGroups()
    {
        try {
            $this->log("Fetching WhatsApp groups");

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/get-whatsapp-group',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiToken
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $result = json_decode($response, true);

            if (isset($result['data']) && is_array($result['data'])) {
                $this->log("Found " . count($result['data']) . " groups");
                return [
                    'success' => true,
                    'groups' => $result['data']
                ];
            } else {
                $this->log("No groups found or error", 'ERROR');
                return [
                    'success' => false,
                    'message' => $result['reason'] ?? 'No groups found',
                    'groups' => []
                ];
            }

        } catch (Exception $e) {
            $this->log("Error fetching groups: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'groups' => []
            ];
        }
    }

    /**
     * Log messages
     */
    private function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);

        // Also echo for cron output
        echo $logMessage;
    }
}
