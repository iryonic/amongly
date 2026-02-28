<?php
// api/admin_actions.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    json_response(['error' => 'Unauthorized'], 401);
}

$action = $_GET['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    case 'delete_word':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_response(['error' => 'Invalid ID'], 400);
        $stmt = $db->prepare("DELETE FROM words WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['success' => true]);
        break;

    case 'delete_room':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_response(['error' => 'Invalid ID'], 400);
        $stmt = $db->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['success' => true]);
        break;

    case 'add_category':
        $name = clean($_POST['name'] ?? '');
        if (empty($name)) json_response(['error' => 'Name required'], 400);
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        json_response(['success' => true]);
        break;

    case 'edit_category':
        $id = (int)($_POST['id'] ?? 0);
        $name = clean($_POST['name'] ?? '');
        if (!$id || empty($name)) json_response(['error' => 'ID and Name required'], 400);
        $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        json_response(['success' => true]);
        break;

    case 'delete_category':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_response(['error' => 'Invalid ID'], 400);
        // Check if words exist
        $cnt = $db->prepare("SELECT COUNT(*) FROM words WHERE category_id = ?");
        $cnt->execute([$id]);
        if ($cnt->fetchColumn() > 0) {
            json_response(['error' => 'Cannot delete. Category has assigned words.'], 400);
        }
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['success' => true]);
        break;

    case 'update_setting':
        $key = clean($_POST['key'] ?? '');
        $value = $_POST['value'] ?? '';
        if (!$key) json_response(['error' => 'Key required'], 400);
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
        json_response(['success' => true]);
        break;

    case 'bulk_delete_words':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) json_response(['error' => 'No words selected'], 400);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM words WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        json_response(['success' => true]);
        break;

    case 'bulk_add_words':
        $rawWords = $_POST['words'] ?? '';
        $catId = (int)($_POST['category_id'] ?? 0);
        $diff = $_POST['difficulty'] ?? 'easy';
        
        if (empty($rawWords) || !$catId) json_response(['error' => 'Words and category required'], 400);
        
        // Split by comma OR newline (handles both)
        $wordArr = preg_split('/[,\n\r]+/', $rawWords, -1, PREG_SPLIT_NO_EMPTY);
        $stmt = $db->prepare("INSERT INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");
        
        $count = 0;
        foreach ($wordArr as $w) {
            $w = clean(trim($w));
            if (!empty($w)) {
                $stmt->execute([$catId, $w, $diff]);
                $count++;
            }
        }
        json_response(['success' => true, 'count' => $count]);
        break;

    case 'bulk_edit_words':
        $ids = $_POST['ids'] ?? [];
        $catId = (int)($_POST['category_id'] ?? 0);
        $diff = $_POST['difficulty'] ?? '';

        if (empty($ids)) json_response(['error' => 'No words selected'], 400);
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = [];
        $set = [];

        if ($catId) {
            $set[] = "category_id = ?";
            $params[] = $catId;
        }
        if ($diff) {
            $set[] = "difficulty = ?";
            $params[] = $diff;
        }

        if (empty($set)) json_response(['error' => 'No changes specified'], 400);

        $sql = "UPDATE words SET " . implode(', ', $set) . " WHERE id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($params, $ids));
        
        json_response(['success' => true]);
        break;

    default:
        json_response(['error' => 'Invalid action'], 400);
}
