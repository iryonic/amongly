<?php
// views/join.php
?>
<div class="flex flex-col space-y-12 pt-8">
    <header class="flex items-center gap-6">
        <button onclick="navigate('index.php')" class="h-14 w-14 app-card border-white/5 bg-white/5 flex items-center justify-center text-white/40 active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <h2 class="heading-app text-4xl font-black text-white italic">Join Room.</h2>
    </header>

    <div class="space-y-12">
        <div class="space-y-4">
            <h3 class="text-white/40 font-medium text-lg">Enter the 6-letter room code to join a game.</h3>
        </div>

        <form id="joinRoomForm" class="space-y-12">
            <div class="space-y-4">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 ml-1">Room Code</span>
                <input type="text" name="room_code" id="join-code" required maxlength="6" 
                    placeholder="ABC123" 
                    class="app-input h-24 text-center text-6xl font-black tracking-[0.1em] placeholder:text-white/5 tabular-nums uppercase">
            </div>

            <button type="submit" id="submitBtn" onclick="haptic('medium')" class="app-btn btn-primary text-xl mt-8">
                Join Game
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('join-code').addEventListener('input', (e) => {
    e.target.value = e.target.value.toUpperCase();
});

document.getElementById('joinRoomForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="h-6 w-6 border-4 border-black/20 border-t-black rounded-full animate-spin"></div>';

    const formData = new FormData(e.target);
    const data = await apiCall('room_actions.php?action=join', 'POST', formData);
    if (data.success) {
        navigate('index.php?view=room');
    } else {
        alert(data.error);
        btn.disabled = false;
        btn.innerText = 'Join Game';
    }
});
</script>
