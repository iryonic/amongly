<?php
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
require_once 'config/config.php';
$db = Database::getInstance();

try {
    $db->exec("ALTER TABLE rooms ADD COLUMN IF NOT EXISTS phase_start_time INT DEFAULT 0 AFTER current_round");
    echo "Column phase_start_time added successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
