<?php
// admin/change_password.php
require_once '../config/config.php';

// Auth Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

$db = Database::getInstance();
$stmt = $db->prepare("SELECT email, password_hash FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newEmail = clean($_POST['new_email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Verify current password first
    if (!$admin || !password_verify($currentPassword, $admin['password_hash'])) {
        $error = 'Current password is incorrect.';
    } else {
        $updates = [];
        $params = [];

        // Handle Email Update
        if ($newEmail && $newEmail !== $admin['email']) {
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } else {
                $updates[] = "email = ?";
                $params[] = $newEmail;
                $_SESSION['admin_email'] = $newEmail;
            }
        }

        // Handle Password Update
        if (!$error && $newPassword) {
            if ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters.';
            } else {
                $updates[] = "password_hash = ?";
                $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }

        if (!$error && !empty($updates)) {
            $sql = "UPDATE admins SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $_SESSION['admin_id'];
            $db->prepare($sql)->execute($params);
            $success = 'Credentials updated successfully.';
            
            // Refresh admin data
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
        } elseif (!$error && empty($updates)) {
            $error = 'No changes detected.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings | Amongly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.05); }
        input::-ms-reveal, input::-ms-clear { display: none; }
    </style>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_50%_0%,#1e1b4b_0%,#020617_80%)]">
    <?php include 'navbar.php' ?>
    <div class="w-full max-w-md mx-auto space-y-8 animate-fade-in p-6 pt-12">
        <div class="text-center space-y-2">
            <h1 class="text-4xl font-extrabold text-white tracking-tight">Security <span class="text-indigo-500">Center</span></h1>
            <p class="text-slate-400 font-medium">Manage your administrative identity.</p>
        </div>

        <div class="glass p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-bold flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form action="change_password.php" method="POST" class="space-y-6">
                <!-- Current Password (Auth Wall) -->
                <div class="space-y-2 relative">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Confirm Identity (Current Password)</label>
                    <div class="relative">
                        <input type="password" name="current_password" required placeholder="••••••••" 
                            class="password-field w-full bg-slate-900 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all pr-12">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-600 hover:text-indigo-400 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="h-px bg-slate-800/50 my-6"></div>

                <!-- Email Update -->
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Admin Email</label>
                    <input type="email" name="new_email" value="<?= htmlspecialchars($admin['email']) ?>" 
                        class="w-full bg-slate-900 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>

                <!-- New Password -->
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">New Password (Leave blank to keep current)</label>
                    <div class="relative">
                        <input type="password" name="new_password" placeholder="••••••••" 
                            class="password-field w-full bg-slate-900 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all pr-12">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-600 hover:text-indigo-400 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" placeholder="••••••••" 
                            class="password-field w-full bg-slate-900 border border-slate-700/50 rounded-2xl p-4 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all pr-12">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-600 hover:text-indigo-400 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                
                <div class="pt-4 flex flex-col gap-4">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition-all text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20 uppercase tracking-widest text-xs">
                        Save Identity Changes
                    </button>
                    <a href="dashboard.php" class="text-center text-slate-500 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">
                        Cancel & Exit
                    </a>
                </div>
            </form>
        </div>
        
        <p class="text-center text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em]">
            Amongly Administration Protocol • Security Layer Active
        </p>
    </div>

    <script>
        function togglePassword(btn) {
            const input = btn.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            btn.classList.toggle('text-indigo-400');
        }
    </script>
</body>
</html>
