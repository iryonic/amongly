<?php
require_once 'config/config.php';

// Route protection: Force nickname if not set
$view = $_GET['view'] ?? 'landing';
$allowed_without_session = ['setup_nickname', 'logout'];

// Handle logout before any HTML output
if ($view === 'logout') {
    include 'views/logout.php';
    exit; // logout.php does its own redirect
}

if (!isset($_SESSION['nickname']) && !in_array($view, $allowed_without_session)) {
    // Allow 'room' view if the player has a valid token (token-based login)
    // Otherwise send to identity setup
    header('Location: index.php?view=setup_nickname');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-black touch-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#09090b">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="./assets/img/icon-512x512.png" type="image/png">
    <link rel="apple-touch-icon" href="./assets/img/icon-512x512.png">
    <title>AMONGLY • THE APP</title>
    
    <!-- High-Impact UI System -->
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        acid: '#bef264',
                        pulsar: '#f43f5e',
                        surface: '#0c0c0e',
                        accent: '#6366f1',
                    }
                }
            }
        }
    </script>
</head>
<body class="selection:bg-indigo-500/30">
    <div class="neo-shell">
        <div id="view-root" class="view-content view-transition v-hidden flex flex-col">
            <main class="max-nexus flex-1 !min-h-[auto] pb-12">
                <?php 
                $file = "views/{$view}.php";
                if (file_exists($file)) include $file;
                else include 'views/landing.php';
                ?>
            </main>

            <?php if ($view !== 'room'): ?>
            <footer class="max-nexus !min-h-0 py-12 text-center space-y-4 opacity-40 hover:opacity-100 transition-opacity">
                <div class="h-px w-10 bg-white/10 mx-auto"></div>
                <div class="space-y-2">
                    <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-[0.4em]">
                        &copy; <?= date('Y') ?> MADE WITH ❤️ BY <a href="https://irfanmanzoor.in" target="_blank">IRFAN MANOOR</a>
                    </p>
                    <div class="flex items-center justify-center gap-4 text-[8px] font-black text-neutral-600 uppercase tracking-widest">
                        <span>AMONGLY</span>
                        <div class="w-1 h-1 rounded-full bg-neutral-800"></div>
                        <span>V2.4.0-STABLE</span>
                    </div>
                </div>
            </footer>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feedback Layer -->
    <div id="toast-container" class="fixed bottom-10 left-0 right-0 px-6 z-[100] pointer-events-none flex flex-col items-center gap-3"></div>

    <!-- Haptic Engine & Navigation Logic -->
    <script>
        // --- UX Feedback ---
        window.showToast = (msg, type = 'error') => {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `px-6 py-4 rounded-2xl shadow-2xl backdrop-blur-xl border pointer-events-auto animate-slide-up bg-neutral-900/90 ${type === 'success' ? 'border-indigo-500/30 text-indigo-400' : 'border-red-500/30 text-red-400'}`;
            toast.innerHTML = `<span class="text-sm font-bold tracking-tight">${msg}</span>`;
            container.appendChild(toast);
            haptic(type === 'success' ? 'light' : 'medium');
            setTimeout(() => {
                toast.classList.add('animate-fade-out');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        };
        let _userInteracted = false;
        document.addEventListener('touchstart', () => _userInteracted = true, { once: true, passive: true });
        document.addEventListener('click',      () => _userInteracted = true, { once: true });

        window.haptic = (type = 'light') => {
            if (!_userInteracted || !window.navigator.vibrate) return;
            const patterns = { light: 10, medium: 25, heavy: 50 };
            window.navigator.vibrate(patterns[type] || 10);
        };

        // --- Synthetic Audio Engine ---
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx;
        window.playTone = (type = 'tick') => {
            if (!_userInteracted) return;
            if (!audioCtx) audioCtx = new AudioContext();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            const now = audioCtx.currentTime;
            
            if (type === 'tick') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, now);
                osc.frequency.exponentialRampToValueAtTime(300, now + 0.1);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.1);
                osc.start(now);
                osc.stop(now + 0.1);
            } else if (type === 'swoosh') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(200, now);
                osc.frequency.exponentialRampToValueAtTime(800, now + 0.3);
                gain.gain.setValueAtTime(0, now);
                gain.gain.linearRampToValueAtTime(0.2, now + 0.1);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                osc.start(now);
                osc.stop(now + 0.3);
            } else if (type === 'win') {
                osc.type = 'square';
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.setValueAtTime(554, now + 0.1);
                osc.frequency.setValueAtTime(659, now + 0.2);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.linearRampToValueAtTime(0, now + 0.6);
                osc.start(now);
                osc.stop(now + 0.6);
            } else if (type === 'lose') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(300, now);
                osc.frequency.linearRampToValueAtTime(100, now + 0.5);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0, now + 0.5);
                osc.start(now);
                osc.stop(now + 0.5);
            }
        };

        window.navigate = (url) => {
            const root = document.getElementById('view-root');
            root.classList.add('v-hidden');
            haptic('light');
            setTimeout(() => window.location.href = url, 400);
        };

        // Node Token System (Tab Isolation)
        const getNodeToken = () => {
            let token = sessionStorage.getItem('amongly_node_token');
            if (!token) {
                token = 'node_' + Math.random().toString(36).substr(2, 9) + Date.now();
                sessionStorage.setItem('amongly_node_token', token);
            }
            return token;
        };

        const apiCall = async (url, method = 'GET', body = null) => {
            try {
                // Base64 encoding for headers to strictly avoid ISO-8859-1 violations (Emojis)
                const safeB64 = (str) => btoa(unescape(encodeURIComponent(str || '')));

                const headers = { 
                    'X-Node-Token': getNodeToken(),
                    'X-Node-Alias': safeB64(sessionStorage.getItem('amongly_nickname')),
                    'X-Node-Avatar': safeB64(sessionStorage.getItem('amongly_avatar') || '👤')
                };
                
                const options = { method, headers };
                if (body) options.body = body;
                const res = await fetch(`api/${url}`, options);
                return await res.json();
            } catch (err) {
                console.error('API Error:', err);
                return { success: false, error: 'Network failure' };
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Tab Identity Guard
            const currentView = '<?= $view ?>';
            if (currentView !== 'setup_nickname' && !sessionStorage.getItem('amongly_nickname')) {
                window.location.href = 'index.php?view=setup_nickname';
                return;
            }

            requestAnimationFrame(() => {
                const root = document.getElementById('view-root');
                root.classList.remove('v-hidden');
                root.classList.add('v-visible');
            });
        });
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
