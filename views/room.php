<?php // views/room.php ?>
<div id="game-ui" class="flex flex-col h-full w-full view-transition relative animate-fade-in">

    <!-- Minimal HUD -->
    <header class="glass-header safe-top">
        <div class="max-nexus flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Room Code</span>
                    <button onclick="copyRoomCode()" class="text-xl font-bold text-white hover:text-indigo-400 transition-colors tabular-nums tracking-tight" id="ui-room-code">------</button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div id="phase-timer" class="hidden px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 flex items-center gap-2">
                    <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                    <span id="timer-display" class="text-xs font-bold text-indigo-400 tabular-nums">--s</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-neutral-900 border border-neutral-800">
                    <span id="ui-player-count" class="text-xs font-bold text-white">0</span>
                    <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <button onclick="navigate('?view=logout')" class="p-2 ml-1 rounded-full hover:bg-red-500/10 hover:text-red-400 text-neutral-500 transition-all active:scale-90" title="Leave Game">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </div>
        </div>
    </header>

    <div class="h-24"></div>

    <main id="room-content" class="flex-1 overflow-y-auto no-scrollbar max-nexus space-y-8 pb-32">
        
        <!-- PHASE: WAITING -->
        <div id="phase-waiting" class="game-phase hidden flex-col space-y-8">
            <div class="space-y-2">
                <h3 class="text-3xl font-bold text-white">Lobby</h3>
                <p class="text-neutral-500 text-sm">Wait for others. Host starts the game.</p>
            </div>
            <div class="space-y-3" id="player-list"></div>
            
            <div id="host-controls" class="hidden fixed bottom-10 left-0 right-0 px-6 pointer-events-none">
                <div class="max-nexus mx-auto pointer-events-auto">
                    <button onclick="startGame()" class="neo-btn neo-btn-primary w-full h-16 text-lg shadow-2xl shadow-indigo-500/10">
                        Start Game
                    </button>
                </div>
            </div>
        </div>

        <!-- PHASE: IDENTITY -->
        <div id="phase-word_reveal" class="game-phase hidden flex-col items-center justify-center min-h-[50vh] space-y-8">
            <div class="text-center space-y-2">
                <span class="text-xs font-bold text-indigo-500 uppercase tracking-widest">Your Word</span>
                <div id="ui-role-card" class="w-full"></div>
                <p class="text-neutral-500 text-xs pt-4">Keep it secret.</p>
            </div>
        </div>

        <!-- PHASE: CLUE -->
        <div id="phase-clue" class="game-phase hidden flex-col space-y-8">
            <div class="space-y-2">
                <h3 class="text-3xl font-bold text-white" id="clue-tag">Your Clue</h3>
                <p class="text-neutral-500 text-sm">Type one word to describe your word.</p>
            </div>

            <div id="clue-feed" class="space-y-3"></div>

            <div class="fixed bottom-10 left-0 right-0 px-6 pointer-events-none">
                <div class="max-nexus mx-auto pointer-events-auto flex flex-col gap-3">
                    <div id="clue-form" class="space-y-3">
                        <input type="text" id="clue-input" maxlength="60" placeholder="Type clue..." 
                            class="neo-input h-14 text-center text-lg font-medium shadow-2xl">
                        <button onclick="submitClue()" class="neo-btn neo-btn-primary w-full h-14">
                            Send
                        </button>
                    </div>
                </div>
            </div>

            <div id="imposter-guess-section" style="display:none" class="neo-card p-6 bg-red-500/5 border-red-500/10 space-y-4">
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-red-400 uppercase tracking-wider">Imposter Guess</h4>
                    <p class="text-xs text-neutral-500">If you guess the secret word correctly, you win now.</p>
                </div>
                <input type="text" id="imposter-guess-input" placeholder="Guess the word..." class="neo-input h-12 bg-neutral-900/50">
                <button onclick="submitImposterGuess()" class="neo-btn h-12 bg-red-500 text-white w-full text-sm">Guess</button>
                <div id="imposter-guess-feedback" class="text-center text-xs pt-2"></div>
            </div>
        </div>

        <!-- PHASE: VOTING -->
        <div id="phase-voting" class="game-phase hidden flex-col space-y-8">
            <div class="space-y-2">
                <h3 class="text-3xl font-bold text-white">Voting</h3>
                <p class="text-neutral-500 text-sm">Vote for the person you think is the imposter.</p>
            </div>

            <div id="vote-counter" class="grid grid-cols-2 gap-3">
                <div class="neo-card p-4 text-center">
                    <span class="text-[10px] font-bold text-neutral-500 uppercase">Eliminate</span>
                    <div id="eliminate-count" class="text-2xl font-bold text-white">0</div>
                </div>
                <div class="neo-card p-4 text-center">
                    <span class="text-[10px] font-bold text-neutral-500 uppercase">Skip</span>
                    <div id="skip-count" class="text-2xl font-bold text-white">0</div>
                </div>
            </div>

            <div id="vote-buttons" class="space-y-3 pb-24"></div>

            <div class="fixed bottom-10 left-0 right-0 px-6 pointer-events-none">
                <div class="max-nexus mx-auto pointer-events-auto">
                    <button id="skip-btn-wrap" onclick="submitSkipVote()" style="display:none" class="neo-btn neo-btn-secondary w-full h-14 text-neutral-400">
                        Skip
                    </button>
                </div>
            </div>
        </div>

        <!-- PHASE: REVEAL -->
        <div id="phase-reveal" class="game-phase hidden flex-col items-center justify-center min-h-[60vh] text-center space-y-8 animate-fade-in">
            <div id="reveal-content" class="w-full"></div>
        </div>
    </main>
</div>

