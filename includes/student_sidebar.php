<?php
// includes/student_sidebar.php
// Require necessary classes for sidebar functionality
require_once __DIR__ . "/../classes/election.php";

// Active page detection
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to determine if a menu item is active
function isStudentActive($page) {
    global $current_page;
    return $current_page === $page ? 'bg-red-100 text-red-700 font-medium' : 'hover:bg-red-100 text-gray-700';
}

// Get election status to determine which menu items should be enabled
$electionObj = new Election();
$schedule_status = $electionObj->getAdminControlledStatus();
$student_id = $_SESSION['user_id'] ?? null;

$canNominate = false;
$canVote = false;

if ($student_id) {
    switch($schedule_status) {
        case 'Ongoing':
            $canNominate = true;
            $has_voted = $electionObj->hasStudentVoted($student_id);
            $canVote = !$has_voted;
            break;
        case 'Paused':
        case 'Ended':
        case 'Upcoming':
        default:
            $canNominate = false;
            $canVote = false;
            break;
    }
}
?>

<aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col z-50">
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-red-700">Student Panel</h1>
        <p class="text-xs text-gray-500 mt-1">Dashboard Panel</p>
    </div>

    <nav class="flex-1 overflow-y-auto mt-4">
        <ul class="space-y-1">
            <li>
                <a href="student_dashboard.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isStudentActive('student_dashboard.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="nominate.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isStudentActive('nominate.php') ?> <?= $canNominate ? '' : 'opacity-50 pointer-events-none' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Nominations
                </a>
            </li>
            <li>
                <a href="voting.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isStudentActive('voting.php') ?> <?= $canVote ? '' : 'opacity-50 pointer-events-none' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.416 21.416a2.25 2.25 0 00-2.242-2.242H6.826a2.25 2.25 0 00-2.242 2.242m14.832 0a2.25 2.25 0 01-2.242-2.242H6.826a2.25 2.25 0 01-2.242 2.242m14.832 0L18 14.832m-1.416-1.416L12 10.832m-5.416 5.416L6 14.832" />
                    </svg>
                    Vote
                </a>
            </li>
            <li>
                <a href="view_results.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isStudentActive('view_results.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Results
                </a>
            </li>
            <li>
                <a href="notifications.php" 
                   class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700<?= isStudentActive('notifications.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </a>
            </li>
        </ul>
    </nav>

    <div class="border-t p-4">
        <a href="../auth/logout.php" 
           class="flex items-center justify-center gap-3 bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
        </a>
    </div>
</aside>