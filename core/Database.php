<?php
/**
 * Albashiro - Islamic Spiritual Hypnotherapy
 * Database PDO Wrapper (Singleton Pattern)
 */

class Database
{
    private static $instance = null;
    private $pdo;
    private $statement;

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $port = defined('DB_PORT') ? DB_PORT : 3306;
        $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // SSL Config with PHP 8.5+ Compatibility
        $caPath = SITE_ROOT . '/isrgrootx1.pem';
        if (defined('Pdo\Mysql::ATTR_SSL_CA')) {
            $options[constant('Pdo\Mysql::ATTR_SSL_CA')] = $caPath;
            $options[constant('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')] = true;
        } else {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO instance directly
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Prepare and execute query with bindings
     */
    public function query($sql, $params = [])
    {
        $start = microtime(true);
        $this->statement = $this->pdo->prepare($sql);
        $this->statement->execute($params);
        $duration = (microtime(true) - $start) * 1000;
        ServerTiming::accumulate('db', $duration);
        return $this;
    }

    /**
     * Fetch all results as array of objects
     */
    public function fetchAll()
    {
        return $this->statement->fetchAll();
    }

    /**
     * Fetch single result as object
     */
    public function fetch()
    {
        return $this->statement->fetch();
    }

    /**
     * Fetch single column value
     */
    public function fetchColumn()
    {
        return $this->statement->fetchColumn();
    }

    /**
     * Get row count
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
