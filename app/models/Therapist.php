<?php
/**
 * Albashiro - Therapist Model
 */

class Therapist
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all therapists
     * @param bool $includeInactive Whether to include inactive therapists
     */
    public function getAll($includeInactive = false)
    {
        $sql = "SELECT * FROM therapists";

        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }

        $sql .= " ORDER BY id ASC";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get single therapist by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT * FROM therapists WHERE id = ? AND is_active = 1",
            [$id]
        )->fetch();
    }

    /**
     * Get therapist count
     */
    public function count()
    {
        return $this->db->query(
            "SELECT COUNT(*) FROM therapists WHERE is_active = 1"
        )->fetchColumn();
    }
}
