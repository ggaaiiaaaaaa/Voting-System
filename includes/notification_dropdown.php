<?php
require_once __DIR__ . "/../classes/notification.php";

$notifObj = new Notification();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'admin' : 'student';

$unread_count = $notifObj->getUnreadCount($user_id, $user_type);
$notifications = $notifObj->getAllNotifications($user_id, $user_type, 10);
?>

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-800">
        🔔
        <?php if ($unread_count > 0): ?>
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                <?= $unread_count ?>
            </span>
        <?php endif; ?>
    </button>

    <div x-show="open" @click.away="open = false" 
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50"
         style="display: none;">
        <div class="p-3 bg-gray-50 border-b">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Notifications</h3>
                <?php if ($unread_count > 0): ?>
                    <a href="../includes/mark_all_read.php" class="text-xs text-blue-600 hover:underline">
                        Mark all as read
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
            <?php if (empty($notifications)): ?>
                <div class="p-4 text-center text-gray-500">
                    No notifications yet
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>" 
                       class="block p-3 border-b hover:bg-gray-50 <?= $notif['is_read'] ? 'bg-white' : 'bg-blue-50' ?>">
                        <div class="flex items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($notif['title']) ?></p>
                                <p class="text-xs text-gray-600 mt-1"><?= htmlspecialchars($notif['message']) ?></p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                                </p>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <span class="ml-2 w-2 h-2 bg-blue-600 rounded-full"></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="p-3 bg-gray-50 border-t text-center">
            <a href="../<?= $user_type ?>/notifications.php" class="text-sm text-blue-600 hover:underline">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>