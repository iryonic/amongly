<?php
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
require_once 'config/config.php';
$db = Database::getInstance();
$res = $db->query("DESCRIBE rooms")->fetchAll();
foreach ($res as $r) {
    echo "| {$r['Field']} |\n";
}
