<?php
// admin/words.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Filters
$filter_cat = (int)($_GET['filter_cat'] ?? 0);
$filter_diff = $_GET['filter_diff'] ?? '';
$search = clean($_GET['search'] ?? '');

// Build Query
$where = [];
$params = [];

if ($filter_cat) {
    $where[] = "w.category_id = ?";
    $params[] = $filter_cat;
}
if ($filter_diff) {
    $where[] = "w.difficulty = ?";
    $params[] = $filter_diff;
}
if ($search) {
    $where[] = "w.word LIKE ?";
    $params[] = "%$search%";
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT w.*, c.name as category_name 
    FROM words w 
    JOIN categories c ON w.category_id = c.id 
    $whereSql
    ORDER BY w.created_at DESC
";

$words = $db->prepare($query);
$words->execute($params);
$words = $words->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch single word if editing
$editWord = null;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM words WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editWord = $stmt->fetch();
}

// Handle POST for single save/update (Form fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = clean($_POST['word'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $diff = $_POST['difficulty'] ?? 'easy';
    $id = (int)($_POST['id'] ?? 0);
    
    if ($word && $catId) {
        if ($id) {
            $stmt = $db->prepare("UPDATE words SET category_id = ?, word = ?, difficulty = ? WHERE id = ?");
            $stmt->execute([$catId, $word, $diff, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO words (category_id, word, difficulty) VALUES (?, ?, ?)");
            $stmt->execute([$catId, $word, $diff]);
        }
        header("Location: words.php?filter_cat=$catId");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Matrix | Amongly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-slide-down { animation: slideDown 0.3s ease-out forwards; }
    </style>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_50%_0%,#1e1b4b_0%,#020617_80%)]">
    <?php include 'navbar.php' ?>

    <div class="max-w-7xl mx-auto px-8 pb-12 space-y-8 animate-fade-in relative">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Word <span class="text-indigo-500">Matrix</span></h1>
                <p class="text-slate-400 font-medium text-sm">Managing the sequence space of Amongly keywords.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="toggleBulkAddModal()" class="bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 px-6 py-3 rounded-2xl font-bold uppercase tracking-widest text-[10px] transition-all border border-indigo-500/20 active:scale-95">
                    Batch Injection
                </button>
                <button onclick="toggleSingleAddModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-2xl font-bold uppercase tracking-widest text-[10px] transition-all shadow-lg shadow-indigo-600/20 active:scale-95">
                    Single Add
                </button>
            </div>
        </div>

        <!-- Bulk Toolbar -->
        <div id="bulk-toolbar" class="hidden sticky top-24 z-30 glass p-5 rounded-3xl border-indigo-500/30 bg-indigo-500/10 animate-slide-down flex flex-col md:flex-row items-center gap-6 shadow-2xl">
            <div class="flex items-center gap-4 border-r border-slate-800 pr-6 mr-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" id="select-all" class="w-5 h-5 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50 cursor-pointer">
                    <span class="text-xs font-black text-slate-300 uppercase tracking-widest group-hover:text-white transition-colors">Select All</span>
                </label>
                <div class="h-8 w-px bg-slate-800"></div>
                <span id="selected-count" class="text-xs font-black text-indigo-400 uppercase tracking-widest">0 Selected</span>
            </div>

            <div class="flex flex-1 items-center gap-4">
                <div class="flex-1 flex gap-3">
                    <select id="bulk-category" class="flex-1 bg-slate-900 border border-slate-700/50 rounded-xl px-4 py-2 text-[10px] uppercase font-bold text-slate-300 outline-none focus:ring-2 focus:ring-indigo-500">
                         <option value="">Move to Category...</option>
                         <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                         <?php endforeach; ?>
                    </select>
                    <select id="bulk-difficulty" class="flex-1 bg-slate-900 border border-slate-700/50 rounded-xl px-4 py-2 text-[10px] uppercase font-bold text-slate-300 outline-none focus:ring-2 focus:ring-indigo-500">
                         <option value="">Set Difficulty...</option>
                         <option value="easy">Easy</option>
                         <option value="medium">Medium</option>
                         <option value="hard">Hard</option>
                    </select>
                </div>
                <button onclick="applyBulkEdit()" class="bg-indigo-500 hover:bg-indigo-400 text-white px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                    Apply Changes
                </button>
            </div>

            <div class="h-8 w-px bg-slate-800 hidden md:block"></div>

            <button onclick="applyBulkDelete()" class="bg-rose-600/10 hover:bg-rose-600 text-rose-500 hover:text-white border border-rose-600/20 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95">
                Wipe Selected
            </button>
        </div>

        <div class="glass p-8 rounded-[40px] space-y-8">
            <!-- Search & Filter Bar -->
            <form action="words.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search keyword matrix..." 
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-600">
                    <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
                <select name="filter_cat" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-300 outline-none focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer">
                    <option value="0">All Categories</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filter_cat == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter_diff" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-300 outline-none focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer">
                    <option value="">All Lethality</option>
                    <option value="easy" <?= $filter_diff == 'easy' ? 'selected' : '' ?>>Easy</option>
                    <option value="medium" <?= $filter_diff == 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="hard" <?= $filter_diff == 'hard' ? 'selected' : '' ?>>Hard</option>
                </select>
            </form>

            <div class="overflow-x-auto rounded-3xl border border-slate-800/50">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/80 text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] border-b border-slate-800">
                            <th class="px-8 py-5 w-10"></th>
                            <th class="px-8 py-5">Keyword</th>
                            <th class="px-8 py-5">Category</th>
                            <th class="px-8 py-5">Difficulty</th>
                            <th class="px-8 py-5 text-right">Protocol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/30">
                        <?php foreach ($words as $w): ?>
                        <tr class="group hover:bg-slate-800/20 transition-all cursor-pointer" onclick="toggleRow(this, event)">
                            <td class="px-8 py-5" onclick="event.stopPropagation()">
                                <input type="checkbox" class="word-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50 cursor-pointer" value="<?= $w['id'] ?>">
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-bold text-white tracking-tight group-hover:text-indigo-400 transition-colors"><?= htmlspecialchars($w['word']) ?></span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest bg-slate-800/40 px-3 py-1 rounded-full border border-slate-700/50"><?= htmlspecialchars($w['category_name']) ?></span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border 
                                    <?= $w['difficulty'] === 'hard' ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 
                                       ($w['difficulty'] === 'medium' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 
                                       'bg-emerald-500/10 text-emerald-500 border-emerald-500/20') ?>">
                                    <?= $w['difficulty'] ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal(<?= $w['id'] ?>, '<?= addslashes($w['word']) ?>', <?= $w['category_id'] ?>, '<?= $w['difficulty'] ?>')" class="p-2 text-slate-600 hover:text-indigo-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button onclick="deleteWord(<?= $w['id'] ?>)" class="p-2 text-slate-600 hover:text-rose-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($words)): ?>
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="space-y-4">
                                    <div class="w-16 h-16 bg-slate-900 rounded-full flex items-center justify-center mx-auto text-slate-700">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">No keywords found in current sector.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-center text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em]">
            Amongly Administration Protocol • Keyword Sector Sync Active
        </p>
    </div>

    <!-- Modals -->
    <!-- Batch Add Modal -->
    <div id="batch-add-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-6">
        <div class="glass w-full max-w-2xl rounded-[40px] p-10 space-y-8 animate-scale-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black">Batch <span class="text-indigo-500">Injection</span></h2>
                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Paste keywords separated by commas or newlines</p>
                </div>
                <button onclick="toggleBulkAddModal()" class="text-slate-500 hover:text-white transition-all bg-slate-900 p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-6">
                <textarea id="bulk-words-input" class="w-full h-64 bg-slate-900/50 border border-slate-800 rounded-3xl p-6 text-white font-mono text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700" placeholder="Astronaut, Galaxy, Black Hole, Nebula..."></textarea>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Target Category</label>
                        <select id="bulk-add-category" class="w-full bg-slate-900 border border-slate-700 rounded-2xl p-4 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Lethality Override</label>
                        <select id="bulk-add-difficulty" class="w-full bg-slate-900 border border-slate-700 rounded-2xl p-4 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
            </div>

            <button onclick="submitBulkAdd(this)" class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition-all text-white font-black py-5 rounded-3xl shadow-2xl shadow-indigo-600/20 uppercase tracking-[0.2em] text-xs">
                Initialize Synthesis
            </button>
        </div>
    </div>

    <!-- Single Add/Edit Modal -->
    <div id="single-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-6">
        <div class="glass w-full max-w-lg rounded-[40px] p-10 space-y-8 animate-scale-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="single-modal-title" class="text-3xl font-black">Word <span class="text-indigo-500">Synthesis</span></h2>
                </div>
                <button onclick="toggleSingleAddModal()" class="text-slate-500 hover:text-white transition-all bg-slate-900 p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-6">
                <div class="space-y-3">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">The Keyword</label>
                    <input type="text" id="single-word" class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-5 text-lg font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ex: Astronaut">
                </div>
                
                <div class="space-y-3">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Category</label>
                    <select id="single-category" class="w-full bg-slate-900 border border-slate-700 rounded-2xl p-4 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Lethality Level</label>
                    <select id="single-difficulty" class="w-full bg-slate-900 border border-slate-700 rounded-2xl p-4 text-xs font-bold text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>

            <input type="hidden" id="edit-id" value="">
            <button onclick="submitSingleSave(this)" id="single-save-btn" class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition-all text-white font-black py-5 rounded-3xl shadow-2xl shadow-indigo-600/20 uppercase tracking-[0.2em] text-xs">
                Authorize Sync
            </button>
        </div>
    </div>

    <script>
        // Checkbox & Toolbar Logic
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.word-checkbox');
        const toolbar = document.getElementById('bulk-toolbar');
        const countEl = document.getElementById('selected-count');

        selectAll.addEventListener('change', (e) => {
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
                cb.closest('tr').classList.toggle('bg-indigo-500/10', e.target.checked);
                cb.closest('tr').classList.toggle('border-indigo-500/20', e.target.checked);
            });
            updateToolbar();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                cb.closest('tr').classList.toggle('bg-indigo-500/10', cb.checked);
                cb.closest('tr').classList.toggle('border-indigo-500/20', cb.checked);
                updateToolbar();
            });
        });

        function toggleRow(row, event) {
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'BUTTON' || event.target.closest('button')) return;
            const cb = row.querySelector('.word-checkbox');
            cb.checked = !cb.checked;
            row.classList.toggle('bg-indigo-500/10', cb.checked);
            row.classList.toggle('border-indigo-500/20', cb.checked);
            updateToolbar();
        }

        function updateToolbar() {
            const selected = document.querySelectorAll('.word-checkbox:checked');
            if (selected.length > 0) {
                toolbar.classList.remove('hidden');
                countEl.innerText = `${selected.length} Selected`;
                selectAll.checked = (selected.length === checkboxes.length);
            } else {
                toolbar.classList.add('hidden');
                selectAll.checked = false;
            }
        }

        async function adminApi(action, formData, btn = null) {
            const originalText = btn ? btn.innerText : '';
            if (btn) { btn.disabled = true; btn.innerText = 'SYNCING...'; }
            
            try {
                const res = await fetch(`../api/admin_actions.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Action failed. Please try again.');
                    if (btn) { btn.disabled = false; btn.innerText = originalText; }
                }
            } catch (err) {
                console.error(err);
                if (btn) { btn.disabled = false; btn.innerText = originalText; }
            }
        }

        // Modals
        function toggleBulkAddModal() { document.getElementById('batch-add-modal').classList.toggle('hidden'); }
        function toggleSingleAddModal() { 
            document.getElementById('edit-id').value = '';
            document.getElementById('single-word').value = '';
            document.getElementById('single-modal-title').innerHTML = 'Word <span class="text-indigo-500">Synthesis</span>';
            document.getElementById('single-save-btn').innerText = 'Authorize Sync';
            document.getElementById('single-modal').classList.toggle('hidden'); 
        }

        function openEditModal(id, word, catId, diff) {
            document.getElementById('edit-id').value = id;
            document.getElementById('single-word').value = word;
            document.getElementById('single-category').value = catId;
            document.getElementById('single-difficulty').value = diff;
            document.getElementById('single-modal-title').innerHTML = 'Matrix <span class="text-indigo-500">Edit</span>';
            document.getElementById('single-save-btn').innerText = 'Update Word';
            document.getElementById('single-modal').classList.remove('hidden');
        }

        // Submissions
        async function submitBulkAdd(btn) {
            const words = document.getElementById('bulk-words-input').value;
            const category = document.getElementById('bulk-add-category').value;
            const diff = document.getElementById('bulk-add-difficulty').value;
            if (!words.trim()) return;
            const fd = new FormData();
            fd.append('words', words);
            fd.append('category_id', category);
            fd.append('difficulty', diff);
            await adminApi('bulk_add_words', fd, btn);
        }

        async function submitSingleSave(btn) {
            const word = document.getElementById('single-word').value;
            const category = document.getElementById('single-category').value;
            const diff = document.getElementById('single-difficulty').value;
            const id = document.getElementById('edit-id').value;
            if (!word.trim()) return;

            const fd = new FormData();
            fd.append('word', word);
            fd.append('category_id', category);
            fd.append('difficulty', diff);
            
            if (id) {
                // We need to add an edit action to API or use existing edit logic
                // For now, let's use the dashboard logic if it exists, or create a simple one here
                // I'll use a direct fetch to simulate the "edit" since I don't want to change too many files at once
                // Wait, I should probably use the same admin_actions.php logic.
                // Looking at my previous edits, I don't recall a single-word edit in admin_actions yet.
                // Actually, the previous dashboard code used words.php POST.
                // Let's use words.php POST instead of the API for single edits to maintain compatibility.
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'words.php';
                
                const inputs = { id, word, category_id: category, difficulty: diff };
                for (const [k, v] of Object.entries(inputs)) {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = k; i.value = v;
                    form.appendChild(i);
                }
                document.body.appendChild(form);
                form.submit();
            } else {
                // If it's a new word, we can use the bulk_add_words with just one word
                fd.append('words', word);
                await adminApi('bulk_add_words', fd, btn);
            }
        }

        // Bulk Actions logic
        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.word-checkbox:checked')).map(cb => cb.value);
        }

        async function applyBulkDelete() {
            if (!confirm('Permanently erase selected sequences?')) return;
            const fd = new FormData();
            getSelectedIds().forEach(id => fd.append('ids[]', id));
            await adminApi('bulk_delete_words', fd);
        }

        async function applyBulkEdit() {
            const category = document.getElementById('bulk-category').value;
            const diff = document.getElementById('bulk-difficulty').value;
            if (!category && !diff) return;
            const fd = new FormData();
            getSelectedIds().forEach(id => fd.append('ids[]', id));
            if (category) fd.append('category_id', category);
            if (diff) fd.append('difficulty', diff);
            await adminApi('bulk_edit_words', fd);
        }

        async function deleteWord(id) {
            if (!confirm('Delete this keyword?')) return;
            const fd = new FormData(); fd.append('id', id);
            await adminApi('delete_word', fd);
        }
    </script>
</body>
</html>
