<!-- admin/navbar.php -->
<nav class="sticky top-0 z-50 w-full glass border-b border-white/5 py-3 sm:py-4 px-4 sm:px-8 mb-4 sm:mb-8">
    <style>
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-slide-down { animation: slideDown 0.3s ease-out forwards; }
    </style>
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4 sm:gap-8">
            <!-- Mobile Menu Toggle -->
            <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-400 hover:text-white transition-colors">
                <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <a href="dashboard.php" class="flex items-center gap-2 sm:gap-3 group">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3m0 18a10.003 10.003 0 01-9.57-7.108m13.111 6.671a10.004 10.004 0 01-2.228-3.653m3.073-8.717a10.001 10.001 0 011.614 5.341m-14.82 0a10.001 10.001 0 011.614-5.341"></path></svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-black tracking-tighter text-white">Amongly <span class="text-indigo-500">Admin</span></h1>
                </div>
            </a>

            <div class="hidden lg:block h-6 w-px bg-slate-800"></div>

            <!-- Desktop Links -->
            <div class="hidden lg:flex items-center gap-1">
                <a href="dashboard.php" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all rounded-lg <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    Dashboard
                </a>
                <a href="words.php" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all rounded-lg <?= basename($_SERVER['PHP_SELF']) == 'words.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    Words
                </a>
                <a href="categories.php" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all rounded-lg <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    Categories
                </a>
                <a href="avatars.php" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all rounded-lg <?= basename($_SERVER['PHP_SELF']) == 'avatars.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    Avatars
                </a>
                <a href="change_password.php" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all rounded-lg <?= basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    Security
                </a>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-6">
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-emerald-500/5 border border-emerald-500/10 rounded-lg">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest truncate max-w-[120px]"><?= $_SESSION['admin_email'] ?></span>
            </div>
            <a href="logout.php" class="group flex items-center gap-2 text-slate-400 hover:text-rose-500 transition-colors">
                 <span class="hidden sm:inline text-[10px] font-black uppercase tracking-widest">Logout</span>
                 <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden animate-slide-down border-t border-white/5 mt-4 pt-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="words.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all <?= basename($_SERVER['PHP_SELF']) == 'words.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Words Matrix
        </a>
        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Category Matrix
        </a>
        <a href="avatars.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all <?= basename($_SERVER['PHP_SELF']) == 'avatars.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Avatars Vault
        </a>
        <a href="change_password.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all <?= basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'text-white bg-indigo-600/10 border border-indigo-500/20' : 'text-slate-400' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Security Center
        </a>
        
        <div class="h-px bg-white/5 my-2"></div>
        
        <div class="px-4 py-2 flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?= $_SESSION['admin_email'] ?></span>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const openIcon = document.getElementById('menu-icon-open');
    const closeIcon = document.getElementById('menu-icon-close');
    
    const isHidden = menu.classList.contains('hidden');
    
    if (isHidden) {
        menu.classList.remove('hidden');
        openIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
    } else {
        menu.classList.add('hidden');
        openIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
    }
}
</script>
