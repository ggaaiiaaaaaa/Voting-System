<?php
// includes/admin_sidebar.php
// Active page detection - get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Helper function to determine if a menu item is active
function isActive($page, $dir = null) {
    global $current_page, $current_dir;
    if ($dir && $current_dir !== $dir) return false;
    return $current_page === $page ? 'bg-red-100 text-red-700 font-medium' : 'hover:bg-red-100 text-gray-700';
}

// Determine base path based on current location
$base_path = '';
if ($current_dir === 'admin') {
    $base_path = '';
} else {
    $base_path = '../';
}
?>

<aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col z-50">
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-red-700">Election Admin</h1>
        <p class="text-xs text-gray-500 mt-1">Dashboard Panel</p>
    </div>

    <nav class="flex-1 overflow-y-auto mt-4">
        <ul class="space-y-1">
            <li>
                <a href="<?= $base_path ?>admin_dashboard.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('admin_dashboard.php', 'admin') ?>">
                    🏠 Overview
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/manage_schedule.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('manage_schedule.php', 'election') ?>">
                    🗳️ Election Management
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>student/view_student.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_student.php', 'student') ?> <?= isActive('add_student.php', 'student') ?> <?= isActive('edit_student.php', 'student') ?>">
                    👥 Students
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>teacher/view_teacher.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_teacher.php', 'teacher') ?> <?= isActive('add_teacher.php', 'teacher') ?> <?= isActive('edit_teacher.php', 'teacher') ?>">
                    👨‍🏫 Teachers
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>position/view_position.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_position.php', 'position') ?> <?= isActive('add_position.php', 'position') ?> <?= isActive('edit_position.php', 'position') ?>">
                    📌 Positions
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>nomination/view_nomination.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_nomination.php', 'nomination') ?> <?= isActive('approve_nomination.php', 'nomination') ?>">
                    📋 Nominations
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/view_results.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_results.php', 'election') ?>">
                    📈 Results
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/view_reports.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('view_reports.php', 'election') ?>">
                    📊 Reports
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>notifications.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('notifications.php', 'admin') ?>">
                    🔔 Notifications
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>admin_profile.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('admin_profile.php', 'admin') ?>">
                    👤 Profile
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/audit_log.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isActive('audit_log.php', 'election') ?>">
                    ⚙️ System Controls
                </a>
            </li>
        </ul>
    </nav>

    <div class="border-t p-4">
        <a href="<?= $base_path ?>../auth/logout.php" 
           class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">
            Logout
        </a>
    </div>
</aside>