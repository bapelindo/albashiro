<?php
/**
 * Automatic WhatsApp Reminder System (Direct PDO Version)
 * Sends appointment reminders to clients at appointment time
 * Run this script every hour via cron job
 * 
 * This version uses PDO directly to avoid Database class issues
 */

// Load configuration and services (only if not already loaded)
if (!defined('ALBASHIROH')) {
    define('ALBASHIROH', true);
    require_once __DIR__ . '/../../config/config.php';
}
require_once __DIR__ . '/../services/FonnteService.php';

// Setup logging
$logFile = __DIR__ . '/../../logs/reminders.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    echo $logEntry;
}

logMessage("=== Starting Reminder Cron Job ===");

try {
    // Direct PDO connection with SSL for TiDB Cloud
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);

    // Initialize Fonnte service
    $fonnteService = new FonnteService();

    // Get current time
    $currentDateTime = new DateTime();
    $currentDateTimeStr = $currentDateTime->format('Y-m-d H:i:s');

    logMessage("Current time: {$currentDateTimeStr}");

    // --- PART 1: H-1 (24-Hour) REMINDERS ---
    logMessage("Checking H-1 (24-hour) reminders...");
    $h1Query = "
        SELECT 
            b.id,
            b.booking_code,
            b.client_name,
            b.wa_number,
            b.appointment_date,
            b.appointment_time,
            b.problem_description,
            b.therapist_id,
            t.name as therapist_name,
            s.name as service_name,
            TIMESTAMPDIFF(HOUR, NOW(), CONVERT_TZ(CONCAT(b.appointment_date, ' ', b.appointment_time), '+07:00', '+00:00')) as hours_until
        FROM bookings b
        LEFT JOIN therapists t ON b.therapist_id = t.id
        LEFT JOIN services s ON b.service_id = s.id
        WHERE b.status IN ('confirmed', 'pending')
          AND TIMESTAMPDIFF(HOUR, NOW(), CONVERT_TZ(CONCAT(b.appointment_date, ' ', b.appointment_time), '+07:00', '+00:00')) BETWEEN 20 AND 26
          AND NOT EXISTS (
              SELECT 1 FROM reminder_logs rl 
              WHERE rl.booking_id = b.id 
                AND rl.message LIKE '%PENGINGAT H-1%' 
                AND rl.delivery_status = 'sent'
          )
        ORDER BY b.appointment_time ASC
    ";

    $stmt = $pdo->prepare($h1Query);
    $stmt->execute();
    $h1Bookings = $stmt->fetchAll();
    logMessage("Found " . count($h1Bookings) . " booking(s) for H-1 reminders");

    $sentH1 = 0;
    foreach ($h1Bookings as $booking) {
        $hoursUntil = isset($booking->hours_until) ? $booking->hours_until : 'unknown';
        logMessage("Processing H-1 reminder for {$booking->client_name} (Booking Code: {$booking->booking_code}, hours until: {$hoursUntil})");
        try {
            $formattedDate = format_date_id($booking->appointment_date);
            $formattedTime = substr($booking->appointment_time, 0, 5) . ' WIB';

            $message = "Assalamu'alaikum {$booking->client_name},\n\n";
            $message .= "🔔 *PENGINGAT H-1 JANJI TEMU*\n\n";
            $message .= "Kami ingin mengingatkan bahwa Anda memiliki jadwal hipnoterapi *BESOK* di Klinik Kamu:\n\n";
            $message .= "👳 Terapis: {$booking->therapist_name}\n";
            $message .= "✨ Layanan: " . ($booking->service_name ?: 'Konsultasi Umum') . "\n";
            $message .= "📅 Tanggal: {$formattedDate}\n";
            $message .= "⏰ Waktu: {$formattedTime}\n";
            $message .= "📍 Lokasi: Klinik Kamu Hypnotherapy Center\n";
            $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
            $message .= "⚠️ Mohon bersiap dan datang 10 menit lebih awal.\n\n";
            $message .= "Jika Anda berhalangan hadir atau ingin melakukan reschedule, silakan hubungi kami segera.\n\n";
            $message .= "Jazakallahu khairan. 🤲";

            $result = $fonnteService->sendMessage($booking->wa_number, $message);

            if ($result['success']) {
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'sent')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);
                $sentH1++;
                logMessage("✓ H-1 reminder sent successfully to {$booking->wa_number}");
            } else {
                $errorMsg = $result['message'] ?? 'Unknown error';
                logMessage("✗ Failed to send H-1 to {$booking->wa_number}: {$errorMsg}");
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'failed')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);
            }
        } catch (Exception $ex) {
            logMessage("✗ Exception for booking {$booking->booking_code}: " . $ex->getMessage());
        }
        sleep(1);
    }

    // --- PART 2: H-1 HOUR (60-Minute) REMINDERS ---
    logMessage("Checking H-1 hour (final) reminders...");
    $h1hourQuery = "
        SELECT 
            b.id,
            b.booking_code,
            b.client_name,
            b.wa_number,
            b.appointment_date,
            b.appointment_time,
            b.problem_description,
            b.therapist_id,
            t.name as therapist_name,
            s.name as service_name,
            TIMESTAMPDIFF(MINUTE, NOW(), CONVERT_TZ(CONCAT(b.appointment_date, ' ', b.appointment_time), '+07:00', '+00:00')) as minutes_until
        FROM bookings b
        LEFT JOIN therapists t ON b.therapist_id = t.id
        LEFT JOIN services s ON b.service_id = s.id
        WHERE b.status IN ('confirmed', 'pending')
          AND TIMESTAMPDIFF(MINUTE, NOW(), CONVERT_TZ(CONCAT(b.appointment_date, ' ', b.appointment_time), '+07:00', '+00:00')) BETWEEN 0 AND 75
          AND NOT EXISTS (
              SELECT 1 FROM reminder_logs rl 
              WHERE rl.booking_id = b.id 
                AND rl.message LIKE '%PENGINGAT TERAKHIR%' 
                AND rl.delivery_status = 'sent'
          )
        ORDER BY b.appointment_time ASC
    ";

    $stmt = $pdo->prepare($h1hourQuery);
    $stmt->execute();
    $h1hourBookings = $stmt->fetchAll();
    logMessage("Found " . count($h1hourBookings) . " booking(s) for H-1 hour reminders");

    $sentH1Hour = 0;
    foreach ($h1hourBookings as $booking) {
        $minutesUntil = isset($booking->minutes_until) ? $booking->minutes_until : 'unknown';
        logMessage("Processing H-1 hour reminder for {$booking->client_name} (Booking Code: {$booking->booking_code}, minutes until: {$minutesUntil})");
        try {
            $formattedDate = format_date_id($booking->appointment_date);
            $formattedTime = substr($booking->appointment_time, 0, 5) . ' WIB';
            $minutesStr = $minutesUntil > 0 ? "dalam *{$minutesUntil} MENIT LAGI*" : "*SEKARANG*";

            $message = "Assalamu'alaikum {$booking->client_name},\n\n";
            $message .= "🔔 *PENGINGAT TERAKHIR JANJI TEMU*\n\n";
            $message .= "Jadwal hipnoterapi Anda akan dimulai {$minutesStr}:\n\n";
            $message .= "👳 Terapis: {$booking->therapist_name}\n";
            $message .= "✨ Layanan: " . ($booking->service_name ?: 'Konsultasi Umum') . "\n";
            $message .= "📅 Tanggal: {$formattedDate}\n";
            $message .= "⏰ Waktu: {$formattedTime}\n";
            $message .= "📍 Lokasi: Klinik Kamu Hypnotherapy Center\n";
            $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
            $message .= "⚠️ Mohon bersiap dan datang tepat waktu. Sampai jumpa di lokasi!\n\n";
            $message .= "Jazakallahu khairan. 🤲";

            $result = $fonnteService->sendMessage($booking->wa_number, $message);

            if ($result['success']) {
                // Update booking - mark reminder as sent (keeps DB backward compatibility)
                $updateStmt = $pdo->prepare("UPDATE bookings SET reminder_sent = 1, reminder_sent_at = NOW() WHERE id = ?");
                $updateStmt->execute([$booking->id]);

                // Log to reminder_logs table
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'sent')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);

                $sentH1Hour++;
                logMessage("✓ H-1 hour reminder sent successfully to client: {$booking->wa_number}");

                // Also send reminder to therapist
                require_once __DIR__ . '/send_to_therapist.php';
                $therapistResult = sendReminderToTherapist([
                    'therapist_id' => $booking->therapist_id,
                    'client_name' => $booking->client_name,
                    'wa_number' => $booking->wa_number,
                    'therapist_name' => $booking->therapist_name,
                    'service_name' => $booking->service_name ?: 'Konsultasi Umum',
                    'formatted_datetime' => $formattedDate . ' pukul ' . $formattedTime,
                    'booking_code' => $booking->booking_code,
                    'problem_description' => $booking->problem_description
                ]);

                if ($therapistResult['success']) {
                    logMessage("✓ Therapist reminder sent successfully");
                } else {
                    logMessage("⚠ Failed to send therapist reminder (client reminder still sent)");
                }
            } else {
                $errorMsg = $result['message'] ?? 'Unknown error';
                logMessage("✗ Failed to send H-1 hour to {$booking->wa_number}: {$errorMsg}");
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'failed')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);
            }
        } catch (Exception $ex) {
            logMessage("✗ Exception for booking {$booking->booking_code}: " . $ex->getMessage());
        }
        sleep(1);
    }

    logMessage("Summary: H-1 Reminders: {$sentH1} sent; H-1 Hour Reminders: {$sentH1Hour} sent");
    logMessage("=== Cron Job Completed ===");

} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}
