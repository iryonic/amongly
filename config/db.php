<?php
// config/db.php

// Environment Switch (Set to true for production)
$isProduction = ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_ADDR'] !== '127.0.0.1');

if ($isProduction) {
    // Production Database Settings
    define('DB_HOST', 'localhost'); // Usually localhost on shared hosting
    define('DB_NAME', 'u167160735_amongly');
    define('DB_USER', 'u167160735_amongly');
    define('DB_PASS', '0z>DHgmVm@Z');
} else {
    // Local Development Settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'amongly');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}
