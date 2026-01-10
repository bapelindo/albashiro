<?php
/**
 * Albashiro - Gallery Category Model
 */

class GalleryCategory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all categories
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT * FROM gallery_categories ORDER BY name ASC"
        )->fetchAll();
    }

    /**
     * Get category by ID
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT * FROM gallery_categories WHERE id = ?",
            [$id]
        )->fetch();
    }

    /**
     * Create new category
     */
    public function create($data)
    {
        $this->db->query(
            "INSERT INTO gallery_categories (name) VALUES (?)",
            [$data['name']]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update category
     */
    public function update($id, $data)
    {
        return $this->db->query(
            "UPDATE gallery_categories SET name = ? WHERE id = ?",
            [$data['name'], $id]
        );
    }

    /**
     * Delete category
     */
    /**
     * Delete category and associated images
     */
    public function delete($id)
    {
        // 1. Get all images in this category
        $images = $this->db->query(
            "SELECT * FROM galleries WHERE category_id = ?",
            [$id]
        )->fetchAll();

        // 2. Delete image files
        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public/images/' . $image->image_url;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // 3. Delete category (DB cascade should handle gallery records, but we do it manually to be safe)
        $this->db->query("DELETE FROM galleries WHERE category_id = ?", [$id]);

        return $this->db->query(
            "DELETE FROM gallery_categories WHERE id = ?",
            [$id]
        );
    }
}
