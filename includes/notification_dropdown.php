<?php
require_once __DIR__ . "/../classes/notification.php";

$notifObj = new Notification();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'admin' : 'student';

$unread_count = $notifObj->getUnreadCount($user_id, $user_type);
$notifications = $notifObj->getAllNotifications($user_id, $user_type, 10);
?>

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-white hover:text-gray-300">
        🔔
        <?php if ($unread_count > 0): ?>
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                <?= $unread_count ?>
            </span>
        <?php endif; ?>
    </button>

    <div x-show="open" @click.away="open = false"
         class="absolute right-0 mt-2 w-80 bg-slate-800 rounded-lg shadow-2xl overflow-hidden z-60 border border-white/20"
         style="display: none;">
        <div class="p-3 bg-slate-700 border-b border-white/20">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-white">Notifications</h3>
                <?php if ($unread_count > 0): ?>
                    <a href="../includes/mark_all_read.php" class="text-xs text-blue-400 hover:underline">
                        Mark all as read
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <?php if (empty($notifications)): ?>
                <div class="p-4 text-center text-gray-400">
                    No notifications yet
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>"
                       class="block p-3 border-b border-white/10 hover:bg-slate-700 <?= $notif['is_read'] ? 'bg-slate-800' : 'bg-blue-900/20' ?>">
                        <div class="flex items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-white"><?= htmlspecialchars($notif['title']) ?></p>
                                <p class="text-xs text-gray-300 mt-1"><?= htmlspecialchars($notif['message']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                                </p>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <span class="ml-2 w-2 h-2 bg-blue-500 rounded-full"></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="p-3 bg-slate-700 border-t border-white/20 text-center">
            <a href="../<?= $user_type ?>/notifications.php" class="text-sm text-blue-400 hover:underline">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>