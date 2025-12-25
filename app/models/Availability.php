<?php
/**
 * Albashiro - Availability Model
 * Manages therapist availability schedules
 */

class Availability
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get therapist weekly availability
     */
    public function getWeeklySchedule($therapistId)
    {
        return $this->db->query(
            "SELECT * FROM therapist_availability 
             WHERE therapist_id = ? 
             ORDER BY FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')",
            [$therapistId]
        )->fetchAll();
    }

    /**
     * Set availability for a specific day
     */
    public function setDayAvailability($therapistId, $day, $startTime, $endTime)
    {
        // Check if record exists
        $existing = $this->db->query(
            "SELECT id FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ?",
            [$therapistId, $day]
        )->fetch(); // Added fetch() back to check existence properly

        if ($startTime === null || $endTime === null) {
            // Delete record for unavailable days
            if ($existing) {
                return $this->db->query(
                    "DELETE FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ?",
                    [$therapistId, $day]
                )->rowCount();
            }
            return 0; // No record to delete
        }

        if ($existing) {
            return $this->db->query(
                "UPDATE therapist_availability SET is_available = 1, start_time = ?, end_time = ? WHERE therapist_id = ? AND day_of_week = ?",
                [$startTime, $endTime, $therapistId, $day]
            )->rowCount(); // Added rowCount() for consistency
        } else {
            return $this->db->query(
                "INSERT INTO therapist_availability (therapist_id, day_of_week, is_available, start_time, end_time) VALUES (?, ?, 1, ?, ?)",
                [$therapistId, $day, $startTime, $endTime]
            )->rowCount(); // Added rowCount() for consistency
        }
    }

    /**
     * Get availability overrides for a date range
     */
    public function getOverrides($therapistId, $startDate, $endDate)
    {
        return $this->db->query(
            "SELECT * FROM availability_overrides 
             WHERE therapist_id = ? AND override_date BETWEEN ? AND ? 
             ORDER BY override_date",
            [$therapistId, $startDate, $endDate]
        )->fetchAll();
    }

    /**
     * Add availability override (holiday, special day)
     */
    public function addOverride($therapistId, $date, $isAvailable = false, $startTime = null, $endTime = null, $reason = null)
    {
        return $this->db->query(
            "INSERT INTO availability_overrides (therapist_id, override_date, start_time, end_time, is_available, reason) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$therapistId, $date, $startTime, $endTime, $isAvailable ? 1 : 0, $reason]
        )->rowCount();
    }

    /**
     * Delete override
     */
    public function deleteOverride($id)
    {
        return $this->db->query(
            "DELETE FROM availability_overrides WHERE id = ?",
            [$id]
        )->rowCount();
    }

    /**
     * Check if therapist is available at specific date/time
     */
    public function isAvailable($therapistId, $date, $time)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        // Check for global holiday first (overrides everything)
        $holidayCheck = $this->db->query(
            "SELECT COUNT(*) FROM global_holidays WHERE holiday_date = ?",
            [$date]
        )->fetchColumn();

        if ($holidayCheck > 0) {
            return false;
        }

        // Check for override first
        $override = $this->db->query(
            "SELECT * FROM availability_overrides 
             WHERE therapist_id = ? AND override_date = ?",
            [$therapistId, $date]
        )->fetch();

        if ($override) {
            if (!$override->is_available) {
                return false;
            }
            // Check time if override has specific hours
            if ($override->start_time && $override->end_time) {
                return $time >= $override->start_time && $time <= $override->end_time;
            }
        }

        // Check regular weekly schedule
        $schedule = $this->db->query(
            "SELECT * FROM therapist_availability 
             WHERE therapist_id = ? AND day_of_week = ? AND is_available = 1",
            [$therapistId, $dayOfWeek]
        )->fetch();

        if (!$schedule) {
            return false;
        }

        return $time >= $schedule->start_time && $time <= $schedule->end_time;
    }

    /**
     * Get available time slots for a specific date
     */
    public function getAvailableSlots($therapistId, $date, $slotDuration = 60)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $slots = [];

        // Check for global holiday first
        $holidayCheck = $this->db->query(
            "SELECT COUNT(*) FROM global_holidays WHERE holiday_date = ?",
            [$date]
        )->fetchColumn();

        if ($holidayCheck > 0) {
            return $slots; // Return empty slots on holidays
        }

        // Get schedule
        $schedule = $this->db->query(
            "SELECT * FROM therapist_availability 
             WHERE therapist_id = ? AND day_of_week = ? AND is_available = 1",
            [$therapistId, $dayOfWeek]
        )->fetch();

        if (!$schedule) {
            return $slots;
        }

        // Check override
        $override = $this->db->query(
            "SELECT * FROM availability_overrides 
             WHERE therapist_id = ? AND override_date = ?",
            [$therapistId, $date]
        )->fetch();

        if ($override && !$override->is_available) {
            return $slots;
        }

        $startTime = $override && $override->start_time ? $override->start_time : $schedule->start_time;
        $endTime = $override && $override->end_time ? $override->end_time : $schedule->end_time;

        // Get existing bookings for this date
        $bookings = $this->db->query(
            "SELECT appointment_time FROM bookings 
             WHERE therapist_id = ? AND appointment_date = ? AND status != 'cancelled'",
            [$therapistId, $date]
        )->fetchAll();

        $bookedTimes = array_map(fn($b) => $b->appointment_time, $bookings);

        // Generate slots
        $current = strtotime($startTime);
        $end = strtotime($endTime);

        while ($current < $end) {
            $slotTime = date('H:i:s', $current);

            if (!in_array($slotTime, $bookedTimes)) {
                $slots[] = [
                    'time' => $slotTime,
                    'display' => date('H:i', $current),
                    'available' => true
                ];
            }

            $current = strtotime("+$slotDuration minutes", $current);
        }

        return $slots;
    }
}
