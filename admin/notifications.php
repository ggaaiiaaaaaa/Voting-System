<?php
session_start();
require_once __DIR__ . "/../classes/notification.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$notifObj = new Notification();
$notifications = $notifObj->getAllNotifications($_SESSION['user_id'], 'admin', 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Notifications</h2>
                <p class="text-sm text-gray-300 mt-1">View all system notifications and alerts</p>
            </div>
            <a href="../includes/mark_all_read.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">Mark All as Read</a>
        </header>

        <!-- NOTIFICATIONS SECTION -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
            <div class="space-y-4">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 12.683A17.925 17.925 0 0112 21c7.962 0 12-1.21 12-2.683m-12 2.683a17.925 17.925 0 01-7.132-8.317M12 21c4.411 0 8-4.03 8-9s-3.589-9-8-9-8 4.03-8 9a9.06 9.06 0 001.832 5.683L4 21l4.868-8.317z"></path>
                        </svg>
                        <span class="text-gray-400 text-sm">No notifications yet</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10 hover:bg-white/10 transition-colors <?= $notif['is_read'] ? '' : 'border-l-4 border-blue-500/50' ?>">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-white mb-2">
                                        <?= htmlspecialchars($notif['title']) ?>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="ml-2 inline-block w-2 h-2 bg-blue-400 rounded-full"></span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-gray-300 mb-3"><?= htmlspecialchars($notif['message']) ?></p>
                                    <p class="text-sm text-gray-400">
                                        <?= date('F d, Y h:i A', strtotime($notif['created_at'])) ?>
                                    </p>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                    <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>"
                                       class="ml-4 text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                                        Mark as read
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
