<?php
// api/identity_actions.php
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'set_nickname') {
        $nickname = clean($_POST['nickname'] ?? '');
        $avatar = $_POST['avatar'] ?? '👤';
        
        if (strlen($nickname) < 2 || strlen($nickname) > 15) {
            json_response(['success' => false, 'error' => 'Alias must be 2-15 characters'], 400);
        }
        
        $_SESSION['nickname'] = $nickname;
        $_SESSION['avatar'] = $avatar;

        $token = get_node_token();
        $playerModel = new Player();
        $p = $playerModel->getByToken($token);
        if ($p) {
             if ($playerModel->isNicknameTaken($p['room_id'], $nickname, $p['id'])) {
                 json_response(['success' => false, 'error' => 'That name is already in use in your room.'], 400);
             }
             $db = Database::getInstance();
             $stmt = $db->prepare('UPDATE players SET nickname = ?, avatar = ? WHERE id = ?');
             $stmt->execute([$nickname, $avatar, $p['id']]);
        }

        json_response(['success' => true]);
    }
}

json_response(['success' => false, 'error' => 'Invalid action'], 400);
