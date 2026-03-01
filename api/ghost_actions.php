<?php
// api/ghost_actions.php
require_once __DIR__ . '/../config/config.php';

$action   = $_GET['action'] ?? '';
$roomId   = get_current_room_id();
$playerId = get_current_player_id();

if (!$roomId || !$playerId) json_response(['error' => 'Unauthorized'], 401);

$playerModel = new Player();
$player = $playerModel->getById($playerId);

if (!$player) json_response(['error' => 'Player not found'], 404);

// Ghost chat is only for ELIMINATED players OR if the game is in REVEAL phase
$gameCtrl = new GameController();
$roomModel = new Room();
$room = $roomModel->getById($roomId);
$status = $room['status'] ?? '';
$round = $gameCtrl->getRoundState($roomId);
if (!$round) $round = $gameCtrl->getLastRound($roomId);

$isEliminated = ($player['is_alive'] == 0);
$isReveal = ($status === 'reveal');

if (!$isEliminated && !$isReveal) {
    json_response(['error' => 'Only neutralized players can participate in the ghost frequencies.'], 403);
}

$db = Database::getInstance();

switch ($action) {
    case 'send':
        $message = trim($_POST['message'] ?? '');
        $emoji   = trim($_POST['emoji'] ?? '');

        if (empty($message) && empty($emoji)) {
            json_response(['error' => 'Protocol requires a signal.'], 400);
        }

        $stmt = $db->prepare("INSERT INTO ghost_chat (room_id, round_id, player_id, message, emoji) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$roomId, $round['id'], $playerId, clean($message), clean($emoji)]);
        
        json_response(['success' => true]);
        break;

    default:
        json_response(['error' => 'Invalid action'], 400);
}
