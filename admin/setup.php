<?php
// admin/setup.php
require_once '../config/config.php';

$db = Database::getInstance();

// Create default admin if none exists
$check = $db->query("SELECT id FROM admins LIMIT 1")->fetch();

if (!$check) {
    $email = 'admin@amongly.com';
    $password = 'amongly123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO admins (email, password_hash) VALUES (?, ?)");
    $stmt->execute([$email, $hash]);

    echo "Default admin created: <br>";
    echo "Email: $email <br>";
    echo "Password: $password <br>";
} else {
    echo "Admin already exists. Delete this file for security.";
}
?>
