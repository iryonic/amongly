<?php
// api/host_actions.php
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';
$roomId = get_current_room_id();
$playerId = get_current_player_id();

if (!$roomId || !$playerId) {
    json_response(['error' => 'Unauthorized'], 401);
}

$roomModel = new Room();
$playerModel = new Player();
$player = $playerModel->getById($playerId);

if (!$player || !$player['is_host']) {
    json_response(['error' => 'Only the host can do this.'], 403);
}

$room = $roomModel->getById($roomId);

switch ($action) {
    case 'kick':
        if ($room['status'] !== 'waiting') {
            json_response(['error' => 'You cannot kick players while a mission is active.'], 400);
        }
        $targetId = $_POST['target_id'] ?? 0;
        if (!$targetId || $targetId == $playerId) {
            json_response(['error' => 'Invalid target.'], 400);
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM players WHERE id = ? AND room_id = ?");
        $stmt->execute([$targetId, $roomId]);
        json_response(['success' => true]);
        break;

    case 'reset_to_lobby':
        if ($room['status'] !== 'reveal') {
            json_response(['error' => 'Mission is not in reveal state.'], 400);
        }
        $gameCtrl = new GameController();
        $gameCtrl->resetForNewGame($roomId);
        json_response(['success' => true]);
        break;

    default:
        json_response(['error' => 'Unknown action.'], 400);
}
