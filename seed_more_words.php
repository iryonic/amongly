<?php
require_once 'config/config.php';
$db = Database::getInstance();

$newCategories = [
    'Space',
    'Technology',
    'Sports',
    'History',
    'Science',
    'Gaming',
    'Music',
    'Nature'
];

$data = [
    'Space' => [
        'Astronaut', 'Galaxy', 'Nebula', 'Supernova', 'Black Hole', 'Asteroid', 'Comet', 'Meteor', 'Satellite', 'Rocket',
        'Telescope', 'Planet', 'Star', 'Moon', 'Sun', 'Orbit', 'Gravity', 'Cosmos', 'Universe', 'Eclipse',
        'Mars', 'Jupiter', 'Saturn', 'Venus', 'Mercury', 'Neptune', 'Uranus', 'Pluto', 'Earth', 'Milky Way',
        'Spacecraft', 'Rover', 'Shuttle', 'Station', 'Module', 'Capsule', 'Suit', 'Helmet', 'Oxygen', 'Vacuum',
        'Alien', 'UFO', 'Extraterrestrial', 'Constellation', 'Lightyear', 'Parsec', 'Quasar', 'Pulsar', 'Wormhole', 'Horizon'
    ],
    'Technology' => [
        'Computer', 'Laptop', 'Smartphone', 'Tablet', 'Smartwatch', 'Server', 'Network', 'Internet', 'Website', 'Software',
        'Hardware', 'Database', 'Algorithm', 'Program', 'Code', 'Script', 'Encryption', 'Security', 'Firewall', 'Antivirus',
        'Robot', 'Android', 'Cyborg', 'AI', 'Machine Learning', 'Blockchain', 'Crypto', 'NFT', 'Metaverse', 'VR',
        'Cloud', 'Data', 'Storage', 'Processor', 'Memory', 'Monitor', 'Keyboard', 'Mouse', 'Printer', 'Scanner',
        'Battery', 'Charger', 'Cable', 'Wireless', 'Bluetooth', 'WiFi', 'Signal', 'Fiber', 'Satellite', 'Radar'
    ],
    'Sports' => [
        'Soccer', 'Basketball', 'Tennis', 'Baseball', 'Golf', 'Cricket', 'Rugby', 'Hockey', 'Volleyball', 'Badminton',
        'Football', 'Boxing', 'Wrestling', 'Karate', 'Judo', 'Swimming', 'Diving', 'Surfing', 'Sailing', 'Skiing',
        'Snowboarding', 'Skating', 'Cycling', 'Running', 'Athletics', 'Gymnastics', 'Dance', 'Yoga', 'Pilates', 'Hiking',
        'Stadium', 'Arena', 'Court', 'Field', 'Pitch', 'Track', 'Pool', 'Gym', 'Club', 'Team',
        'Coach', 'Referee', 'Umpire', 'Captain', 'Player', 'Athlete', 'Winner', 'Champion', 'Medal', 'Trophy'
    ],
    'History' => [
        'Pyramid', 'Castle', 'Empire', 'Kingdom', 'Revolution', 'Civilization', 'Ancient', 'Medieval', 'Modern', 'Future',
        'Knight', 'Warrior', 'Samurai', 'Ninja', 'Viking', 'Pirate', 'Explorer', 'Scientist', 'Inventor', 'Leader',
        'Pharaoh', 'Emperor', 'King', 'Queen', 'President', 'General', 'Soldier', 'Civil', 'World', 'War',
        'Discovery', 'Invention', 'Dynasty', 'Renaissance', 'Industrial', 'Digital', 'Atomic', 'Space', 'Stone', 'Iron',
        'Temple', 'Monument', 'Ruins', 'Tomb', 'Manuscript', 'Artifact', 'Legend', 'Myth', 'Epic', 'Saga'
    ],
    'Science' => [
        'Atom', 'Molecule', 'Element', 'Chemical', 'Reaction', 'Laboratory', 'Microscope', 'Flask', 'Beaker', 'Bunsen',
        'Cell', 'DNA', 'Gene', 'Protein', 'Enzyme', 'Bacteria', 'Virus', 'Organism', 'Species', 'Evolution',
        'Physics', 'Chemistry', 'Biology', 'Geology', 'Astronomy', 'Math', 'Formula', 'Equation', 'Theory', 'Law',
        'Energy', 'Force', 'Motion', 'Light', 'Sound', 'Heat', 'Electricity', 'Magnetism', 'Gravity', 'Quantum',
        'Experiment', 'Research', 'Control', 'Variable', 'Result', 'Analysis', 'Data', 'Paper', 'Journal', 'Patent'
    ],
    'Gaming' => [
        'Console', 'Joystick', 'Controller', 'Keyboard', 'Mouse', 'Headset', 'Level', 'Boss', 'Enemy', 'Player',
        'Multiplayer', 'Online', 'Server', 'Ping', 'Lag', 'Glitch', 'Patch', 'Update', 'DLC', 'Mod',
        'RPG', 'FPS', 'MMO', 'MOBA', 'Strategy', 'Adventure', 'Puzzle', 'Action', 'Sim', 'Racing',
        'Avatar', 'Skin', 'Emote', 'Currency', 'Item', 'Inventory', 'Map', 'Quest', 'Guild', 'Clan',
        'Arcade', 'Retro', 'Classic', 'Modern', 'Indie', 'Esports', 'Stream', 'Twitch', 'Discord', 'Steam'
    ],
    'Music' => [
        'Guitar', 'Piano', 'Drum', 'Violin', 'Flute', 'Trumpet', 'Saxophone', 'Bass', 'Keyboard', 'Synthesizer',
        'Song', 'Track', 'Album', 'Single', 'EP', 'Playlist', 'Lyrics', 'Melody', 'Harmony', 'Rhythm',
        'Rock', 'Pop', 'Jazz', 'Blues', 'Hip Hop', 'Rap', 'Reggae', 'Country', 'Classical', 'Electronic',
        'Concert', 'Festival', 'Gig', 'Tour', 'Studio', 'Stage', 'Mic', 'Amp', 'Speaker', 'Headphones',
        'Singer', 'Band', 'Group', 'Chorus', 'Orchestra', 'DJ', 'Producer', 'Composer', 'Fans', 'Star'
    ],
    'Nature' => [
        'Forest', 'Jungle', 'Mountain', 'Valley', 'Hill', 'Cave', 'Canyon', 'Desert', 'Oasis', 'Safari',
        'Ocean', 'Sea', 'River', 'Lake', 'Stream', 'Waterfall', 'Pond', 'Swamp', 'Marsh', 'Bay',
        'Tree', 'Flower', 'Plant', 'Leaf', 'Root', 'Grass', 'Moss', 'Mushroom', 'Seed', 'Fruit',
        'Storm', 'Rain', 'Snow', 'Wind', 'Cloud', 'Sun', 'Moon', 'Thunder', 'Lightning', 'Rainbow',
        'Eco', 'Earth', 'Green', 'Wild', 'Life', 'World', 'Space', 'Environment', 'Climate', 'Global'
    ]
];

foreach ($newCategories as $catName) {
    // Add Category
    $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$catName]);
    $catId = $db->lastInsertId();

    // Add Words
    if (isset($data[$catName])) {
        $wordStmt = $db->prepare("INSERT INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");
        foreach ($data[$catName] as $word) {
            $diffs = ['easy', 'medium', 'hard'];
            $diff = $diffs[array_rand($diffs)];
            $wordStmt->execute([$catId, $word, $diff]);
        }
    }
}

echo "Added " . count($newCategories) . " new categories and " . (count($newCategories) * 50) . " words.";
?>
