<?php
// admin/dashboard.php
require_once '../config/config.php';

// Simple Auth Check (Placeholder for real admin session)
if (!isset($_SESSION['admin_logged_in'])) {
    // For demo, let's just show a login form or redirect
    // header('Location: index.php');
}

$db = Database::getInstance();
$roomCount = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$activeRooms = $db->query("SELECT COUNT(*) FROM rooms WHERE status != 'finished'")->fetchColumn();
$totalRounds = $db->query("SELECT COUNT(*) FROM rounds")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Amongly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        <h1 class="text-4xl font-black">Admin Dashboard</h1>
        
        <div class="grid grid-cols-3 gap-6">
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <p class="text-slate-400 font-bold uppercase text-xs">Total Rooms</p>
                <p class="text-4xl font-black text-indigo-400"><?= $roomCount ?></p>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <p class="text-slate-400 font-bold uppercase text-xs">Active Games</p>
                <p class="text-4xl font-black text-green-400"><?= $activeRooms ?></p>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <p class="text-slate-400 font-bold uppercase text-xs">Rounds Played</p>
                <p class="text-4xl font-black text-purple-400"><?= $totalRounds ?></p>
            </div>
        </div>

        <div class="bg-slate-800 rounded-3xl overflow-hidden border border-slate-700">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-xl font-bold">Categories & Words</h2>
                <button class="bg-indigo-600 px-4 py-2 rounded-lg font-bold text-sm">Add Word</button>
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-900/50 text-slate-400 text-xs uppercase">
                    <tr>
                        <th class="p-4">Word</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Difficulty</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php
                    $words = $db->query("SELECT w.*, c.name as cat_name FROM words w JOIN categories c ON w.category_id = c.id LIMIT 10")->fetchAll();
                    foreach ($words as $w): ?>
                    <tr>
                        <td class="p-4 font-bold"><?= $w['word'] ?></td>
                        <td class="p-4 text-slate-400"><?= $w['cat_name'] ?></td>
                        <td class="p-4"><span class="px-2 py-1 rounded text-xs bg-slate-700"><?= $w['difficulty'] ?></span></td>
                        <td class="p-4"><button class="text-indigo-400 text-sm">Edit</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
