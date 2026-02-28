<?php
// config/config.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);          // Never output errors as HTML into JSON
ini_set('log_errors', 1);              // Log to Apache error log instead

require_once 'db.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Player.php';
require_once __DIR__ . '/../controllers/GameController.php';

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function json_response($data, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function get_header_safe($name) {
    $target = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$target])) return $_SERVER[$target];
    
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strcasecmp($k, $name) === 0) return $v;
            }
        }
    }
    return null;
}

function get_node_token() {
    return get_header_safe('X-Node-Token');
}

function get_node_alias() {
    $val = get_header_safe('X-Node-Alias');
    return $val ? base64_decode($val) : ($_SESSION['nickname'] ?? null);
}

function get_node_avatar() {
    $val = get_header_safe('X-Node-Avatar');
    return $val ? base64_decode($val) : ($_SESSION['avatar'] ?? '👤');
}

function get_current_player() {
    $token = get_node_token();
    if (!$token) return null;
    
    $playerModel = new Player();
    return $playerModel->getByToken($token);
}

function get_current_player_id() {
    $player = get_current_player();
    return $player ? $player['id'] : null;
}

function get_current_room_id() {
    $player = get_current_player();
    return $player ? $player['room_id'] : null;
}
?>
