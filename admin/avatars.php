<?php
// admin/avatars.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Fetch current avatars
$avatarsSetting = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'available_avatars'")->fetchColumn() ?: '🦊,🐱,🐸,🐼,🤖,👾,🚀,👽,👻,🌟,💎,🔥,⚡,🌈';
$currentAvatars = explode(',', $avatarsSetting);

?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avatar Vault | Amongly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_50%_0%,#1e1b4b_0%,#020617_80%)]">
    <?php include 'navbar.php' ?>

    <div class="max-w-4xl mx-auto px-8 pb-12 space-y-8 animate-fade-in relative">
        <div class="text-center space-y-2">
            <h1 class="text-4xl font-extrabold text-white tracking-tight">Avatar <span class="text-indigo-500">Vault</span></h1>
            <p class="text-slate-400 font-medium text-sm">Managing the visual identity of Amongly players.</p>
        </div>

        <!-- Bulk Toolbar -->
        <div id="bulk-toolbar" class="hidden sticky top-24 z-30 glass p-4 rounded-2xl border-rose-500/30 bg-rose-500/5 animate-slide-down flex items-center justify-between shadow-2xl">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50 cursor-pointer">
                    <span class="text-xs font-black text-slate-300 uppercase tracking-widest group-hover:text-white transition-colors">Select All</span>
                </label>
                <div class="h-6 w-px bg-slate-800"></div>
                <span id="selected-count" class="text-xs font-black text-rose-400 uppercase tracking-widest">3 Selected</span>
            </div>
            <button onclick="bulkDelete()" class="bg-rose-600 hover:bg-rose-500 text-white px-6 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all shadow-lg shadow-rose-600/20 active:scale-95">
                Delete Selected
            </button>
        </div>

        <div class="glass p-8 rounded-3xl space-y-8">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Current Avatar Sequence</label>
                    <button onclick="toggleSelectionMode()" id="selection-toggle-btn" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-widest transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Bulk Actions
                    </button>
                </div>
                
                <div id="avatar-container" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                    <?php foreach ($currentAvatars as $index => $avatar): ?>
                    <div class="avatar-card relative group aspect-square bg-slate-900 border border-slate-800 rounded-2xl flex items-center justify-center text-3xl shadow-lg transition-all hover:scale-105 hover:border-indigo-500/50 cursor-pointer" 
                         onclick="toggleCard(this)" data-avatar="<?= htmlspecialchars(trim($avatar)) ?>">
                        
                        <input type="checkbox" class="avatar-checkbox hidden absolute top-3 left-3 w-5 h-5 rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500/50 z-10 pointer-events-none">
                        
                        <span class="select-none"><?= trim($avatar) ?></span>
                        
                        <button onclick="event.stopPropagation(); removeAvatar('<?= htmlspecialchars(trim($avatar)) ?>')" class="individual-delete absolute -top-2 -right-2 w-6 h-6 bg-rose-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="h-px bg-slate-800/50"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Add New Identity (Emojis)</label>
                    <div class="flex gap-3">
                        <input type="text" id="new-avatar-input" placeholder="Paste emoji(s) here..." 
                            class="flex-1 bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 text-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <button onclick="addAvatars()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-4 rounded-2xl font-bold uppercase tracking-widest text-xs transition-all shadow-lg shadow-indigo-600/20 active:scale-95">
                            Add
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-600 italic">Pro-tip: You can paste multiple emojis separated by commas to add a batch.</p>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Danger Zone</label>
                    <button onclick="resetAvatars()" class="w-full bg-rose-600/10 hover:bg-rose-600 text-rose-500 hover:text-white border border-rose-600/20 px-8 py-4 rounded-2xl font-bold uppercase tracking-widest text-xs transition-all flex items-center justify-center gap-3 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset to Default Sequence
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em]">
            Amongly Administration Protocol • Visual Identity Matrix
        </p>
    </div>

    <script>
        const currentAvatars = <?= json_encode($currentAvatars) ?>;
        let isSelectionMode = false;

        function toggleSelectionMode() {
            isSelectionMode = !isSelectionMode;
            const btn = document.getElementById('selection-toggle-btn');
            const checkboxes = document.querySelectorAll('.avatar-checkbox');
            const deleteBtns = document.querySelectorAll('.individual-delete');
            const toolbar = document.getElementById('bulk-toolbar');

            if (isSelectionMode) {
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg> Exit Selection`;
                btn.classList.replace('text-indigo-400', 'text-rose-400');
                checkboxes.forEach(cb => cb.classList.remove('hidden'));
                deleteBtns.forEach(db => db.classList.add('!hidden'));
            } else {
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Bulk Actions`;
                btn.classList.replace('text-rose-400', 'text-indigo-400');
                checkboxes.forEach(cb => {
                    cb.checked = false;
                    cb.classList.add('hidden');
                });
                deleteBtns.forEach(db => db.classList.remove('!hidden'));
                toolbar.classList.add('hidden');
                document.querySelectorAll('.avatar-card').forEach(card => card.classList.remove('border-indigo-500', 'bg-indigo-500/10'));
                document.getElementById('select-all').checked = false;
            }
        }

        function toggleCard(card) {
            if (!isSelectionMode) return;
            const cb = card.querySelector('.avatar-checkbox');
            cb.checked = !cb.checked;
            updateCardStyle(card, cb.checked);
            updateBulkToolbar();
        }

        function updateCardStyle(card, isChecked) {
            if (isChecked) {
                card.classList.add('border-indigo-500', 'bg-indigo-500/10', 'scale-105');
                card.classList.remove('border-slate-800');
            } else {
                card.classList.remove('border-indigo-500', 'bg-indigo-500/10', 'scale-105');
                card.classList.add('border-slate-800');
            }
        }

        function updateBulkToolbar() {
            const selected = document.querySelectorAll('.avatar-checkbox:checked');
            const toolbar = document.getElementById('bulk-toolbar');
            const countEl = document.getElementById('selected-count');

            if (selected.length > 0) {
                toolbar.classList.remove('hidden');
                countEl.innerText = `${selected.length} Selected`;
            } else {
                toolbar.classList.add('hidden');
            }
        }

        document.getElementById('select-all').addEventListener('change', (e) => {
            const checked = e.target.checked;
            document.querySelectorAll('.avatar-card').forEach(card => {
                const cb = card.querySelector('.avatar-checkbox');
                cb.checked = checked;
                updateCardStyle(card, checked);
            });
            updateBulkToolbar();
        });

        async function bulkDelete() {
            const selected = Array.from(document.querySelectorAll('.avatar-card')).filter(card => card.querySelector('.avatar-checkbox').checked);
            if (selected.length === 0) return;

            if (!confirm(`Permanently wipe ${selected.length} identities from the vault?`)) return;

            const selectedAvatars = selected.map(card => card.dataset.avatar);
            const newList = currentAvatars.filter(a => !selectedAvatars.includes(a.trim()));
            await saveAvatars(newList);
        }

        async function saveAvatars(list) {
            const fd = new FormData();
            fd.append('key', 'available_avatars');
            fd.append('value', list.join(','));
            
            try {
                const res = await fetch('../api/admin_actions.php?action=update_setting', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Identity update failed.');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function removeAvatar(avatar) {
            if (!confirm(`Permanently remove ${avatar} from the available identity pool?`)) return;
            const newList = currentAvatars.filter(a => a.trim() !== avatar);
            saveAvatars(newList);
        }

        function addAvatars() {
            const input = document.getElementById('new-avatar-input').value;
            if (!input.trim()) return;
            
            const newOnes = input.split(/[,\s]+/).filter(a => a.trim().length > 0);
            const newList = [...currentAvatars, ...newOnes];
            saveAvatars([...new Set(newList)]); // Unique only
        }

        function resetAvatars() {
            const defaultSet = '🦊,🐱,🐸,🐼,🤖,👾,🚀,👽,👻,🌟,💎,🔥,⚡,🌈,🦄,🐉,🐙,🍦,🍕,🍔'.split(',');
            if (confirm('Revert the entire visual sequence to factory defaults?')) {
                saveAvatars(defaultSet);
            }
        }
    </script>
</body>
</html>
