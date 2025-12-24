<?php
/**
 * Secure Authentication Helper for Vercel Serverless
 * Uses encrypted cookies instead of PHP sessions
 */

class SecureAuth
{
    private static $cookieName = 'albashiro_auth';
    private static $cookieLifetime = 86400; // 24 hours

    /**
     * Get encryption key from environment or generate one
     */
    private static function getKey()
    {
        $key = getenv('AUTH_SECRET_KEY') ?: 'change-this-to-random-32-char-key!!';
        return hash('sha256', $key, true);
    }

    /**
     * Encrypt data
     */
    private static function encrypt($data)
    {
        $key = self::getKey();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt(
            json_encode($data),
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data
     */
    private static function decrypt($encrypted)
    {
        try {
            $key = self::getKey();
            $data = base64_decode($encrypted);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);

            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $key,
                0,
                $iv
            );

            return json_decode($decrypted, true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Set authentication cookie
     */
    public static function login($userId, $userName, $userEmail, $userRole)
    {
        $data = [
            'user_id' => $userId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'user_role' => $userRole,
            'expires' => time() + self::$cookieLifetime
        ];

        $encrypted = self::encrypt($data);

        $secure = isset($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie(
            self::$cookieName,
            $encrypted,
            [
                'expires' => time() + self::$cookieLifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        // Also set in session for backward compatibility
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_email'] = $userEmail;
        $_SESSION['user_role'] = $userRole;
    }

    /**
     * Get current user from cookie
     */
    public static function getUser()
    {
        // Try session first (for local development)
        if (isset($_SESSION['user_id'])) {
            return [
                'user_id' => $_SESSION['user_id'],
                'user_name' => $_SESSION['user_name'],
                'user_email' => $_SESSION['user_email'],
                'user_role' => $_SESSION['user_role']
            ];
        }

        // Try cookie (for Vercel)
        if (!isset($_COOKIE[self::$cookieName])) {
            return null;
        }

        $data = self::decrypt($_COOKIE[self::$cookieName]);

        if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
            self::logout();
            return null;
        }

        // Refresh session variables
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['user_name'] = $data['user_name'];
        $_SESSION['user_email'] = $data['user_email'];
        $_SESSION['user_role'] = $data['user_role'];

        return $data;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return self::getUser() !== null;
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        // Clear cookie
        setcookie(
            self::$cookieName,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        // Clear session
        session_unset();
        session_destroy();
    }
}
