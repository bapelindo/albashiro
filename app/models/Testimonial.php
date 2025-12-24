<?php
/**
 * Albashiro - Testimonial Model
 */

class Testimonial
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all testimonials
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT t.*, th.name as therapist_name 
             FROM testimonials t 
             LEFT JOIN therapists th ON t.therapist_id = th.id 
             ORDER BY t.created_at DESC"
        )->fetchAll();
    }

    /**
     * Get featured testimonials
     */
    public function getFeatured($limit = 6)
    {
        return $this->db->query(
            "SELECT t.*, th.name as therapist_name 
             FROM testimonials t 
             LEFT JOIN therapists th ON t.therapist_id = th.id 
             WHERE t.is_featured = 1 
             ORDER BY t.rating DESC, t.created_at DESC 
             LIMIT ?",
            [$limit]
        )->fetchAll();
    }

    /**
     * Get testimonials by therapist
     */
    public function getByTherapist($therapistId)
    {
        return $this->db->query(
            "SELECT * FROM testimonials 
             WHERE therapist_id = ? 
             ORDER BY created_at DESC",
            [$therapistId]
        )->fetchAll();
    }
}
