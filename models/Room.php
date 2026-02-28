<?php
// models/Room.php

class Room {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($categoryId, $difficulty, $maxPlayers) {
        $roomCode = $this->generateRoomCode();
        $stmt = $this->db->prepare("INSERT INTO rooms (room_code, category_id, difficulty, max_players, status) VALUES (?, ?, ?, ?, 'waiting')");
        $stmt->execute([$roomCode, $categoryId, $difficulty, $maxPlayers]);
        return $this->db->lastInsertId();
    }

    public function getByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE room_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($roomId, $status) {
        $stmt = $this->db->prepare("UPDATE rooms SET status = ?, phase_start_time = ? WHERE id = ?");
        $stmt->execute([$status, time(), $roomId]);
    }

    public function updateHost($roomId, $hostId) {
        $stmt = $this->db->prepare("UPDATE rooms SET host_id = ? WHERE id = ?");
        $stmt->execute([$hostId, $roomId]);
    }

    private function generateRoomCode($length = 6) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Avoid O, 0, I, 1
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        // Check uniqueness
        $stmt = $this->db->prepare("SELECT id FROM rooms WHERE room_code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            return $this->generateRoomCode($length);
        }
        return $code;
    }
    
    public function getCategories() {
        return $this->db->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();
    }
}
