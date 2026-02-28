<?php
// api/room_actions.php
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';
$roomModel = new Room();
$playerModel = new Player();

switch ($action) {
    case 'create':
        $catId = $_POST['category_id'] ?? 1;
        $difficulty = $_POST['difficulty'] ?? 'easy';
        $maxPlayers = $_POST['max_players'] ?? 20;
        $nickname = get_node_alias();
        $token = get_node_token();

        if (empty($nickname)) json_response(['error' => 'Could not verify your identity. Please refresh the page.'], 400);
        if (!$token) json_response(['error'  => 'Your session token is missing. Please refresh the page.'], 400);

        $roomId = $roomModel->create($catId, $difficulty, $maxPlayers);
        $playerId = $playerModel->create($roomId, $nickname, $token, true);
        $roomModel->updateHost($roomId, $playerId);

        $_SESSION['room_id'] = $roomId;
        $_SESSION['player_id'] = $playerId;

        json_response(['success' => true]);
        break;

    case 'join':
        $code = strtoupper(clean($_POST['room_code'] ?? ''));
        $nickname = get_node_alias();
        $token = get_node_token();

        if (empty($code))     json_response(['error' => 'Please enter a room code.'], 400);
        if (empty($nickname)) json_response(['error' => 'You need a nickname to join. Please go back and set one.'], 400);
        if (empty($token))    json_response(['error' => 'Session error. Please refresh and try again.'], 400);

        $room = $roomModel->getByCode($code);
        if (!$room) json_response(['error' => 'Room not found. Check the code.'], 404);
        if ($room['status'] !== 'waiting') json_response(['error' => 'Game already in progress. Cannot join now.'], 403);

        $players = $playerModel->getPlayersInRoom($room['id']);
        if (count($players) >= $room['max_players']) json_response(['error' => 'Room full'], 403);

        // Check unique nickname
        foreach ($players as $p) {
            if (strtolower($p['nickname']) === strtolower($nickname)) {
                json_response(['error' => 'That name is already taken in this room. Choose another.'], 400);
            }
        }

        $playerId = $playerModel->create($room['id'], $nickname, $token, false);
        
        $_SESSION['room_id'] = $room['id'];
        $_SESSION['player_id'] = $playerId;

        json_response(['success' => true]);
        break;

    case 'start_game':
        $roomId = get_current_room_id();
        $playerId = get_current_player_id();
        $player = $playerModel->getById($playerId);

        if (!$player || !$player['is_host']) json_response(['error' => 'Only host can start'], 403);

        $room = $roomModel->getById($roomId);
        if ($room['status'] !== 'waiting') json_response(['error' => 'A mission is already in progress.'], 400);

        $players = $playerModel->getPlayersInRoom($roomId);
        if (count($players) < 3) {
            json_response(['error' => 'You need at least 3 players to start the mission.'], 400);
        }

        $gameCtrl = new GameController();
        if ($gameCtrl->startRound($roomId)) {
            json_response(['success' => true]);
        } else {
            json_response(['error' => 'No secret words found for this category/difficulty. Add some in the admin panel!'], 400);
        }
        break;

    default:
        json_response(['error' => 'Invalid action'], 400);
}
