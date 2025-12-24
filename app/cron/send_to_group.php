<?php
/**
 * Send Booking Notification to WhatsApp Group
 * Production function - called after successful booking
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../services/FonnteService.php';

/**
 * Send booking notification to WhatsApp group
 * 
 * @param array $bookingData Booking information
 * @return array Result of send operation
 */
function sendBookingToGroup($bookingData)
{
    try {
        $fonnte = new FonnteService();

        // Get group ID from config
        $groupId = defined('FONNTE_GROUP_ID') ? FONNTE_GROUP_ID : '120363422821859147@g.us';

        // Extract booking data
        $clientName = $bookingData['client_name'] ?? 'N/A';
        $waNumber = $bookingData['wa_number'] ?? 'N/A';
        $therapistName = $bookingData['therapist_name'] ?? 'N/A';
        $serviceName = $bookingData['service_name'] ?? 'Konsultasi Umum';
        $formattedDateTime = $bookingData['formatted_datetime'] ?? 'N/A';
        $bookingCode = $bookingData['booking_code'] ?? 'N/A';
        $problemDescription = $bookingData['problem_description'] ?? '';

        // Format group message
        $groupMessage = "🔔 *RESERVASI BARU*\n\n";
        $groupMessage .= "Assalamu'alaikum,\n\n";
        $groupMessage .= "Ada reservasi baru yang masuk:\n\n";
        $groupMessage .= "👤 Nama: {$clientName}\n";
        $groupMessage .= "📱 WhatsApp: {$waNumber}\n";
        $groupMessage .= "👳 Terapis: {$therapistName}\n";
        $groupMessage .= "✨ Layanan: {$serviceName}\n";
        $groupMessage .= "📅 Tanggal: {$formattedDateTime}\n";
        $groupMessage .= "🔖 Kode Booking: *{$bookingCode}*\n\n";

        if (!empty($problemDescription)) {
            $groupMessage .= "📝 Keluhan:\n" . substr($problemDescription, 0, 200) . "\n\n";
        }

        $groupMessage .= "Status: ⏳ Menunggu Konfirmasi\n\n";
        $groupMessage .= "Mohon segera dikonfirmasi. Jazakallahu khairan.";

        // Send to group
        $result = $fonnte->sendToGroup($groupId, $groupMessage);

        // Log result
        if ($result['success']) {
            error_log("WhatsApp group notification sent for booking: {$bookingCode}");
        } else {
            error_log("Failed to send WhatsApp group notification for booking {$bookingCode}: " . ($result['message'] ?? 'Unknown error'));
        }

        return $result;

    } catch (Exception $e) {
        error_log("Exception in sendBookingToGroup: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

