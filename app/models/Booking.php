<?php
/**
 * Albashiro - Booking Model
 */

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if therapist is available at given date and time
     */
    public function checkAvailability($therapistId, $date, $time)
    {
        $result = $this->db->query(
            "SELECT COUNT(*) FROM bookings 
             WHERE therapist_id = ? 
             AND appointment_date = ? 
             AND appointment_time = ? 
             AND status != 'cancelled'",
            [$therapistId, $date, $time]
        )->fetchColumn();

        return $result == 0; // Returns true if available (no conflicts)
    }

    /**
     * Create new booking
     */
    public function create($data)
    {
        $bookingCode = generate_booking_code();

        $sql = "INSERT INTO bookings 
                (booking_code, therapist_id, service_id, client_name, wa_number, email, problem_description, appointment_date, appointment_time, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $this->db->query($sql, [
            $bookingCode,
            $data['therapist_id'],
            $data['service_id'] ?? null,
            $data['client_name'],
            $data['wa_number'],
            $data['email'] ?? null,
            $data['problem_description'],
            $data['appointment_date'],
            $data['appointment_time'] ?? null
        ]);

        return $bookingCode;
    }

    /**
     * Get booking by code
     */
    public function getByCode($code)
    {
        return $this->db->query(
            "SELECT b.*, t.name as therapist_name, s.name as service_name 
             FROM bookings b 
             LEFT JOIN therapists t ON b.therapist_id = t.id 
             LEFT JOIN services s ON b.service_id = s.id 
             WHERE b.booking_code = ?",
            [$code]
        )->fetch();
    }

    /**
     * Get all bookings with therapist and service info
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT b.*, 
                    t.name as therapist_name,
                    s.name as service_name
             FROM bookings b
             LEFT JOIN therapists t ON b.therapist_id = t.id
             LEFT JOIN services s ON b.service_id = s.id
             ORDER BY b.appointment_date DESC, b.appointment_time DESC"
        )->fetchAll();
    }

    /**
     * Update booking status
     */
    public function updateStatus($id, $status)
    {
        return $this->db->query(
            "UPDATE bookings SET status = ? WHERE id = ?",
            [$status, $id]
        )->rowCount();
    }

    /**
     * Update booking notes
     */
    public function updateNotes($id, $notes)
    {
        return $this->db->query(
            "UPDATE bookings SET notes = ? WHERE id = ?",
            [$notes, $id]
        )->rowCount();
    }

    /**
     * Get bookings by therapist with service info
     */
    public function getByTherapist($therapistId)
    {
        return $this->db->query(
            "SELECT b.*, 
                    t.name as therapist_name,
                    s.name as service_name
             FROM bookings b
             LEFT JOIN therapists t ON b.therapist_id = t.id
             LEFT JOIN services s ON b.service_id = s.id
             WHERE b.therapist_id = ?
             ORDER BY b.appointment_date DESC, b.appointment_time DESC",
            [$therapistId]
        )->fetchAll();
    }

    /**
     * Get bookings by date range (for calendar)
     */
    public function getByDateRange($startDate, $endDate, $therapistId = null)
    {
        if ($therapistId) {
            return $this->db->query(
                "SELECT b.*, t.name as therapist_name, s.name as service_name 
                 FROM bookings b 
                 LEFT JOIN therapists t ON b.therapist_id = t.id 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.appointment_date BETWEEN ? AND ? 
                 AND b.therapist_id = ?
                 ORDER BY b.appointment_date, b.appointment_time",
                [$startDate, $endDate, $therapistId]
            )->fetchAll();
        } else {
            return $this->db->query(
                "SELECT b.*, t.name as therapist_name, s.name as service_name 
                 FROM bookings b 
                 LEFT JOIN therapists t ON b.therapist_id = t.id 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.appointment_date BETWEEN ? AND ? 
                 ORDER BY b.appointment_date, b.appointment_time",
                [$startDate, $endDate]
            )->fetchAll();
        }
    }

    /**
     * Get booking by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT b.*, t.name as therapist_name, s.name as service_name 
             FROM bookings b 
             LEFT JOIN therapists t ON b.therapist_id = t.id 
             LEFT JOIN services s ON b.service_id = s.id 
             WHERE b.id = ?",
            [$id]
        )->fetch();
    }

    /**
     * Reschedule booking
     */
    public function reschedule($id, $newDate, $newTime, $reason, $rescheduledBy)
    {
        // Get current booking
        $booking = $this->getById($id);

        if (!$booking) {
            return false;
        }

        // Save to reschedule history
        $this->db->query(
            "INSERT INTO reschedule_history (booking_id, old_date, old_time, new_date, new_time, reason, rescheduled_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$id, $booking->appointment_date, $booking->appointment_time, $newDate, $newTime, $reason, $rescheduledBy]
        );

        // Update booking
        return $this->db->query(
            "UPDATE bookings 
             SET original_date = COALESCE(original_date, appointment_date),
                 original_time = COALESCE(original_time, appointment_time),
                 appointment_date = ?,
                 appointment_time = ?,
                 reschedule_count = reschedule_count + 1,
                 reschedule_reason = ?,
                 rescheduled_by = ?,
                 rescheduled_at = NOW()
             WHERE id = ?",
            [$newDate, $newTime, $reason, $rescheduledBy, $id]
        )->rowCount();
    }

    /**
     * Get reschedule history for a booking
     */
    public function getRescheduleHistory($bookingId)
    {
        return $this->db->query(
            "SELECT * FROM reschedule_history 
             WHERE booking_id = ? 
             ORDER BY created_at DESC",
            [$bookingId]
        )->fetchAll();
    }

    /**
     * Check if a time slot is already booked
     */
    public function isSlotBooked($therapistId, $date, $time)
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM bookings 
             WHERE therapist_id = ? 
             AND appointment_date = ? 
             AND appointment_time = ?
             AND status NOT IN ('cancelled')",
            [$therapistId, $date, $time]
        )->fetch();

        return $result->count > 0;
    }
}


