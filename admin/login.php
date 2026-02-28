<?php
// admin/login.php
require_once '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Amongly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-[radial-gradient(circle_at_50%_-20%,#1e1b4b_0%,#020617_80%)]">
    <div class="w-full max-w-md space-y-8 animate-fade-in">
        <div class="text-center space-y-2">
            <h1 class="text-4xl font-extrabold text-white tracking-tight">Amongly <span class="text-indigo-500">Admin</span></h1>
            <p class="text-slate-400 font-medium">Mission Control Authentication</p>
        </div>

        <div class="glass p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Admin Email</label>
                    <input type="email" name="email" required placeholder="name@amongly.com" 
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all placeholder:text-slate-600">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Secret Key</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all placeholder:text-slate-600">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition-all text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20">
                    Authorize Access
                </button>
            </form>
        </div>
        
        <p class="text-center text-slate-500 text-xs font-medium">
            System version 1.0.4 • Protected by Amongly Core
        </p>
    </div>
</body>
</html>
