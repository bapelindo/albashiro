<?php
/**
 * Albashiro - Gallery Model
 */

class Gallery
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all images, optionally filtered by category
     */
    public function getAll($categoryId = null)
    {
        $sql = "SELECT g.*, c.name as category_name 
                FROM galleries g 
                LEFT JOIN gallery_categories c ON g.category_id = c.id";

        $params = [];

        if ($categoryId) {
            $sql .= " WHERE g.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY g.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get single image by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT * FROM galleries WHERE id = ?",
            [$id]
        )->fetch();
    }

    /**
     * Upload/Create new image entry
     */
    public function create($data)
    {
        $this->db->query(
            "INSERT INTO galleries (category_id, image_url) VALUES (?, ?)",
            [$data['category_id'], $data['image_url']]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Delete image
     */
    public function delete($id)
    {
        // Get image URL first to delete file
        $image = $this->getById($id);
        if ($image) {
            $filePath = __DIR__ . '/../../public/images/' . $image->image_url;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $this->db->query(
            "DELETE FROM galleries WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get paginated images
     */
    public function getPaginated($categoryId = null, $page = 1, $limit = 12)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT g.*, c.name as category_name 
                FROM galleries g 
                LEFT JOIN gallery_categories c ON g.category_id = c.id";

        $params = [];

        if ($categoryId) {
            $sql .= " WHERE g.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY g.created_at DESC LIMIT $limit OFFSET $offset";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Count total images
     */
    public function countAll($categoryId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM galleries";
        $params = [];

        if ($categoryId) {
            $sql .= " WHERE category_id = ?";
            $params[] = $categoryId;
        }

        return $this->db->query($sql, $params)->fetch()->count;
    }

    /**
     * Delete multiple images
     */
    public function deleteBulk($ids)
    {
        if (empty($ids))
            return false;

        // Get all images to delete files
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $images = $this->db->query(
            "SELECT * FROM galleries WHERE id IN ($placeholders)",
            $ids
        )->fetchAll();

        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public/images/' . $image->image_url;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete records
        return $this->db->query(
            "DELETE FROM galleries WHERE id IN ($placeholders)",
            $ids
        );
    }
}
