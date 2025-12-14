<?php
session_start();

// ✅ SET PHILIPPINE TIMEZONE AT THE TOP
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . "/../classes/notification.php";
require_once __DIR__ . "/../config/timezone_config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$notifObj = new Notification();
$notifications = $notifObj->getAllNotifications($_SESSION['user_id'], 'student', 100);

// ✅ Helper function to get notification icon based on type
function getNotificationIcon($type) {
    switch($type) {
        case 'nomination_approved':
            return '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'nomination_rejected':
            return '<svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'election_started':
        case 'election_resumed':
            return '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'election_paused':
            return '<svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'election_ended':
            return '<svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>';
        case 'vote_confirmed':
            return '<svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>';
        default:
            return '<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(50px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-item {
            animation: slideInRight 0.5s ease-out backwards;
        }

        .notification-item:nth-child(1) { animation-delay: 0.05s; }
        .notification-item:nth-child(2) { animation-delay: 0.1s; }
        .notification-item:nth-child(3) { animation-delay: 0.15s; }
        .notification-item:nth-child(4) { animation-delay: 0.2s; }
        .notification-item:nth-child(5) { animation-delay: 0.25s; }
        .notification-item:nth-child(6) { animation-delay: 0.3s; }
        .notification-item:nth-child(7) { animation-delay: 0.35s; }
        .notification-item:nth-child(8) { animation-delay: 0.4s; }
        .notification-item:nth-child(9) { animation-delay: 0.45s; }
        .notification-item:nth-child(10) { animation-delay: 0.5s; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">My Notifications</h2>
                <p class="text-sm text-gray-300 mt-1">View all your notifications and updates</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Mark All as Read Button -->
                <a href="../includes/mark_all_read.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
                    Mark All as Read
                </a>
            </div>
        </header>

        <!-- NOTIFICATIONS SECTION -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
            <div class="space-y-4">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-400 text-lg font-medium">No notifications yet</p>
                        <p class="text-gray-500 text-sm mt-2">You'll see notifications here when there's activity</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item bg-white/5 backdrop-blur-sm rounded-xl p-6 border <?= $notif['is_read'] ? 'border-white/10' : 'border-l-4 border-blue-500/50 border-t border-r border-b border-white/10' ?> hover:bg-white/10 transition-all duration-300 group">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0 w-12 h-12 <?= $notif['is_read'] ? 'bg-gray-700/50' : 'bg-blue-500/20' ?> rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <?= getNotificationIcon($notif['type']) ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                            <?= htmlspecialchars($notif['title']) ?>
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="inline-block w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></span>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="bg-blue-500/20 text-blue-300 text-xs px-2 py-1 rounded-full font-medium">
                                                NEW
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-gray-300 mb-4 leading-relaxed">
                                        <?= htmlspecialchars($notif['message']) ?>
                                    </p>
                                    
                                    <!-- Timestamp with relative time -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4 text-sm">
                                            <!-- Full timestamp -->
                                            <div class="flex items-center gap-2 text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span><?= formatPhilippineTime($notif['created_at']) ?></span>
                                                <span class="text-xs bg-gray-700/50 px-2 py-1 rounded">PHT</span>
                                            </div>
                                            
                                            <!-- Relative time -->
                                            <div class="text-gray-300">
                                                • <?= getRelativeTime($notif['created_at']) ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Mark as read button -->
                                        <?php if (!$notif['is_read']): ?>
                                            <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>"
                                               class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors flex items-center gap-2 group/btn">
                                                <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Mark as read
                                            </a>
                                        <?php else: ?>
                                            <div class="text-gray-500 text-sm flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Read
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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