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

<aside class="w-64 bg-slate-900/95 backdrop-blur-sm shadow-2xl fixed h-screen flex flex-col z-50 border-r border-white/20">
    <div class="p-6 border-b border-white/20">
        <h1 class="text-2xl font-bold text-white drop-shadow-lg">Election Admin</h1>
        <p class="text-xs text-gray-300 mt-1">Dashboard Panel</p>
    </div>

    <nav class="flex-1 overflow-y-auto mt-4">
        <ul class="space-y-2">
            <li>
                <a href="<?= $base_path ?>admin_dashboard.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('admin_dashboard.php', 'admin') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/manage_schedule.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('manage_schedule.php', 'election') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Election Management
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>student/view_student.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('view_student.php', 'student') ?> <?= isActive('add_student.php', 'student') ?> <?= isActive('edit_student.php', 'student') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.122-1.28-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.122-1.28.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Students
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>position/view_position.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('view_position.php', 'position') ?> <?= isActive('add_position.php', 'position') ?> <?= isActive('edit_position.php', 'position') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 8v5z" />
                    </svg>
                    Positions
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>nomination/view_nomination.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('view_nomination.php', 'nomination') ?> <?= isActive('approve_nomination.php', 'nomination') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Nominations
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/view_results.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('view_results.php', 'election') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Results
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/view_reports.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('view_reports.php', 'election') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m-3-4H9a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2z" />
                    </svg>
                    Reports
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>notifications.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('notifications.php', 'admin') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>admin_profile.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('admin_profile.php', 'admin') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>
            </li>
            <li>
                <a href="<?= $base_path ?>election/audit_log.php"
                   class="flex items-center gap-3 px-6 py-3 rounded-xl hover:bg-white/10 text-white/90 hover:text-white transition-all duration-300<?= isActive('audit_log.php', 'election') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.096 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    System Controls
                </a>
            </li>
        </ul>
    </nav>

    <div class="border-t border-white/20 p-4">
        <a href="<?= $base_path ?>../auth/logout.php"
           class="flex items-center justify-center gap-3 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 py-3 px-6 rounded-xl hover:bg-red-500/30 hover:text-red-200 transition-all duration-300 font-semibold shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
        </a>
    </div>
</aside>