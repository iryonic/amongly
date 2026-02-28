<?php
// views/logout.php
// Called when a player leaves the room or logs out.
// Removes the player record and clears the PHP session room data.

$token = get_node_token();
if ($token) {
    $playerModel = new Player();
    $player = $playerModel->getByToken($token);
    if ($player) {
        // If host leaves, try to assign a new host (or just let the room sit)
        $db = Database::getInstance();
        if ($player['is_host']) {
            // Assign host to next player in room
            $stmt = $db->prepare("SELECT id FROM players WHERE room_id = ? AND id != ? AND is_alive = 1 ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([$player['room_id'], $player['id']]);
            $newHost = $stmt->fetch();
            if ($newHost) {
                $db->prepare("UPDATE players SET is_host = 0 WHERE room_id = ?")->execute([$player['room_id']]);
                $db->prepare("UPDATE players SET is_host = 1 WHERE id = ?")->execute([$newHost['id']]);
                $db->prepare("UPDATE rooms SET host_id = ? WHERE id = ?")->execute([$newHost['id'], $player['room_id']]);
            }
        }
        // Remove the player
        $playerModel->removeByToken($token);
    }
}

// Clear session room data
unset($_SESSION['room_id'], $_SESSION['player_id']);

// Redirect back to landing
header('Location: index.php');
exit;
