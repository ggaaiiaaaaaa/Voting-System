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
                   class="flex items-center gap-3 px-6 py-2 <?= isStudentActive('student_dashboard.php') ?>">
                    🏠 Overview
                </a>
            </li>
            <li>
                <a href="nominate.php" 
                   class="flex items-center gap-3 px-6 py-2 <?= isStudentActive('nominate.php') ?> <?= $canNominate ? '' : 'opacity-50 pointer-events-none' ?>">
                    📋 Nominations
                </a>
            </li>
            <li>
                <a href="voting.php" 
                   class="flex items-center gap-3 px-6 py-2 <?= isStudentActive('voting.php') ?> <?= $canVote ? '' : 'opacity-50 pointer-events-none' ?>">
                    🗳️ Vote
                </a>
            </li>
            <li>
                <a href="view_results.php" 
                   class="flex items-center gap-3 px-6 py-2 <?= isStudentActive('view_results.php') ?>">
                    📈 Results
                </a>
            </li>
            <li>
                <a href="notifications.php" 
                   class="flex items-center gap-3 px-6 py-2 <?= isStudentActive('notifications.php') ?>">
                    🔔 Notifications
                </a>
            </li>
        </ul>
    </nav>

    <div class="border-t p-4">
        <a href="../auth/logout.php" 
           class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">
            Logout
        </a>
    </div>
</aside>