<?php
/**
 * Albashiro - Reminder Model
 * Manages booking reminders and notifications
 */

class Reminder
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create reminder for a booking
     */
    public function create($bookingId, $type, $scheduledAt, $messageText)
    {
        return $this->db->query(
            "INSERT INTO booking_reminders (booking_id, reminder_type, scheduled_at, message_text, status) 
             VALUES (?, ?, ?, ?, 'pending')",
            [$bookingId, $type, $scheduledAt, $messageText]
        )->rowCount();
    }

    /**
     * Get pending reminders that need to be sent
     */
    public function getPendingReminders()
    {
        return $this->db->query(
            "SELECT r.*, b.client_name, b.wa_number, b.appointment_date, b.appointment_time, 
                    t.name as therapist_name, s.name as service_name
             FROM booking_reminders r
             JOIN bookings b ON r.booking_id = b.id
             LEFT JOIN therapists t ON b.therapist_id = t.id
             LEFT JOIN services s ON b.service_id = s.id
             WHERE r.status = 'pending' 
             AND r.scheduled_at <= NOW()
             ORDER BY r.scheduled_at ASC"
        )->fetchAll();
    }

    /**
     * Mark reminder as sent
     */
    public function markAsSent($id, $messageId = null)
    {
        return $this->db->query(
            "UPDATE booking_reminders 
             SET status = 'sent', sent_at = NOW(), whatsapp_message_id = ? 
             WHERE id = ?",
            [$messageId, $id]
        )->rowCount();
    }

    /**
     * Mark reminder as failed
     */
    public function markAsFailed($id, $errorMessage)
    {
        return $this->db->query(
            "UPDATE booking_reminders 
             SET status = 'failed', error_message = ? 
             WHERE id = ?",
            [$errorMessage, $id]
        )->rowCount();
    }

    /**
     * Get reminders for a booking
     */
    public function getByBooking($bookingId)
    {
        return $this->db->query(
            "SELECT * FROM booking_reminders 
             WHERE booking_id = ? 
             ORDER BY scheduled_at DESC",
            [$bookingId]
        )->fetchAll();
    }

    /**
     * Schedule all reminders for a booking
     */
    public function scheduleForBooking($bookingId, $appointmentDate, $appointmentTime, $clientName, $therapistName)
    {
        $appointmentDateTime = "$appointmentDate $appointmentTime";

        // 1. Confirmation (immediate)
        $confirmationMsg = "🌙 Konfirmasi Booking Albashiro\n\n";
        $confirmationMsg .= "Halo $clientName,\n";
        $confirmationMsg .= "Booking Anda telah dikonfirmasi!\n\n";
        $confirmationMsg .= "📅 Tanggal: " . date('d M Y', strtotime($appointmentDate)) . "\n";
        $confirmationMsg .= "⏰ Waktu: " . date('H:i', strtotime($appointmentTime)) . " WIB\n";
        $confirmationMsg .= "👤 Terapis: $therapistName\n\n";
        $confirmationMsg .= "Jika ada perubahan, hubungi kami.\n";
        $confirmationMsg .= "Terima kasih! 🙏";

        $this->create($bookingId, 'confirmation', date('Y-m-d H:i:s'), $confirmationMsg);

        // 2. 24 hours before
        $reminder24h = date('Y-m-d H:i:s', strtotime($appointmentDateTime . ' -24 hours'));
        if (strtotime($reminder24h) > time()) {
            $msg24h = "⏰ Pengingat Booking Albashiro\n\n";
            $msg24h .= "Halo $clientName,\n";
            $msg24h .= "Pengingat: Anda memiliki sesi hipnoterapi besok!\n\n";
            $msg24h .= "📅 Tanggal: " . date('d M Y', strtotime($appointmentDate)) . "\n";
            $msg24h .= "⏰ Waktu: " . date('H:i', strtotime($appointmentTime)) . " WIB\n";
            $msg24h .= "👤 Terapis: $therapistName\n\n";
            $msg24h .= "Sampai jumpa! 🌙";

            $this->create($bookingId, '24h', $reminder24h, $msg24h);
        }

        // 3. 1 hour before
        $reminder1h = date('Y-m-d H:i:s', strtotime($appointmentDateTime . ' -1 hour'));
        if (strtotime($reminder1h) > time()) {
            $msg1h = "🔔 Pengingat Terakhir - Albashiro\n\n";
            $msg1h .= "Halo $clientName,\n";
            $msg1h .= "Sesi Anda akan dimulai dalam 1 jam!\n\n";
            $msg1h .= "⏰ Waktu: " . date('H:i', strtotime($appointmentTime)) . " WIB\n";
            $msg1h .= "👤 Terapis: $therapistName\n\n";
            $msg1h .= "Kami tunggu kedatangan Anda! 🙏";

            $this->create($bookingId, '1h', $reminder1h, $msg1h);
        }

        return true;
    }

    /**
     * Delete all reminders for a booking
     */
    public function deleteByBooking($bookingId)
    {
        return $this->db->query(
            "DELETE FROM booking_reminders WHERE booking_id = ?",
            [$bookingId]
        )->rowCount();
    }
}
