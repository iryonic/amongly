<?php
// views/setup_nickname.php
$hasSession = isset($_SESSION['nickname']);
$currentName = $_SESSION['nickname'] ?? '';
$currentAvatar = $_SESSION['avatar'] ?? '🦊';
?>
<div class="flex flex-col space-y-12 animate-fade-in relative">
    <!-- Back Navigation -->
    <div class="absolute -top-6 left-0">
        <button onclick="navigate('index.php')" class="flex items-center gap-2 text-neutral-500 hover:text-white transition-colors group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            <span class="text-xs font-bold uppercase tracking-widest">Back</span>
        </button>
    </div>

    <div class="space-y-4 pt-10">
        <h1 class="text-4xl font-bold text-white"><?= $hasSession ? 'My Profile' : 'Setup Profile' ?></h1>
        <p class="text-neutral-400 max-w-[300px]"><?= $hasSession ? 'Update your appearance and nickname for upcoming games.' : 'Choose a unique avatar and nickname to identify yourself.' ?></p>
    </div>

    <form id="nicknameForm" class="space-y-10">
        <!-- Avatar Selector -->
        <div class="space-y-4">
            <label class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">Select Avatar</label>
            <div class="flex gap-4 overflow-x-auto pb-4 no-scrollbar -mx-6 px-6" id="avatar-grid">
                <?php 
                $avatars = ['🦊', '🐱', '🐸', '🐼', '🤖', '👾', '🚀', '👽', '👻', '🌟', '💎', '🔥', '⚡', '🌈'];
                foreach ($avatars as $i => $a): 
                    $isActive = ($a === $currentAvatar);
                ?>
                    <div class="avatar-node <?= $isActive ? 'active' : '' ?>" data-avatar="<?= $a ?>"><?= $a ?></div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="avatar" id="avatarInput" value="<?= htmlspecialchars($currentAvatar) ?>">
        </div>

        <!-- Nickname Input -->
        <div class="space-y-4">
            <label class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">Your Nickname</label>
            <input type="text" name="nickname" id="nicknameInput" required maxlength="15" 
                value="<?= htmlspecialchars($currentName) ?>"
                placeholder="Enter your name..." 
                class="neo-input h-14 text-lg font-semibold shadow-sm">
        </div>

        <!-- Submit -->
        <button type="submit" id="submitBtn" class="neo-btn neo-btn-primary w-full h-16 text-lg tracking-tight">
            <?= $hasSession ? 'Save Changes' : "Continue to Game" ?>
        </button>
    </form>
</div>

<script>
// Avatar selection logic
document.getElementById('avatar-grid').addEventListener('click', (e) => {
    const node = e.target.closest('.avatar-node');
    if (!node) return;
    document.querySelectorAll('.avatar-node').forEach(n => n.classList.remove('active'));
    node.classList.add('active');
    document.getElementById('avatarInput').value = node.dataset.avatar;
});

document.getElementById('nicknameForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Saving...';

    const formData = new FormData(e.target);
    const data = await apiCall('identity_actions.php?action=set_nickname', 'POST', formData);
    
    if (data.success) {
        showToast('Profile updated.', 'success');
        sessionStorage.setItem('amongly_nickname', formData.get('nickname'));
        sessionStorage.setItem('amongly_avatar', formData.get('avatar'));
        navigate('index.php');
    } else {
        showToast(data.error);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
