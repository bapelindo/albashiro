<?php
/**
 * Send Reminder to Therapist's WhatsApp
 * Notifies therapist about upcoming appointment
 */

// Load configuration (only if not already loaded)
if (!defined('ALBASHIROH')) {
    define('ALBASHIROH', true);
    require_once __DIR__ . '/../../config/config.php';
}
require_once __DIR__ . '/../services/FonnteService.php';

/**
 * Send appointment reminder to therapist
 * 
 * @param array $bookingData Booking information
 * @return array Result of send operation
 */
function sendReminderToTherapist($bookingData)
{
    try {
        $fonnte = new FonnteService();

        // Get therapist WhatsApp number from config
        $therapistId = $bookingData['therapist_id'] ?? 0;
        $therapistWhatsApp = THERAPIST_WHATSAPP[$therapistId] ?? ADMIN_WHATSAPP;

        // Extract booking data
        $clientName = $bookingData['client_name'] ?? 'N/A';
        $clientWA = $bookingData['wa_number'] ?? 'N/A';
        $therapistName = $bookingData['therapist_name'] ?? 'N/A';
        $serviceName = $bookingData['service_name'] ?? 'Konsultasi Umum';
        $formattedDateTime = $bookingData['formatted_datetime'] ?? 'N/A';
        $bookingCode = $bookingData['booking_code'] ?? 'N/A';
        $problemDescription = $bookingData['problem_description'] ?? '';

        // Format message for therapist
        $message = "Assalamu'alaikum {$therapistName},\n\n";
        $message .= "🔔 *PENGINGAT JADWAL TERAPI*\n\n";
        $message .= "Anda memiliki jadwal terapi *HARI INI*:\n\n";
        $message .= "👤 Klien: {$clientName}\n";
        $message .= "📱 WhatsApp: {$clientWA}\n";
        $message .= "✨ Layanan: {$serviceName}\n";
        $message .= "📅 Waktu: {$formattedDateTime}\n";
        $message .= "🔖 Kode Booking: *{$bookingCode}*\n\n";

        if (!empty($problemDescription)) {
            $message .= "📝 Keluhan Klien:\n" . substr($problemDescription, 0, 200) . "\n\n";
        }

        $message .= "Mohon persiapkan sesi terapi dengan baik.\n\n";
        $message .= "Jazakallahu khairan. 🤲";

        // Send to therapist
        $result = $fonnte->sendMessage($therapistWhatsApp, $message);


        return $result;

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

