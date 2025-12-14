<?php
// ✅ SET PHILIPPINE TIMEZONE
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . "/../classes/notification.php";
require_once __DIR__ . "/../config/timezone_config.php";

$notifObj = new Notification();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'admin' : 'student';

$unread_count = $notifObj->getUnreadCount($user_id, $user_type);
$notifications = $notifObj->getAllNotifications($user_id, $user_type, 10);

// ✅ Helper function to format datetime in Philippine Time (short format)
function formatPhilippineTimeShort($datetime) {
    $dt = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    
    // If today, show time only
    if ($dt->format('Y-m-d') === $now->format('Y-m-d')) {
        return $dt->format('h:i A');
    }
    
    // If this year, show month/day and time
    if ($dt->format('Y') === $now->format('Y')) {
        return $dt->format('M j, h:i A');
    }
    
    // Otherwise show full date
    return $dt->format('M j, Y h:i A');
}

// ✅ Helper function to get relative time
function getRelativeTimeShort($datetime) {
    $dt = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $diff = $now->diff($dt);
    
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Now';
}

// ✅ Helper function to get notification icon
function getNotificationIconSmall($type) {
    switch($type) {
        case 'nomination_approved':
            return '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'nomination_rejected':
            return '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'new_nomination':
            return '<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>';
        case 'new_vote':
            return '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'election_started':
        case 'election_resumed':
            return '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
            </svg>';
        case 'election_paused':
            return '<svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        case 'election_ended':
            return '<svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>';
        default:
            return '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>';
    }
}
?>

<div class="relative" x-data="{ open: false }">
    <!-- Button with Badge -->
    <button @click="open = !open" class="relative p-2 text-white hover:text-gray-300 focus:outline-none transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <?php if ($unread_count > 0): ?>
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full animate-pulse">
                <?= $unread_count > 99 ? '99+' : $unread_count ?>
            </span>
        <?php endif; ?>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-3 w-96 bg-slate-800/95 backdrop-blur-md rounded-xl shadow-2xl overflow-hidden z-[100] border border-white/20 origin-top-right"
         style="display: none;">
         
        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-slate-700 to-slate-800 border-b border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-white text-base">Notifications</h3>
                    <?php if ($unread_count > 0): ?>
                        <span class="bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                            <?= $unread_count ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($unread_count > 0): ?>
                    <a href="../includes/mark_all_read.php" 
                       class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors">
                        Clear all
                    </a>
                <?php endif; ?>
            </div>
            <!-- Timezone indicator -->
            <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Philippine Time (PHT)</span>
            </div>
        </div>

        <!-- Content -->
        <div class="max-h-[28rem] overflow-y-auto custom-scrollbar">
            <?php if (empty($notifications)): ?>
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-400 text-sm font-medium">No notifications</p>
                    <p class="text-gray-500 text-xs mt-1">You're all caught up!</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>"
                       class="block px-4 py-3 border-b border-white/5 hover:bg-slate-700/50 transition-all duration-200 <?= $notif['is_read'] ? 'bg-slate-800/50' : 'bg-blue-900/10' ?> group">
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-10 h-10 <?= $notif['is_read'] ? 'bg-slate-700/50' : 'bg-blue-500/20' ?> rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <?= getNotificationIconSmall($notif['type']) ?>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <p class="text-sm font-semibold text-white line-clamp-1 flex items-center gap-1.5">
                                        <?= htmlspecialchars($notif['title']) ?>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="flex-shrink-0 bg-blue-500 text-white text-[10px] px-1.5 py-0.5 rounded font-bold">
                                            NEW
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-xs text-gray-300 line-clamp-2 mb-2 leading-relaxed">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                
                                <!-- Timestamp -->
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?= formatPhilippineTimeShort($notif['created_at']) ?>
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <span class="text-gray-500">
                                        <?= getRelativeTimeShort($notif['created_at']) ?>
                                    </span>
                                    <span class="text-xs bg-slate-700/70 text-gray-400 px-1.5 py-0.5 rounded">PHT</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-slate-700/50 border-t border-white/10 text-center">
            <a href="../<?= $user_type ?>/notifications.php" 
               class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors inline-flex items-center gap-2 group">
                View all notifications
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Custom scrollbar styles -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.3);
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(71, 85, 105, 0.5);
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(71, 85, 105, 0.7);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>