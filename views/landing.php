<?php
// views/landing.php
$roomModel = new Room();
$categories = $roomModel->getCategories();
?>
<div class="flex flex-col space-y-12 animate-fade-in">
    <!-- Header/Hero -->
    <div class="space-y-4">
        <h1 class="text-5xl heading-premium text-white">Amongly</h1>
        <p class="text-lg text-neutral-400 max-w-[320px]">A social deception game designed for close-knit circles.</p>
    </div>

    <!-- Quick Interactions -->
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4">
            <button onclick="openCreateModal()" class="neo-btn neo-btn-primary h-14 text-base">
                Create New Game
            </button>
            <button onclick="openJoinModal()" class="neo-btn neo-btn-secondary h-14 text-base">
                Join with Code
            </button>
        </div>
    </div>

    <!-- Features/Secondary -->
    <div class="grid grid-cols-2 gap-4">
        <div onclick="openRulesModal()" class="neo-card p-6 flex flex-col justify-between h-32 cursor-pointer hover:bg-neutral-900 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <span class="text-sm font-medium text-white">How to Play</span>
        </div>
        <div onclick="navigate('?view=setup_nickname')" class="neo-card p-6 flex flex-col justify-between h-32 cursor-pointer hover:bg-neutral-900 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center text-pink-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <span class="text-sm font-medium text-white">My Profile</span>
        </div>
    </div>
</div>

<!-- Modal: Create -->
<div id="createModal" class="neo-modal" onclick="if(event.target == this) closeCreateModal()">
    <div class="neo-modal-content space-y-8">
        <div class="sheet-handle"></div>
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-white">Game Settings</h2>
            <p class="text-neutral-500 text-sm">Configure your private room parameters.</p>
        </div>
        
        <form id="createRoomForm" class="space-y-6">
            <div class="space-y-3">
                <label class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Word Pack</label>
                <div class="grid grid-cols-2 gap-2" id="category-chips">
                    <?php foreach ($categories as $i => $cat): ?>
                        <div class="neo-chip <?= $i === 0 ? 'active' : '' ?>" data-value="<?= $cat['id'] ?>"><?= $cat['name'] ?></div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="category_id" id="categoryInput" value="<?= $categories[0]['id'] ?? '' ?>">
            </div>

            <div class="space-y-3">
                <label class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Difficulty</label>
                <div class="grid grid-cols-3 gap-2" id="difficulty-chips">
                    <div class="neo-chip active" data-value="easy">Easy</div>
                    <div class="neo-chip" data-value="medium">Normal</div>
                    <div class="neo-chip" data-value="hard">Hard</div>
                </div>
                <input type="hidden" name="difficulty" id="difficultyInput" value="easy">
            </div>

            <button type="submit" class="neo-btn neo-btn-primary w-full py-4">Create Game</button>
        </form>
    </div>
</div>

<!-- Modal: Join -->
<div id="joinModal" class="neo-modal" onclick="if(event.target == this) closeJoinModal()">
    <div class="neo-modal-content space-y-8">
        <div class="sheet-handle"></div>
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-white">Join Room</h2>
            <p class="text-neutral-500 text-sm">Enter the code provided by the host.</p>
        </div>
        
        <form id="joinRoomForm" class="space-y-6">
            <input type="text" name="room_code" required maxlength="6" 
                placeholder="Enter 6-digit code" 
                class="neo-input h-16 text-center text-2xl font-bold tracking-widest placeholder:text-neutral-700">
            <button type="submit" class="neo-btn neo-btn-primary w-full py-4">Join Room</button>
        </form>
    </div>
</div>

<!-- Modal: Rules -->
<div id="rulesModal" class="neo-modal" onclick="if(event.target == this) closeRulesModal()">
    <div class="neo-modal-content space-y-6 max-h-[85vh] overflow-y-auto no-scrollbar">
        <div class="sheet-handle"></div>
        <h2 class="text-2xl font-bold text-white">Quick Guide</h2>
        <div class="space-y-6 text-neutral-400 text-sm leading-relaxed">
            <section class="space-y-2">
                <h3 class="text-white font-semibold">The Core Concept</h3>
                <p>Everyone gets a Secret Word except for the Imposter. They get a slightly different word or no word at all.</p>
            </section>
            <section class="space-y-2 border-l-2 border-neutral-800 pl-4">
                <h3 class="text-white font-semibold">1. Describe</h3>
                <p>Submit a single word to prove you know the secret without giving it away to the Imposter.</p>
            </section>
            <section class="space-y-2 border-l-2 border-neutral-800 pl-4">
                <h3 class="text-white font-semibold">2. Vote</h3>
                <p>Discuss the clues and vote for the most suspicious person. If the Imposter is eliminated, the Crew wins.</p>
            </section>
        </div>
        <button onclick="closeRulesModal()" class="neo-btn neo-btn-secondary w-full">Got it</button>
    </div>
</div>

<script>
function openCreateModal() { document.getElementById('createModal').classList.add('active'); }
function closeCreateModal() { document.getElementById('createModal').classList.remove('active'); }
function openJoinModal() { document.getElementById('joinModal').classList.add('active'); }
function closeJoinModal() { document.getElementById('joinModal').classList.remove('active'); }
function openRulesModal() { document.getElementById('rulesModal').classList.add('active'); }
function closeRulesModal() { document.getElementById('rulesModal').classList.remove('active'); }

function initChips(containerId, inputId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.addEventListener('click', (e) => {
        const chip = e.target.closest('.neo-chip');
        if (!chip) return;
        container.querySelectorAll('.neo-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        document.getElementById(inputId).value = chip.dataset.value;
    });
}
initChips('category-chips', 'categoryInput');
initChips('difficulty-chips', 'difficultyInput');

document.getElementById('joinRoomForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = await apiCall('room_actions.php?action=join', 'POST', new FormData(e.target));
    if (data.success) {
        showToast('Joined mission.', 'success');
        navigate('?view=room');
    } else showToast(data.error);
});

document.getElementById('createRoomForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = await apiCall('room_actions.php?action=create', 'POST', new FormData(e.target));
    if (data.success) {
        showToast('Mission created.', 'success');
        navigate('?view=room');
    } else showToast(data.error);
});
</script>
