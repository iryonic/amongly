<?php
// admin/dashboard.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Stats
$roomCount = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$activeRoomsCount = $db->query("SELECT COUNT(*) FROM rooms WHERE status NOT IN ('finished', 'waiting')")->fetchColumn();
$totalWords = $db->query("SELECT COUNT(*) FROM words")->fetchColumn();
$totalPlayers = $db->query("SELECT COUNT(*) FROM players")->fetchColumn();

// Recent Game Activity
$recentRooms = $db->query("
    SELECT r.*, c.name as category_name 
    FROM rooms r 
    JOIN categories c ON r.category_id = c.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
")->fetchAll();

// Categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Global Settings
$avatars = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'available_avatars'")->fetchColumn() ?: '🦊,🐱,🐸,🐼,🤖,👾,🚀,👽,👻,🌟,💎,🔥,⚡,🌈';

// Word List
$words = $db->query("
    SELECT w.*, c.name as category_name 
    FROM words w 
    JOIN categories c ON w.category_id = c.id 
    ORDER BY w.created_at DESC 
    LIMIT 50
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amongly • Mission Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        ::-webkit-scrollbar{
            display: none;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_50%_0%,#1e1b4b_0%,#020617_80%)]">
    <?php include 'navbar.php' ?>

    <div class="max-w-7xl mx-auto px-8 pb-12 space-y-12 animate-fade-in">

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="glass p-6 rounded-3xl space-y-4">
                <div class="w-10 h-10 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Rooms</span>
                    <p class="text-4xl font-black"><?= $roomCount ?></p>
                </div>
            </div>
            <div class="glass p-6 rounded-3xl space-y-4">
                <div class="w-10 h-10 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active Games</span>
                    <p class="text-4xl font-black text-emerald-400"><?= $activeRoomsCount ?></p>
                </div>
            </div>
            <div class="glass p-6 rounded-3xl space-y-4">
                <div class="w-10 h-10 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Unique Words</span>
                    <p class="text-4xl font-black text-purple-400"><?= $totalWords ?></p>
                </div>
            </div>
            <div class="glass p-6 rounded-3xl space-y-4">
                <div class="w-10 h-10 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Engaged Players</span>
                    <p class="text-4xl font-black text-amber-400"><?= $totalPlayers ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Word Matrix -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Word Matrix
                    </h3>
                    <div class="flex items-center gap-3">
                        <button onclick="toggleBulkModal()" class="bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 px-4 py-2 rounded-xl text-xs font-bold transition-all border border-indigo-500/20 uppercase tracking-widest">
                            Bulk Create
                        </button>
                        <a href="words.php" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/20 uppercase tracking-widest">
                            Add Word
                        </a>
                    </div>
                </div>

                <!-- Bulk Toolbar (Hidden by default) -->
                <div id="bulk-toolbar" class="hidden glass p-4 rounded-2xl border-indigo-500/30 bg-indigo-500/5 animate-slide-up flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span id="selected-count" class="text-xs font-black text-indigo-400 uppercase tracking-widest ml-2">3 Selected</span>
                        <div class="h-6 w-px bg-slate-800"></div>
                        <select id="bulk-category" class="bg-slate-900 border border-slate-700/50 rounded-lg px-2 py-1 text-[10px] uppercase font-bold text-slate-300 outline-none focus:ring-1 focus:ring-indigo-500">
                             <option value="">Move to Category</option>
                             <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                             <?php endforeach; ?>
                        </select>
                        <select id="bulk-difficulty" class="bg-slate-900 border border-slate-700/50 rounded-lg px-2 py-1 text-[10px] uppercase font-bold text-slate-300 outline-none focus:ring-1 focus:ring-indigo-500">
                             <option value="">Set Difficulty</option>
                             <option value="easy">Easy</option>
                             <option value="medium">Medium</option>
                             <option value="hard">Hard</option>
                        </select>
                        <button onclick="applyBulkEdit()" class="text-[10px] font-black uppercase text-white bg-indigo-600 px-4 py-1.5 rounded-lg hover:bg-indigo-500 transition-all">Apply</button>
                    </div>
                    <button onclick="applyBulkDelete()" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all border border-rose-500/20">
                        Delete Selected
                    </button>
                </div>
                
                <div class="glass rounded-3xl h-[500px] overflow-scroll">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900/50 text-slate-400 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50">
                                </th>
                                <th class="px-6 py-4 tracking-widest">Keyword</th>
                                <th class="px-6 py-4 tracking-widest">Category</th>
                                <th class="px-6 py-4 tracking-widest">Difficulty</th>
                                <th class="px-6 py-4 text-right tracking-widest">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            <?php foreach ($words as $w): ?>
                            <tr class="hover:bg-slate-800/20 transition-all group" id="word-row-<?= $w['id'] ?>">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="word-select w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50" value="<?= $w['id'] ?>">
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-200"><?= $w['word'] ?></td>
                                <td class="px-6 py-4 text-slate-500 font-medium"><?= $w['category_name'] ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase border 
                                        <?= $w['difficulty'] === 'hard' ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 
                                           ($w['difficulty'] === 'medium' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 
                                           'bg-emerald-500/10 text-emerald-500 border-emerald-500/20') ?>">
                                        <?= $w['difficulty'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 flex items-center justify-end gap-3 text-slate-600">
                                    <a href="words.php?id=<?= $w['id'] ?>" class="hover:text-indigo-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button onclick="deleteWord(<?= $w['id'] ?>)" class="hover:text-rose-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Side Panels -->
            <div class="space-y-12">
                <!-- Categories -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Categories
                    </h3>
                    <div class="glass p-6 rounded-3xl space-y-4">
                        <div class="flex gap-2">
                            <input type="text" id="new-cat-name" placeholder="Category Name" class="flex-1 bg-slate-900/50 border border-slate-700/50 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none">
                            <button onclick="addCategory()" class="bg-indigo-600 px-3 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest">+</button>
                        </div>
                        <div class="space-y-2 max-h-[200px] overflow-y-auto no-scrollbar">
                            <?php foreach ($categories as $cat): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/30 group">
                                <span class="text-xs font-bold text-slate-300" id="cat-name-<?= $cat['id'] ?>"><?= $cat['name'] ?></span>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')" class="text-slate-500 hover:text-indigo-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="text-slate-500 hover:text-rose-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Global Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Global Config
                    </h3>
                    <div class="glass p-6 rounded-3xl space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Available Avatars (Emojis)</label>
                            <input type="text" id="avatar-list" value="<?= htmlspecialchars($avatars) ?>" 
                                class="w-full bg-slate-900 border border-slate-700/50 rounded-xl px-4 py-3 text-sm focus:ring-1 focus:ring-indigo-500 outline-none">
                            <p class="text-[9px] text-slate-600 font-medium italic">Separate emojis with commas. These appear in the player profile setup.</p>
                        </div>
                        <button onclick="updateAvatars()" class="w-full bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] transition-all text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-600/10 uppercase tracking-widest text-[10px]">
                            Update Core Config
                        </button>
                    </div>
                </div>

                <!-- Recent Rooms -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Recent Game Activity
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($recentRooms as $rr): ?>
                        <div class="glass p-5 rounded-2xl flex items-center justify-between group">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-white tracking-widest uppercase"><?= $rr['room_code'] ?></span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[9px] font-bold text-slate-400 border border-slate-700 uppercase"><?= $rr['status'] ?></span>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-tight"><?= $rr['category_name'] ?> • <?= $rr['difficulty'] ?></p>
                            </div>
                            <button onclick="deleteRoom(<?= $rr['id'] ?>)" class="text-slate-600 hover:text-rose-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em] pt-12 pb-6">
            Amongly Administration Protocol • v1.0.4-LTS
        </p>
    </div>

    <!-- Bulk Create Modal -->
    <div id="bulk-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-6">
        <div class="glass w-full max-w-2xl rounded-3xl p-8 space-y-6 animate-scale-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black">Bulk Protocol</h2>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-1">Separate by commas or newlines</p>
                </div>
                <button onclick="toggleBulkModal()" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <textarea id="bulk-words-input" class="w-full h-64 bg-slate-900 border border-slate-800 rounded-2xl p-4 text-white font-mono text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700" placeholder="Astronaut, Galaxy, Black Hole, Nebula..."></textarea>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Category</label>
                        <select id="bulk-add-category" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Difficulty</label>
                        <select id="bulk-add-difficulty" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button onclick="submitBulkAdd()" class="flex-1 bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition-all text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-600/20 uppercase tracking-widest text-xs">
                    Synthesize Batch
                </button>
            </div>
        </div>
    </div>

    <script>
        // Checkbox Logic
        const selectAll = document.getElementById('select-all');
        const wordCheckboxes = document.querySelectorAll('.word-select');
        const bulkToolbar = document.getElementById('bulk-toolbar');
        const selectedCountEl = document.getElementById('selected-count');

        function updateToolbar() {
            const selected = document.querySelectorAll('.word-select:checked');
            if (selected.length > 0) {
                bulkToolbar.classList.remove('hidden');
                selectedCountEl.innerText = `${selected.length} Selected`;
            } else {
                bulkToolbar.classList.add('hidden');
            }
        }

        selectAll.addEventListener('change', (e) => {
            wordCheckboxes.forEach(cb => cb.checked = e.target.checked);
            updateToolbar();
        });

        wordCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateToolbar);
        });

        // Bulk Actions
        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.word-select:checked')).map(cb => cb.value);
        }

        async function adminApi(action, formData) {
            try {
                const res = await fetch(`../api/admin_actions.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'System error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function toggleBulkModal() {
            document.getElementById('bulk-modal').classList.toggle('hidden');
        }

        async function updateAvatars() {
            const list = document.getElementById('avatar-list').value;
            const fd = new FormData();
            fd.append('key', 'available_avatars');
            fd.append('value', list);
            await adminApi('update_setting', fd);
        }

        async function submitBulkAdd() {
            const words = document.getElementById('bulk-words-input').value;
            const category = document.getElementById('bulk-add-category').value;
            const diff = document.getElementById('bulk-add-difficulty').value;
            
            if (!words.trim()) return;

            const fd = new FormData();
            fd.append('words', words);
            fd.append('category_id', category);
            fd.append('difficulty', diff);
            
            await adminApi('bulk_add_words', fd);
        }

        async function applyBulkDelete() {
            if (!confirm('Permanently erase selected words from the matrix?')) return;
            const ids = getSelectedIds();
            const fd = new FormData();
            ids.forEach(id => fd.append('ids[]', id));
            await adminApi('bulk_delete_words', fd);
        }

        async function applyBulkEdit() {
            const ids = getSelectedIds();
            const category = document.getElementById('bulk-category').value;
            const diff = document.getElementById('bulk-difficulty').value;
            
            if (!category && !diff) return;

            const fd = new FormData();
            ids.forEach(id => fd.append('ids[]', id));
            if (category) fd.append('category_id', category);
            if (diff) fd.append('difficulty', diff);

            await adminApi('bulk_edit_words', fd);
        }

        function deleteWord(id) {
            if (!confirm('Are you sure you want to delete this word?')) return;
            const fd = new FormData(); fd.append('id', id);
            adminApi('delete_word', fd);
        }

        function deleteRoom(id) {
            if (!confirm('Are you sure you want to terminate this room?')) return;
            const fd = new FormData(); fd.append('id', id);
            adminApi('delete_room', fd);
        }

        function addCategory() {
            const name = document.getElementById('new-cat-name').value;
            if (!name) return;
            const fd = new FormData(); fd.append('name', name);
            adminApi('add_category', fd);
        }

        function deleteCategory(id) {
            if (!confirm('Delete this category? Only succeeds if no words are assigned.')) return;
            const fd = new FormData(); fd.append('id', id);
            adminApi('delete_category', fd);
        }

        function editCategory(id, currentName) {
            const newName = prompt('Enter new name for category:', currentName);
            if (!newName || newName === currentName) return;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('name', newName);
            adminApi('edit_category', fd);
        }
    </script>
</body>
</html>
