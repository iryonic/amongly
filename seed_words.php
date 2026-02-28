<?php
require_once 'config/config.php';
$db = Database::getInstance();

$data = [
    // 1: Animals
    1 => [
        'Lion', 'Tiger', 'Zebra', 'Monkey', 'Hippo', 'Gorilla', 'Cheetah', 'Leopard', 'Panther', 'Jaguar',
        'Rhino', 'Elephant', 'Giraffe', 'Hyena', 'Wolf', 'Bear', 'Panda', 'Koala', 'Kangaroo', 'Platypus',
        'Crocodile', 'Alligator', 'Snake', 'Lizard', 'Chameleon', 'Turtle', 'Tortoise', 'Frog', 'Toad', 'Salamander',
        'Eagle', 'Falcon', 'Hawk', 'Owl', 'Parrot', 'Penguin', 'Ostrich', 'Flamingo', 'Peacock', 'Swan',
        'Shark', 'Dolphin', 'Whale', 'Octopus', 'Squid', 'Jellyfish', 'Starfish', 'Seahorse', 'Crab', 'Lobster'
    ],
    // 2: Food
    2 => [
        'Pizza', 'Burger', 'Sushi', 'Taco', 'Pasta', 'Steak', 'Salad', 'Soup', 'Sandwich', 'Omelette',
        'Pancakes', 'Waffles', 'Croissant', 'Bagel', 'Donut', 'Muffin', 'Cookie', 'Brownie', 'Cake', 'Pie',
        'Ice Cream', 'Chocolate', 'Cheese', 'Yogurt', 'Butter', 'Bread', 'Rice', 'Noodles', 'Quinoa', 'Couscous',
        'Apple', 'Banana', 'Orange', 'Strawberry', 'Grape', 'Watermelon', 'Pineapple', 'Mango', 'Peach', 'Kiwi',
        'Carrot', 'Broccoli', 'Spinach', 'Tomato', 'Potato', 'Onion', 'Garlic', 'Pepper', 'Cucumber', 'Eggplant'
    ],
    // 3: Movies
    3 => [
        'Avatar', 'Inception', 'Titanic', 'Gladiator', 'Matrix', 'Jaws', 'Alien', 'Predator', 'Rocky', 'Rambo',
        'Frozen', 'Shrek', 'Moana', 'Coco', 'Toy Story', 'Finding Nemo', 'Lion King', 'Cars', 'Up', 'Ratatouille',
        'Batman', 'Superman', 'Spiderman', 'Iron Man', 'Thor', 'Hulk', 'Wonder Woman', 'Deadpool', 'Joker', 'Logan',
        'Godfather', 'Scarface', 'Goodfellas', 'Casablanca', 'Psycho', 'Vertigo', 'Interstellar', 'Gravity', 'Dune', 'Arrival',
        'Hamilton', 'Sound of Music', 'Grease', 'Chicago', 'Mamma Mia', 'La La Land', 'Hairspray', 'Rent', 'Newsies', 'Cats'
    ],
    // 4: Travel
    4 => [
        'Paris', 'London', 'Tokyo', 'Rome', 'New York', 'Dubai', 'Sydney', 'Cairo', 'Berlin', 'Moscow',
        'Passport', 'Suitcase', 'Backpack', 'Ticket', 'Camera', 'Map', 'Compass', 'Guidebook', 'Sunglasses', 'Sunscreen',
        'Airplane', 'Train', 'Bus', 'Ship', 'Boat', 'Bicycle', 'Car', 'Taxi', 'Subway', 'Helicopter',
        'Hotel', 'Resort', 'Hostel', 'Camping', 'Airbnb', 'Villa', 'Cruise', 'Safari', 'Museum', 'Beach',
        'Island', 'Mountain', 'Forest', 'Desert', 'Valley', 'River', 'Lake', 'Ocean', 'Waterfall', 'Volcano'
    ]
];

$stmt = $db->prepare("INSERT INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");

foreach ($data as $catId => $words) {
    foreach ($words as $word) {
        // Assign random difficulty for variety
        $diffs = ['easy', 'medium', 'hard'];
        $diff = $diffs[array_rand($diffs)];
        $stmt->execute([$catId, $word, $diff]);
    }
}

echo "Seeded " . (count($data) * 50) . " words successfully.";
?>
