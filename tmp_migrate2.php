<?php
require_once 'config/config.php';
$db = Database::getInstance();
$db->exec("ALTER TABLE rooms MODIFY COLUMN status ENUM('waiting','word_reveal','clue','decision','voting','resolving','reveal','finished') DEFAULT 'waiting'");
echo "ENUM updated OK\n";
