<?php
// admin/categories.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Fetch categories with word counts
$categories = $db->query("
    SELECT c.*, COUNT(w.id) as word_count 
    FROM categories c 
    LEFT JOIN words w ON c.id = w.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Matrix | Amongly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_50%_0%,#1e1b4b_0%,#020617_80%)]">
    <?php include 'navbar.php' ?>

    <div class="max-w-5xl mx-auto px-8 pb-12 space-y-8 animate-fade-in">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Category <span class="text-indigo-500">Matrix</span></h1>
                <p class="text-slate-400 font-medium text-sm">Organize and restructure the game's core themes.</p>
            </div>
            
            <div class="flex flex-wrap
             items-center gap-3">
                <div class="relative">
                    <input type="text" id="new-cat-name" placeholder="New category name..." 
                        class="bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all w-64">
                </div>
                <button onclick="addCategory(this)" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white px-8 py-4 rounded-2xl font-bold uppercase tracking-widest text-xs transition-all shadow-lg shadow-indigo-600/20 active:scale-95">
                    Add Category
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $cat): ?>
            <div class="glass p-6 rounded-3xl space-y-6 group hover:border-indigo-500/30 transition-all">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="editCategory(this, <?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')" class="p-2 text-slate-500 hover:text-indigo-400 transition-colors disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="deleteCategory(this, <?= $cat['id'] ?>)" class="p-2 text-slate-500 hover:text-rose-400 transition-colors disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-black tracking-tight text-white group-hover:text-indigo-400 transition-colors"><?= htmlspecialchars($cat['name']) ?></h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase border border-emerald-500/20">
                            <?= $cat['word_count'] ?> Words
                        </span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest ml-1">Sequence ID #<?= $cat['id'] ?></span>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="dashboard.php?filter_cat=<?= $cat['id'] ?>" class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 hover:text-white transition-colors flex items-center gap-2">
                        View Word List
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em] pt-12 pb-6">
            Amongly Administration Protocol • Category Matrix Active
        </p>
    </div>

    <script>
        async function adminApi(action, formData, btn = null) {
            const originalText = btn ? btn.innerText : '';
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'PROMPT...';
            }
            
            try {
                const res = await fetch(`../api/admin_actions.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Critical Logic Error. Check console.');
                    if (btn) { btn.disabled = false; btn.innerText = originalText; }
                    return;
                }
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'System error');
                    if (btn) { btn.disabled = false; btn.innerText = originalText; }
                }
            } catch (err) {
                console.error(err);
                alert('Fatal Connection Error.');
                if (btn) { btn.disabled = false; btn.innerText = originalText; }
            }
        }

        function addCategory(btn) {
            const input = document.getElementById('new-cat-name');
            const name = input.value;
            if (!name) return;
            const fd = new FormData(); fd.append('name', name);
            adminApi('add_category', fd, btn);
        }

        function deleteCategory(btn, id) {
            if (!confirm('Permanently erase this category? (Only works if empty)')) return;
            const fd = new FormData(); fd.append('id', id);
            adminApi('delete_category', fd, btn);
        }

        function editCategory(btn, id, currentName) {
            const newName = prompt('Rewrite category identifier:', currentName);
            if (!newName || newName === currentName) return;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('name', newName);
            adminApi('edit_category', fd, btn);
        }
    </script>
</body>
</html>
