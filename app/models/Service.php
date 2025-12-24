<?php
/**
 * Albashiro - Service Model
 */

class Service
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all services
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT * FROM services ORDER BY sort_order ASC"
        )->fetchAll();
    }

    /**
     * Get featured services
     */
    public function getFeatured()
    {
        return $this->db->query(
            "SELECT * FROM services WHERE is_featured = 1 ORDER BY sort_order ASC"
        )->fetchAll();
    }

    /**
     * Get single service by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT * FROM services WHERE id = ?",
            [$id]
        )->fetch();
    }

    /**
     * Get services by target audience
     */
    public function getByAudience($audience)
    {
        return $this->db->query(
            "SELECT * FROM services WHERE target_audience = ? OR target_audience = 'Semua' ORDER BY sort_order ASC",
            [$audience]
        )->fetchAll();
    }

    /**
     * Get services grouped by audience
     */
    public function getGroupedByAudience()
    {
        $services = $this->getAll();
        $grouped = [
            'Anak' => [],
            'Remaja' => [],
            'Dewasa' => [],
            'Semua' => []
        ];

        foreach ($services as $service) {
            $grouped[$service->target_audience][] = $service;
        }

        return $grouped;
    }
}
