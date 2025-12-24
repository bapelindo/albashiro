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
     * Get all active therapists
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT * FROM therapists WHERE is_active = 1 ORDER BY id ASC"
        )->fetchAll();
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
