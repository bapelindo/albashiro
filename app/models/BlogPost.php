<?php
/**
 * Albashiro - BlogPost Model
 */

class BlogPost
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all published posts
     */
    public function getPublished($limit = 10, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             WHERE p.status = 'published' 
             ORDER BY p.published_at DESC 
             LIMIT $limit OFFSET $offset"
        )->fetchAll();
    }

    /**
     * Get all posts (for admin)
     */
    public function getAll()
    {
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             ORDER BY p.created_at DESC"
        )->fetchAll();
    }

    /**
     * Get post by slug
     */
    public function getBySlug($slug)
    {
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             WHERE p.slug = ? AND p.status = 'published'",
            [$slug]
        )->fetch();
    }

    /**
     * Get post by ID (for admin)
     */
    public function getById($id)
    {
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             WHERE p.id = ?",
            [$id]
        )->fetch();
    }

    /**
     * Increment view count
     */
    public function incrementViews($id)
    {
        return $this->db->query(
            "UPDATE blog_posts SET views = views + 1 WHERE id = ?",
            [$id]
        )->rowCount();
    }

    /**
     * Create new post
     */
    public function create($data)
    {
        $slug = $this->generateSlug($data['title']);

        $sql = "INSERT INTO blog_posts 
                (title, slug, excerpt, content, featured_image, author_id, category, tags, status, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $data['title'],
            $slug,
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['author_id'],
            $data['category'] ?? 'Artikel',
            $data['tags'] ?? null,
            $data['status'] ?? 'draft',
            $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update post
     */
    public function update($id, $data)
    {
        $sql = "UPDATE blog_posts SET 
                title = ?, 
                excerpt = ?, 
                content = ?, 
                featured_image = ?, 
                category = ?, 
                tags = ?, 
                status = ?,
                published_at = CASE WHEN status = 'published' AND published_at IS NULL THEN NOW() ELSE published_at END,
                updated_at = NOW()
                WHERE id = ?";

        return $this->db->query($sql, [
            $data['title'],
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category'] ?? 'Artikel',
            $data['tags'] ?? null,
            $data['status'] ?? 'draft',
            $id
        ])->rowCount();
    }

    /**
     * Delete post
     */
    public function delete($id)
    {
        return $this->db->query(
            "DELETE FROM blog_posts WHERE id = ?",
            [$id]
        )->rowCount();
    }

    /**
     * Generate unique slug
     */
    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug exists
        $originalSlug = $slug;
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug)
    {
        return $this->db->query(
            "SELECT COUNT(*) FROM blog_posts WHERE slug = ?",
            [$slug]
        )->fetchColumn() > 0;
    }

    /**
     * Count published posts
     */
    public function countPublished()
    {
        return $this->db->query(
            "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'"
        )->fetchColumn();
    }

    /**
     * Get recent posts
     */
    public function getRecent($limit = 3)
    {
        $limit = intval($limit);
        return $this->db->query(
            "SELECT * FROM blog_posts 
             WHERE status = 'published' 
             ORDER BY published_at DESC 
             LIMIT $limit"
        )->fetchAll();
    }

    /**
     * Get published posts by tag
     */
    public function getPublishedByTag($tag, $limit = 10, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             WHERE p.status = 'published' 
             AND (p.tags LIKE ? OR p.tags LIKE ? OR p.tags LIKE ? OR p.tags = ?)
             ORDER BY p.published_at DESC 
             LIMIT $limit OFFSET $offset",
            [
                $tag . ',%',      // tag at start
                '%, ' . $tag . ',%', // tag at middle
                '%, ' . $tag,     // tag at end
                $tag              // exact match
            ]
        )->fetchAll();
    }

    /**
     * Count published posts by tag
     */
    public function countPublishedByTag($tag)
    {
        return $this->db->query(
            "SELECT COUNT(*) FROM blog_posts 
             WHERE status = 'published' 
             AND (tags LIKE ? OR tags LIKE ? OR tags LIKE ? OR tags = ?)",
            [
                $tag . ',%',
                '%, ' . $tag . ',%',
                '%, ' . $tag,
                $tag
            ]
        )->fetchColumn();
    }

    /**
     * Search published posts
     */
    public function searchPublished($query, $limit = 10, $offset = 0)
    {
        $searchTerm = '%' . $query . '%';
        $limit = intval($limit);
        $offset = intval($offset);
        return $this->db->query(
            "SELECT p.*, u.name as author_name 
             FROM blog_posts p 
             LEFT JOIN users u ON p.author_id = u.id 
             WHERE p.status = 'published' 
             AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ? OR p.tags LIKE ?)
             ORDER BY p.published_at DESC 
             LIMIT $limit OFFSET $offset",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
        )->fetchAll();
    }

    /**
     * Count search results
     */
    public function countSearchPublished($query)
    {
        $searchTerm = '%' . $query . '%';
        return $this->db->query(
            "SELECT COUNT(*) FROM blog_posts 
             WHERE status = 'published' 
             AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ? OR tags LIKE ?)",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
        )->fetchColumn();
    }
}
