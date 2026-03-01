<?php
// api/state.php
require_once __DIR__ . '/../config/config.php';

$roomId   = get_current_room_id();
$playerId = get_current_player_id();

if (!$roomId || !$playerId) {
    json_response(['error' => 'Unauthorized'], 401);
}

$roomModel   = new Room();
$playerModel = new Player();
$gameCtrl    = new GameController();

// Heartbeat
$playerModel->updateHeartbeat($playerId);

// Check and auto-transition phases FIRST
$gameCtrl->checkPhaseTransition($roomId);
$gameCtrl->cleanupRoom($roomId);

// Fetch fresh room + player + round AFTER transition
$room   = $roomModel->getById($roomId);
$player = $playerModel->getById($playerId);

if (!$player || !$room) {
    json_response(['error' => 'Mission terminated or user removed.'], 401);
}

$round  = $gameCtrl->getRoundState($roomId);
if (!$round && $room['status'] === 'reveal') {
    $round = $gameCtrl->getLastRound($roomId);
}

// Build state
$state = [
    'room_status'    => $room['status'],
    'room_code'      => $room['room_code'],
    'is_host'        => (bool)$player['is_host'],
    'is_alive'       => (bool)$player['is_alive'],
    'phase_start'    => (int)$room['phase_start_time'],
    'server_time'    => time(),
    'players'        => $playerModel->getPlayersInRoom($roomId),
    'is_imposter'    => ($round && $round['imposter_id'] == $playerId),
    'word'           => ($round && $round['imposter_id'] != $playerId) ? $round['word'] : null,
    'clues'          => [],
    'submitted_clue' => false,   // safe default — prevents JS undefined
    'submitted_vote' => false,   // safe default
    'vote_summary'   => ['skip' => 0, 'eliminate' => 0, 'total' => 0],
];

if ($round) {
    $roundId = $round['id'];
    $db = Database::getInstance();

    // Clue Privacy: Only send clues in ALL active game phases EXCEPT 'clue' phase.
    // In 'clue' phase, only send if the current player HAS already submitted theirs.
    if ($room['status'] !== 'clue') {
        $state['clues'] = $gameCtrl->getClues($roundId);
    } else {
        $stmt = $db->prepare("SELECT player_id FROM clues WHERE round_id = ? AND player_id = ?");
        $stmt->execute([$roundId, $playerId]);
        if ($stmt->fetch()) {
            $state['clues'] = $gameCtrl->getClues($roundId);
        }
    }

    // Track which players have taken action
    $stmt = $db->prepare("SELECT player_id FROM clues WHERE round_id = ?");
    $stmt->execute([$roundId]);
    $state['clue_player_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $db->prepare("SELECT voter_id FROM votes WHERE round_id = ?");
    $stmt->execute([$roundId]);
    $state['voted_player_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Ghost Reveal: Dead players see EVERYTHING.
    if (!$player['is_alive']) {
        $state['is_ghost']   = true;
        $state['imposter_id'] = $round['imposter_id'];
        $state['word']        = $round['word']; // Reveal word to ghosts immediately
    }

    // Ghost Chat: Fetch last 15 messages for this room/round
    $stmt = $db->prepare("
        SELECT g.message, g.emoji, p.nickname, p.avatar 
        FROM ghost_chat g 
        JOIN players p ON g.player_id = p.id 
        WHERE g.room_id = ? AND g.round_id = ?
        ORDER BY g.created_at ASC LIMIT 15
    ");
    $stmt->execute([$roomId, $roundId]);
    $state['ghost_chat'] = $stmt->fetchAll();

    if ($room['status'] === 'clue') {
        $state['submitted_clue'] = in_array($playerId, $state['clue_player_ids']);
    }

    if ($room['status'] === 'voting') {
        $state['submitted_vote'] = in_array($playerId, $state['voted_player_ids']);
        $state['vote_tally']   = $gameCtrl->getVoteTally($roundId);
        $state['vote_summary'] = $gameCtrl->getVoteSummary($roundId);  // skip vs eliminate counts
    }

    if ($room['status'] === 'reveal') {
        $imposterPlayer   = $playerModel->getById($round['imposter_id']);
        $eliminatedPlayer = $room['eliminated_player_id'] ? $playerModel->getById($room['eliminated_player_id']) : null;

        $isImposter = ($round['imposter_id'] == $playerId);

        $revealDuration   = 12; // Must match GameController::REVEAL_DURATION
        $elapsed          = time() - (int)$room['phase_start_time'];
        $state['reveal'] = [
            'winner'           => $room['winner'],
            'imposter_name'    => $imposterPlayer ? $imposterPlayer['nickname'] : '?',
            'imposter_avatar'  => $imposterPlayer ? ($imposterPlayer['avatar'] ?? '👤') : '👤',
            'word'             => $round['word'],
            'eliminated_name'  => $eliminatedPlayer ? $eliminatedPlayer['nickname'] : null,
            'end_reason'       => $room['end_reason'] ?? '',
            'you_won'          => ($room['winner'] === 'crew'    && !$isImposter)
                               || ($room['winner'] === 'imposter' && $isImposter),
            'reveal_countdown' => max(0, $revealDuration - $elapsed),
        ];
    }
}

json_response($state);
