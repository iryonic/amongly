<?php
// admin/words.php
require_once '../config/config.php';

$db = Database::getInstance();
$categories = $db->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $word = clean($_POST['word']);
    $catId = $_POST['category_id'];
    $diff = $_POST['difficulty'];
    
    $stmt = $db->prepare("INSERT INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");
    $stmt->execute([$catId, $word, $diff]);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Words | Amongly</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
    <div class="max-w-2xl mx-auto glass p-8 rounded-3xl">
        <h2 class="text-3xl font-bold mb-6">Add New Word</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-400 text-sm mb-2">Word</label>
                <input type="text" name="word" required class="w-full bg-slate-800 p-4 rounded-xl border border-slate-700">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-2">Category</label>
                    <select name="category_id" class="w-full bg-slate-800 p-4 rounded-xl border border-slate-700">
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-2">Difficulty</label>
                    <select name="difficulty" class="w-full bg-slate-800 p-4 rounded-xl border border-slate-700">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full bg-indigo-600 py-4 rounded-xl font-bold">SAVE WORD</button>
            <a href="dashboard.php" class="block text-center text-slate-400 text-sm">Cancel</a>
        </form>
    </div>
</body>
</html>
