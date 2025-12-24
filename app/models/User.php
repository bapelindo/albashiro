<?php
/**
 * Albashiro - User Model
 */

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->db->query(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        )->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        return $this->db->query(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$id]
        )->fetch();
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($id)
    {
        return $this->db->query(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$id]
        )->rowCount();
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'editor'
        ]);
        return $this->db->lastInsertId();
    }
}
