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
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">
    <!-- SIDEBAR (copy from admin_dashboard.php) -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Notifications</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🏠 Overview</a></li>
                <li><a href="election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🗳️ Election Management</a></li>
                <li><a href="student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👥 Students</a></li>
                <li><a href="teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📌 Positions</a></li>
                <li><a href="nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📋 Nominations</a></li>
                <li><a href="election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📈 Results</a></li>
                <li><a href="notifications.php" class="flex items-center gap-3 px-6 py-2 bg-red-100 text-red-700 font-medium">🔔 Notifications</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Notifications</h2>
                <p class="text-sm text-gray-500">View all system notifications and alerts</p>
            </div>
            <a href="../includes/mark_all_read.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Mark All as Read
            </a>
        </header>

        <div class="space-y-4">
            <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    <p class="text-4xl mb-4">🔔</p>
                    <p>No notifications yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="bg-white rounded-lg shadow p-6 <?= $notif['is_read'] ? '' : 'border-l-4 border-blue-600' ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <?= htmlspecialchars($notif['title']) ?>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="ml-2 inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <?php endif; ?>
                                </h3>
                                <p class="text-gray-700 mb-3"><?= htmlspecialchars($notif['message']) ?></p>
                                <p class="text-sm text-gray-500">
                                    <?= date('F d, Y h:i A', strtotime($notif['created_at'])) ?>
                                </p>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <a href="../includes/mark_read.php?id=<?= $notif['id'] ?>" 
                                   class="ml-4 text-blue-600 hover:underline text-sm">
                                    Mark as read
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>