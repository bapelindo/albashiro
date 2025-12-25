<?php
/**
 * Automatic WhatsApp Reminder System (Direct PDO Version)
 * Sends appointment reminders to clients at appointment time
 * Run this script every hour via cron job
 * 
 * This version uses PDO directly to avoid Database class issues
 */

// Load configuration and services (only if not already loaded)
if (!defined('ALBASHIRO')) {
    define('ALBASHIRO', true);
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
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
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

    // Get current date and hour
    $currentDate = date('Y-m-d');
    $currentHour = date('H:00:00');

    logMessage("Checking appointments for {$currentDate} at {$currentHour}");

    // Query bookings that need reminders
    $query = "
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
            s.name as service_name
        FROM bookings b
        LEFT JOIN therapists t ON b.therapist_id = t.id
        LEFT JOIN services s ON b.service_id = s.id
        WHERE b.appointment_date = :date
        AND TIME_FORMAT(b.appointment_time, '%H:00:00') = :hour
        AND b.status IN ('confirmed', 'pending')
        AND (b.reminder_sent = 0 OR b.reminder_sent IS NULL)
        ORDER BY b.appointment_time ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':date' => $currentDate,
        ':hour' => $currentHour
    ]);

    $bookings = $stmt->fetchAll();
    $totalBookings = count($bookings);

    logMessage("Found {$totalBookings} booking(s) that need reminders");

    if ($totalBookings === 0) {
        logMessage("No reminders to send at this time");
        logMessage("=== Cron Job Completed ===");
        exit(0);
    }

    // Send reminders to each client
    $sentCount = 0;
    $failedCount = 0;

    foreach ($bookings as $booking) {
        logMessage("Processing booking: {$booking->booking_code} for {$booking->client_name}");

        try {
            // Format date and time
            $formattedDate = format_date_id($booking->appointment_date);
            $formattedTime = substr($booking->appointment_time, 0, 5) . ' WIB';

            // Create reminder message for client
            $message = "Assalamu'alaikum {$booking->client_name},\n\n";
            $message .= "🔔 *PENGINGAT JANJI TEMU*\n\n";
            $message .= "Anda memiliki jadwal hipnoterapi *HARI INI*:\n\n";
            $message .= "👳 Terapis: {$booking->therapist_name}\n";
            $message .= "✨ Layanan: " . ($booking->service_name ?: 'Konsultasi Umum') . "\n";
            $message .= "📅 Tanggal: {$formattedDate}\n";
            $message .= "⏰ Waktu: {$formattedTime}\n";
            $message .= "📍 Lokasi: Al-Bashiro Hypnotherapy Center\n";
            $message .= "🔖 Kode Booking: *{$booking->booking_code}*\n\n";
            $message .= "⚠️ Mohon datang 10 menit lebih awal.\n\n";
            $message .= "Jika berhalangan hadir, silakan hubungi kami segera.\n\n";
            $message .= "Jazakallahu khairan. 🤲";

            // Send via Fonnte to individual client
            $result = $fonnteService->sendMessage($booking->wa_number, $message);

            if ($result['success']) {
                // Update booking - mark reminder as sent
                $updateStmt = $pdo->prepare("UPDATE bookings SET reminder_sent = 1, reminder_sent_at = NOW() WHERE id = ?");
                $updateStmt->execute([$booking->id]);

                // Log to reminder_logs table
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'sent')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);

                $sentCount++;
                logMessage("✓ Reminder sent successfully to client: {$booking->wa_number}");

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
                $failedCount++;
                $errorMsg = $result['message'] ?? 'Unknown error';
                logMessage("✗ Failed to send to {$booking->wa_number}: {$errorMsg}");

                // Log failure
                $logStmt = $pdo->prepare("INSERT INTO reminder_logs (booking_id, message, wa_number, sent_at, delivery_status) VALUES (?, ?, ?, NOW(), 'failed')");
                $logStmt->execute([$booking->id, $message, $booking->wa_number]);
            }

        } catch (Exception $e) {
            $failedCount++;
            logMessage("✗ Exception for booking {$booking->booking_code}: " . $e->getMessage());
        }

        // Small delay between messages
        sleep(1);
    }

    logMessage("Summary: {$sentCount} sent, {$failedCount} failed out of {$totalBookings} total");
    logMessage("=== Cron Job Completed ===");

} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}
