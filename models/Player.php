<?php
// models/Player.php

class Player {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($roomId, $nickname, $token, $isHost = false) {
        $avatar = get_node_avatar();
        $stmt = $this->db->prepare("INSERT INTO players (room_id, nickname, avatar, is_host, session_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$roomId, $nickname, $avatar, $isHost ? 1 : 0, $token]);
        return $this->db->lastInsertId();
    }

    public function getByToken($token) {
        // Only match players in active (non-finished) rooms
        $stmt = $this->db->prepare("
            SELECT p.* FROM players p
            JOIN rooms r ON p.room_id = r.id
            WHERE p.session_id = ? AND r.status != 'finished'
            ORDER BY p.created_at DESC LIMIT 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function removeByToken($token) {
        $stmt = $this->db->prepare("DELETE FROM players WHERE session_id = ?");
        return $stmt->execute([$token]);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getPlayersInRoom($roomId) {
        $stmt = $this->db->prepare("SELECT id, nickname, avatar, is_host, is_alive, last_active_at FROM players WHERE room_id = ? ORDER BY created_at ASC");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll();
    }

    public function updateHeartbeat($playerId) {
        $stmt = $this->db->prepare("UPDATE players SET last_active_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$playerId]);
    }

    public function kill($playerId) {
        $stmt = $this->db->prepare("UPDATE players SET is_alive = 0 WHERE id = ?");
        $stmt->execute([$playerId]);
    }

    public function isNicknameTaken($roomId, $nickname, $excludeId = null) {
        if (!$roomId) return false;
        $sql = "SELECT id FROM players WHERE room_id = ? AND LOWER(nickname) = LOWER(?)";
        $params = [$roomId, $nickname];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}
