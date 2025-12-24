<?php
/**
 * Albashiro - FAQ Model
 */

class Faq
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active FAQs
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC"
        )->fetchAll();
    }

    /**
     * Get single FAQ by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT * FROM faqs WHERE id = ? AND is_active = 1",
            [$id]
        )->fetch();
    }
}
