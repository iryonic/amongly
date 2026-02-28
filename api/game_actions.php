<?php
// api/game_actions.php
require_once __DIR__ . '/../config/config.php';

$action   = $_GET['action'] ?? '';
$roomId   = get_current_room_id();
$playerId = get_current_player_id();

if (!$roomId || !$playerId) json_response(['error' => 'Unauthorized'], 401);

$gameCtrl = new GameController();
$round    = $gameCtrl->getRoundState($roomId);
if (!$round) json_response(['error' => 'No active round'], 404);

$roomModel = new Room();
$playerModel = new Player();
$room = $roomModel->getById($roomId);
$status = $room['status'] ?? '';
$player = $playerModel->getById($playerId);

if (!$player || !$player['is_alive']) {
    json_response(['error' => 'You are eliminated and cannot participate in this round.'], 403);
}

switch ($action) {

    case 'submit_clue':
        if ($status !== 'clue') json_response(['error' => 'Cannot submit clue right now.'], 400);
        $clueText = $_POST['clue'] ?? '';
        if (empty($clueText)) json_response(['error' => 'Clue required'], 400);
        $gameCtrl->submitClue($round['id'], $playerId, $clueText);
        json_response(['success' => true]);
        break;

    case 'submit_vote':
        if ($status !== 'voting') json_response(['error' => 'Voting is not active.'], 400);
        $votedId = (int)($_POST['voted_id'] ?? 0);
        if (!$votedId) json_response(['error' => 'Invalid target'], 400);
        if ($votedId === (int)$playerId) json_response(['error' => 'You cannot vote for yourself.'], 400);
        
        $target = $playerModel->getById($votedId);
        if (!$target || $target['room_id'] != $roomId || !$target['is_alive']) {
            json_response(['error' => 'Target is no longer available.'], 400);
        }

        $gameCtrl->submitVote($round['id'], $playerId, $votedId);
        json_response(['success' => true]);
        break;

    case 'skip_vote':
        if ($status !== 'voting') json_response(['error' => 'Voting is not active.'], 400);
        $gameCtrl->submitSkipVote($round['id'], $playerId);
        json_response(['success' => true]);
        break;

    case 'imposter_guess':
        if ($status !== 'clue') json_response(['error' => 'You can only guess during the clue phase.'], 400);
        if ($round['imposter_id'] != $playerId) {
            json_response(['error' => 'Only the imposter can guess.'], 403);
        }

        // Limit to ONE guess per round
        $stmt = Database::getInstance()->prepare("SELECT id FROM imposter_guesses WHERE round_id = ? AND player_id = ?");
        $stmt->execute([$round['id'], $playerId]);
        if ($stmt->fetch()) {
            json_response(['error' => 'You already made your one attempt for this round.'], 400);
        }

        $guess = trim($_POST['guess'] ?? '');
        if (empty($guess)) json_response(['error' => 'Guess cannot be empty.'], 400);

        $result = $gameCtrl->submitImposterGuess($round['id'], $playerId, $guess, $roomId);
        json_response(['success' => true, 'correct' => $result['correct']]);
        break;

    default:
        json_response(['error' => 'Invalid action'], 400);
}
