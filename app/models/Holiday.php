<?php
/**
 * Albashiro - Holiday Model
 * Manages global holidays that apply to all therapists
 */

class Holiday
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all holidays
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT * FROM global_holidays ORDER BY holiday_date ASC"
        )->fetchAll();
    }

    /**
     * Get upcoming holidays from today onwards
     */
    public function getUpcoming($limit = null)
    {
        $sql = "SELECT * FROM global_holidays 
                WHERE holiday_date >= CURDATE() 
                ORDER BY holiday_date ASC";

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get holiday by specific date
     */
    public function getByDate($date)
    {
        return $this->db->query(
            "SELECT * FROM global_holidays WHERE holiday_date = ?",
            [$date]
        )->fetch();
    }

    /**
     * Check if a specific date is a holiday
     */
    public function isHoliday($date)
    {
        $result = $this->db->query(
            "SELECT COUNT(*) FROM global_holidays WHERE holiday_date = ?",
            [$date]
        )->fetchColumn();

        return $result > 0;
    }

    /**
     * Add a new holiday
     */
    public function add($date, $name)
    {
        return $this->db->query(
            "INSERT INTO global_holidays (holiday_date, name) 
             VALUES (?, ?)",
            [$date, $name]
        )->rowCount();
    }

    /**
     * Add multiple holidays at once (bulk import)
     */
    public function addBulk($holidays)
    {
        $count = 0;
        foreach ($holidays as $holiday) {
            try {
                // Skip if holiday already exists for this date
                $existing = $this->getByDate($holiday['date']);
                if (!$existing) {
                    $this->add($holiday['date'], $holiday['name']);
                    $count++;
                }
            } catch (Exception $e) {
                // Skip duplicates or errors
                continue;
            }
        }
        return $count;
    }

    /**
     * Delete a holiday
     */
    public function delete($id)
    {
        return $this->db->query(
            "DELETE FROM global_holidays WHERE id = ?",
            [$id]
        )->rowCount();
    }

    /**
     * Get holidays in a date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->db->query(
            "SELECT * FROM global_holidays 
             WHERE holiday_date BETWEEN ? AND ? 
             ORDER BY holiday_date ASC",
            [$startDate, $endDate]
        )->fetchAll();
    }
}
