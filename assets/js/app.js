// assets/js/app.js - The Amongly Engine (Optimized)

let gameState = {};
let pollTimer = null;
let voteBtnHash = '';
let lastCluePhaseStart = 0;
let revealShown = false;

// Timer State
let timerInterval = null;
let currentTimerPhaseKey = null;

async function pollState() {
    try {
        const res = await fetch('api/state.php', {
            headers: { 'X-Node-Token': (typeof getNodeToken === 'function') ? getNodeToken() : '' }
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = 'index.php?view=logout'; return; }
            return;
        }
        const state = await res.json();
        updateUI(state);
    } catch (err) {
        console.error('Connection error:', err);
    }
}

function updateUI(state) {
    gameState = state;
    const myNickname = sessionStorage.getItem('amongly_nickname');

    // HUD Updates
    const roomCodeEl = document.getElementById('ui-room-code');
    if (roomCodeEl) roomCodeEl.innerText = state.room_code || '------';
    const playerCountEl = document.getElementById('ui-player-count');
    if (playerCountEl) playerCountEl.innerText = (state.players ? state.players.length : 0);

    // Phase Routing
    const phases = ['waiting', 'word_reveal', 'clue', 'voting', 'reveal'];
    phases.forEach(p => {
        const el = document.getElementById(`phase-${p}`);
        if (el) {
            let isVisible = (state.room_status === p);
            // Treat 'resolving' backend status as 'voting' in frontend to avoid flicker
            if (p === 'voting' && state.room_status === 'resolving') isVisible = true;

            if (isVisible) {
                if (el.classList.contains('hidden')) {
                    el.classList.remove('hidden');
                    el.classList.add('flex');
                    if (window.playTone) window.playTone('swoosh');
                    haptic('medium');
                }
            } else {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        }
    });

    // Phase Renders & Timers
    if (state.room_status === 'waiting') renderLobby(state, myNickname);

    if (state.room_status === 'word_reveal') {
        renderIdentity(state);
        startPhaseTimer('word_reveal', state.phase_start, 15, state.server_time);
    } else if (state.room_status === 'clue') {
        renderCluePhase(state, myNickname);
        startPhaseTimer('clue', state.phase_start, 90, state.server_time);
    } else if (state.room_status === 'voting') {
        renderVotingPhase(state, myNickname);
        startPhaseTimer('voting', state.phase_start, 60, state.server_time);
    } else if (state.room_status === 'reveal') {
        renderRevealPhase(state);
        stopTimer();
    } else {
        stopTimer();
    }

    // Ghost Frequencies
    renderGhostFrequencies(state);

    // Cleanup logic
    if (state.room_status === 'waiting') {
        revealShown = false; voteBtnHash = ''; lastCluePhaseStart = 0;
        const ghostShell = document.getElementById('ghost-shell');
        if (ghostShell) ghostShell.classList.add('hidden');
    }
}

function startPhaseTimer(phase, phaseStart, durationSec, serverNow) {
    const key = phase + '_' + phaseStart;
    const elapsed = serverNow - phaseStart;
    const remaining = Math.max(0, durationSec - elapsed);
    if (currentTimerPhaseKey === key) return;

    currentTimerPhaseKey = key;
    let localRemaining = remaining;

    clearInterval(timerInterval);
    const timerWrap = document.getElementById('phase-timer');
    const timerEl = document.getElementById('timer-display');
    if (!timerWrap || !timerEl) return;

    timerWrap.style.display = 'flex';
    const tick = () => {
        if (localRemaining <= 0) {
            clearInterval(timerInterval);
            timerEl.innerText = '0s';
            return;
        }
        timerEl.innerText = localRemaining + 's';
        localRemaining--;
    };
    tick();
    timerInterval = setInterval(tick, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
    currentTimerPhaseKey = null;
    const w = document.getElementById('phase-timer');
    if (w) w.style.display = 'none';
}

function renderLobby(state, me) {
    const list = document.getElementById('player-list');
    if (!list) return;
    const players = (state.players || []);
    const uniquePlayers = Array.from(new Map(players.map(p => [p.id, p])).values());

    list.innerHTML = uniquePlayers.map(p => {
        const isMe = p.nickname === me;
        return `
            <div class="player-row border-neutral-800 bg-neutral-900/40 ${isMe ? 'is-me' : ''}">
                <span class="text-2xl">${p.avatar || '👤'}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-base font-semibold text-white">${p.nickname}</span>
                        ${p.is_host ? '<span class="text-[9px] font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-full border border-indigo-500/20">HOST</span>' : ''}
                        ${isMe ? '<span class="text-[9px] font-bold text-neutral-500 bg-neutral-800 px-2 py-0.5 rounded-full">YOU</span>' : ''}
                    </div>
                    <span class="text-[10px] font-bold ${p.is_alive ? 'text-neutral-600' : 'text-red-500'} block uppercase tracking-tight">${p.is_alive ? 'Active' : 'Neutralized'}</span>
                </div>
                ${(state.is_host && !p.is_host) ? `<button onclick="kickPlayer(${p.id})" class="text-[10px] font-bold text-neutral-500 hover:text-red-400 uppercase tracking-widest px-3 py-1.5 rounded-lg border border-neutral-800 transition-colors">KICK</button>` : ''}
            </div>
        `;
    }).join('');
    const hControls = document.getElementById('host-controls');
    if (hControls) hControls.style.display = (state.is_host ? 'block' : 'none');
}

function renderIdentity(state) {
    const card = document.getElementById('ui-role-card');
    if (!card) return;
    const isImposter = state.is_imposter;
    card.innerHTML = `
        <div class="neo-card p-10 bg-indigo-500/5 border-indigo-500/20 text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-500/10 flex items-center justify-center text-4xl">${isImposter ? '🕵️' : '🔑'}</div>
            <div class="space-y-1">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-[0.2em]">${isImposter ? 'You are Imposter' : 'You are Crew'}</span>
                <div class="text-5xl font-extrabold text-white capitalize tracking-tighter">${state.word || '??????'}</div>
            </div>
            <p class="text-xs text-neutral-500 font-medium px-4 leading-relaxed">${isImposter ? 'Your goal is to infiltrate the group and provide a convincing verbal clue.' : 'Your goal is to protect the secret word and identify the imposters.'}</p>
        </div>
    `;
}

function renderCluePhase(state, me) {
    if (state.phase_start !== lastCluePhaseStart) {
        lastCluePhaseStart = state.phase_start;
        const input = document.getElementById('clue-input');
        if (input) input.value = '';
    }
    const label = document.getElementById('clue-tag');
    if (label) label.innerText = state.is_imposter ? 'Imposter Clue' : 'Crew Clue';

    const feed = document.getElementById('clue-feed');
    if (feed) {
        const clues = (state.clues || []);
        const uniqueClues = Array.from(new Map(clues.map(c => [c.nickname, c])).values());

        const clueHtml = uniqueClues.map(c => `
            <div class="player-row border-neutral-800 bg-neutral-900/20 ${c.nickname === me ? 'is-me' : ''}">
                <span class="text-2xl">${c.avatar || '👤'}</span>
                <div class="flex-1">
                    <span class="text-[10px] font-bold text-neutral-600 block uppercase">${c.nickname}</span>
                    <span class="text-white font-semibold text-base">${c.clue_text}</span>
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
            </div>
        `).join('');

        // Show pending players
        const submittedIds = state.clue_player_ids || [];
        const pendingHtml = (state.players || []).filter(p => p.is_alive && !submittedIds.includes(p.id.toString())).map(p => `
            <div class="player-row border-neutral-800 bg-neutral-900/5 opacity-40 grayscale">
                <span class="text-2xl">${p.avatar || '👤'}</span>
                <div class="flex-1">
                    <span class="text-[10px] font-bold text-neutral-600 block uppercase">${p.nickname}</span>
                    <span class="text-neutral-500 italic text-sm">Transmitting...</span>
                </div>
            </div>
        `).join('');

        feed.innerHTML = (clueHtml + pendingHtml) || '<div class="text-center py-10 text-neutral-700 text-sm italic">Waiting for incoming signals...</div>';
    }

    const form = document.getElementById('clue-form');
    if (form) form.style.display = state.submitted_clue ? 'none' : 'block';
    const guessSection = document.getElementById('imposter-guess-section');
    if (guessSection) guessSection.style.display = (state.is_imposter && state.submitted_clue) ? 'block' : 'none';
}

function renderVotingPhase(state, me) {
    const btns = document.getElementById('vote-buttons');
    if (!btns) return;
    const summary = state.vote_summary || {};
    const elimCount = document.getElementById('eliminate-count');
    const skipCount = document.getElementById('skip-count');
    if (elimCount) elimCount.innerText = summary.eliminate || 0;
    if (skipCount) skipCount.innerText = summary.skip || 0;

    const votedIds = state.voted_player_ids || [];

    if (state.submitted_vote) {
        btns.innerHTML = `
            <div class="space-y-3">
                ${(state.players || []).map(p => {
            const hasVoted = votedIds.includes(p.id.toString());
            const isMe = p.nickname === me;
            return `
                        <div class="player-row border-neutral-800 bg-neutral-900/40 ${isMe ? 'is-me' : ''} ${!p.is_alive ? 'opacity-30' : ''}">
                            <span class="text-2xl">${p.avatar || '👤'}</span>
                            <span class="text-base font-semibold text-white">${p.nickname}</span>
                            ${hasVoted ? '<span class="ml-auto text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Locked</span>' : '<span class="ml-auto text-[10px] font-bold text-neutral-700 uppercase tracking-widest italic">Voting...</span>'}
                        </div>
                    `;
        }).join('')}
                <div class="text-center py-6 text-neutral-500 font-medium tracking-tight text-sm">Protocol locked. Awaiting consensus...</div>
            </div>
        `;
        const skipBtn = document.getElementById('skip-btn-wrap');
        if (skipBtn) skipBtn.style.display = 'none';
    } else {
        const skipBtn = document.getElementById('skip-btn-wrap');
        if (skipBtn) skipBtn.style.display = 'block';
        const myId = state.players ? state.players.find(p => p.nickname === me)?.id : null;
        const alivePlayers = (state.players || []).filter(p => p.is_alive && p.id !== myId);
        const newHash = alivePlayers.map(p => p.id).join(',');
        if (newHash !== voteBtnHash) {
            voteBtnHash = newHash;
            btns.innerHTML = alivePlayers.map(p => `
                <button onclick="submitVote(${p.id})" class="player-row w-full text-left hover:border-neutral-600 active:scale-[0.98] transition-all bg-neutral-900/40">
                    <span class="text-2xl">${p.avatar || '👤'}</span>
                    <span class="text-base font-semibold text-white">${p.nickname}</span>
                    <div class="ml-auto w-8 h-8 rounded-full bg-neutral-800 flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    </div>
                </button>
            `).join('');
        }
    }
}

function renderRevealPhase(state) {
    if (revealShown || !state.reveal) return;
    revealShown = true;
    const content = document.getElementById('reveal-content');
    if (!content) return;
    const won = state.reveal.you_won;
    if (window.playTone) window.playTone(won ? 'win' : 'lose');
    content.innerHTML = `
        <div class="space-y-8 py-10 animate-fade-in text-center">
            <div class="text-8xl">${won ? '🏆' : '💀'}</div>
            <div class="space-y-1">
                <h2 class="text-4xl font-extrabold text-white">${won ? 'Mission Win' : 'Mission Failed'}</h2>
                <div class="flex items-center justify-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white/5 text-neutral-500 uppercase tracking-widest">
                        ${state.reveal.end_reason === 'imposter_failed_guess' ? 'Compromised Guess' : (state.reveal.end_reason === 'imposter_guessed' ? 'Intelligence Leak' : 'Neural Consensus')}
                    </span>
                    <span class="text-neutral-500">•</span>
                    <p class="text-neutral-500 text-sm font-medium">${state.reveal.winner === 'crew' ? 'The Crew prevailed.' : 'Anomaly exfiltrated.'}</p>
                </div>
            </div>
            <div class="neo-card p-8 bg-neutral-900/50 space-y-6 text-left relative overflow-hidden">
                <div class="absolute inset-y-0 right-0 w-1 bg-${state.reveal.winner === 'crew' ? 'indigo-500' : 'red-500'}"></div>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-neutral-800 flex items-center justify-center text-4xl">${state.reveal.imposter_avatar}</div>
                    <div>
                        <span class="text-[10px] font-bold text-neutral-600 uppercase tracking-widest">Infiltrator</span>
                        <div class="text-2xl font-bold text-white">${state.reveal.imposter_name}</div>
                    </div>
                </div>
                <div class="pt-4 border-t border-neutral-800">
                    <span class="text-[10px] font-bold text-neutral-600 uppercase tracking-widest">Target Keyword</span>
                    <div class="text-4xl font-extrabold text-indigo-400 capitalize tracking-tight">${state.reveal.word}</div>
                </div>
            </div>
            ${state.is_host ? `
                <div class="pt-6">
                    <button onclick="handleHostReset()" class="neo-btn neo-btn-primary w-full h-14 text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Next Mission
                    </button>
                </div>
            ` : `
                <div class="pt-6">
                    <div class="neo-btn neo-btn-secondary w-full h-14 text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-3 opacity-60">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></div>
                        Awaiting Host Reset
                    </div>
                </div>
            `}
        </div>
    `;
}

// Global Actions
window.handleHostReset = async () => {
    const data = await apiCall('host_actions.php?action=reset_to_lobby', 'POST', new FormData());
    if (data.success) {
        showToast('System reset.', 'success');
        pollState();
    } else showToast(data.error);
};
window.startGame = async () => {
    const data = await apiCall('room_actions.php?action=start_game', 'POST', new FormData());
    if (data.success) {
        showToast('Mission initiated.', 'success');
        pollState();
    } else showToast(data.error);
};
window.copyRoomCode = () => {
    const code = document.getElementById('ui-room-code').innerText;
    if (code === '------') return;
    navigator.clipboard.writeText(code);
    const btn = document.getElementById('ui-room-code');
    const old = btn.innerText; btn.innerText = 'COPIED!';
    setTimeout(() => btn.innerText = old, 1500);
};
window.submitVote = async (id) => {
    const fd = new FormData(); fd.append('voted_id', id);
    const data = await apiCall('game_actions.php?action=submit_vote', 'POST', fd);
    if (data.success) {
        showToast('Vote locked.', 'success');
        pollState();
    } else showToast(data.error);
};
window.submitSkipVote = async () => {
    const data = await apiCall('game_actions.php?action=skip_vote', 'POST', new FormData());
    if (data.success) {
        showToast('Skip vote cast.', 'success');
        pollState();
    } else showToast(data.error);
};
window.submitClue = async () => {
    const input = document.getElementById('clue-input');
    const clue = input?.value.trim() || ''; if (!clue) return;
    const fd = new FormData(); fd.append('clue', clue);
    const data = await apiCall('game_actions.php?action=submit_clue', 'POST', fd);
    if (data.success) {
        input.value = '';
        showToast('Clue transmitted.', 'success');
        pollState();
    } else showToast(data.error);
};
window.submitImposterGuess = async () => {
    const input = document.getElementById('imposter-guess-input');
    const guess = input?.value.trim() || ''; if (!guess) return;
    const fd = new FormData(); fd.append('guess', guess);
    const data = await apiCall('game_actions.php?action=imposter_guess', 'POST', fd);
    const feedback = document.getElementById('imposter-guess-feedback');
    if (data.success) { if (data.correct) pollState(); else { feedback.style.color = 'var(--danger)'; feedback.innerText = 'Incorrect word.'; } }
};
window.kickPlayer = async (id) => {
    if (!confirm("Remove this player?")) return;
    const fd = new FormData(); fd.append('target_id', id);
    const data = await apiCall('host_actions.php?action=kick', 'POST', fd);
    if (data.success) {
        showToast('Player removed.', 'success');
        pollState();
    } else showToast(data.error);
};

// Spectral Mechanics
let _lastGhostCount = 0;
function renderGhostFrequencies(state) {
    const shell = document.getElementById('ghost-shell');
    if (!shell) return;

    const isGhost = !state.is_alive;
    const isReveal = state.room_status === 'reveal';

    // Show shell if dead or in result screen
    const intel = document.getElementById('ghost-intel');
    if (isGhost || isReveal) {
        if (shell.classList.contains('hidden')) {
            shell.classList.remove('hidden');
            if (isGhost) showToast("Spectral frequencies unlocked.", "success");
        }

        // Populate Intel
        if (intel && (state.word || isReveal)) {
            intel.classList.remove('hidden');
            const imposter = state.players.find(p => p.id == state.imposter_id);
            document.getElementById('ghost-imposter-name').textContent = imposter ? imposter.nickname : 'Unknown';
            document.getElementById('ghost-secret-word').textContent = state.word || '??????';
        }
    } else {
        shell.classList.add('hidden');
        if (intel) intel.classList.add('hidden');
    }

    const messagesEl = document.getElementById('ghost-messages');
    if (messagesEl && state.ghost_chat) {
        if (state.ghost_chat.length > _lastGhostCount) {
            const notif = document.getElementById('ghost-notif');
            if (notif && !document.getElementById('ghost-panel').classList.contains('active')) {
                notif.style.display = 'block';
                haptic('light');
            }
        }
        _lastGhostCount = state.ghost_chat.length;

        messagesEl.innerHTML = state.ghost_chat.map(m => {
            if (m.emoji && !m.message) {
                return `<div class="ghost-msg p-2 text-center animate-bounce-in ghost-reaction-bubble">${m.emoji}</div>`;
            }
            return `
                <div class="ghost-msg">
                    <div class="ghost-msg-header">${m.avatar} ${m.nickname}</div>
                    <div class="ghost-msg-bubble">${m.message}</div>
                </div>
            `;
        }).join('');
        // Scroll to bottom
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
}

window.toggleGhostPanel = () => {
    const panel = document.getElementById('ghost-panel');
    const notif = document.getElementById('ghost-notif');
    if (!panel) return;
    panel.classList.toggle('active');
    if (panel.classList.contains('active') && notif) notif.style.display = 'none';
    haptic('medium');
};

window.sendGhostSignal = async (msg = '', emoji = '') => {
    const input = document.getElementById('ghost-input');
    const actualMsg = msg || input.value.trim();
    if (!actualMsg && !emoji) return;

    const fd = new FormData();
    fd.append('message', actualMsg);
    fd.append('emoji', emoji);

    const res = await apiCall('ghost_actions.php?action=send', 'POST', fd);
    if (res.success) {
        if (input) input.value = '';
        pollState();
    } else {
        showToast(res.error);
    }
};

// Listen for Enter in ghost chat
document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && document.activeElement.id === 'ghost-input') {
        sendGhostSignal();
    }
});

// Initializer
if (window.location.search.includes('view=room')) {
    pollState();
    pollTimer = setInterval(pollState, 2000); // Snappier sync
}
