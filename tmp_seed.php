<?php
require_once 'config/config.php';

$db = Database::getInstance();

echo "Seeding categories...\n";
$categories = [
    ['name' => 'Food & Drinks'],
    ['name' => 'Movies & TV'],
    ['name' => 'Technology'],
    ['name' => 'Animals']
];

foreach ($categories as $cat) {
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    $stmt->execute([$cat['name']]);
}

echo "Seeding words...\n";
$data = [
    'Food & Drinks' => [
        'easy' => ['Pizza', 'Burger', 'Pasta', 'Sushi', 'Coffee', 'Donut'],
        'medium' => ['Guacamole', 'Smoothie', 'Baguette', 'Espresso', 'Kimchi'],
        'hard' => ['Ratatouille', 'Kombucha', 'Souffle', 'Risotto']
    ],
    'Movies & TV' => [
        'easy' => ['Batarang', 'Titanic', 'Avatar', 'Simpsons', 'Ironman'],
        'medium' => ['Inception', 'Star Wars', 'Sherlock', 'Gladiator'],
        'hard' => ['Parasite', 'Whiplash', 'Mandalorian']
    ],
    'Technology' => [
        'easy' => ['Laptop', 'iPhone', 'Google', 'Wifi', 'Robot'],
        'medium' => ['Software', 'Network', 'Bitcoin', 'Antivirus'],
        'hard' => ['Algorithm', 'Blockchain', 'Cybersecurity']
    ],
    'Animals' => [
        'easy' => ['Lion', 'Tiger', 'Elephant', 'Rabbit', 'Monkey'],
        'medium' => ['Dolphin', 'Penguin', 'Giraffe', 'Hamster'],
        'hard' => ['Platypus', 'Chameleon', 'Axolotl']
    ]
];

foreach ($data as $catName => $diffs) {
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$catName]);
    $catId = $stmt->fetchColumn();
    
    foreach ($diffs as $diff => $words) {
        foreach ($words as $word) {
            $stmt = $db->prepare("INSERT IGNORE INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");
            $stmt->execute([$catId, $word, $diff]);
        }
    }
}

echo "Done! Categories and words seeded.\n";
