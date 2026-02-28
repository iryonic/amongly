<?php
// views/create.php
$roomModel = new Room();
$categories = $roomModel->getCategories();
?>
<div class="flex flex-col space-y-12 pt-8">
    <header class="flex items-center gap-6">
        <button onclick="navigate('index.php')" class="h-14 w-14 app-card border-white/5 bg-white/5 flex items-center justify-center text-white/40 active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <h2 class="heading-app text-4xl font-black text-white italic">Create Room.</h2>
    </header>

    <form id="createRoomForm" class="space-y-12">
        <div class="space-y-8">
            <div class="space-y-4">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 ml-1">Word Category</span>
                <div class="relative">
                    <select name="category_id" class="app-input h-20 appearance-none bg-white/[0.04]">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= strtoupper($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-white/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-4">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/20 ml-1">Difficulty</span>
                    <select name="difficulty" class="app-input h-16 bg-white/[0.04] text-sm">
                        <option value="easy">EASY</option>
                        <option value="medium" selected>NORMAL</option>
                        <option value="hard">HARD</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/20 ml-1">Max Players</span>
                    <input type="number" name="max_players" value="12" min="3" max="50" class="app-input h-16 bg-white/[0.04] text-center italic">
                </div>
            </div>
        </div>

        <button type="submit" id="submitBtn" onclick="haptic('heavy')" class="app-btn btn-primary text-xl">
            Create Room
        </button>
    </form>
</div>

<script>
document.getElementById('createRoomForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="h-6 w-6 border-4 border-black/20 border-t-black rounded-full animate-spin"></div>';
    
    const formData = new FormData(e.target);
    const data = await apiCall('room_actions.php?action=create', 'POST', formData);
    if (data.success) {
        navigate('index.php?view=room');
    } else {
        alert(data.error);
        btn.disabled = false;
        btn.innerText = 'Create Room';
    }
});
</script>
