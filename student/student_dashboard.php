<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/position.php";
require_once __DIR__ . "/../classes/student.php";

// ✅ Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$electionObj = new Election();
$posObj = new Position();
$studentObj = new Student();

// --- Fetch Student Data ---
$studentData = $studentObj->getStudentById($student_id);
$fullname = $studentData['fullname'] ?? '';
// --- Current Election Status (admin-controlled) ---
$schedule_status = $electionObj->getAdminControlledStatus(); 


$canNominate = $canVote = false;

switch($schedule_status) {
    case 'Ongoing':
        $canNominate = true;
        $has_voted = $electionObj->hasStudentVoted($student_id);
        $canVote = !$has_voted;
        break;
    case 'Paused':
        $canNominate = false;
        $canVote = false;
        break;
    case 'Ended':
        // Redirect to results page
        header("Location: view_results.php");
        exit;
    case 'Upcoming':
    default:
        $canNominate = false;
        $canVote = false;
        break;
}

// --- Voting Status ---
$has_voted = $electionObj->hasStudentVoted($student_id);

// --- Student Nominations ---
$student_nominations = $electionObj->getStudentNominations($student_id);

// --- Voter Statistics ---
$stats = $electionObj->getVoterStats();
$total_students = $studentObj->countStudents();
$voted = $stats['voted'] ?? 0;
$voter_turnout = $total_students > 0 ? round(($voted / $total_students) * 100, 2) : 0;

// --- Leading Candidates (fresh if new cycle) ---
$leading_candidates = $electionObj->getLeadingCandidates(true);
if (!is_array($leading_candidates)) {
    $leading_candidates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
<header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
    <div>
        <h2 class="text-3xl font-bold text-white drop-shadow-lg">Dashboard Overview</h2>
        <p class="text-sm text-gray-300 mt-1">Welcome back, <?= htmlspecialchars($fullname) ?>!</p>
    </div>
    <div class="flex items-center gap-4">
        <?php include '../includes/notification_dropdown.php'; ?>
        <div class="bg-red-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
            <?= htmlspecialchars($schedule_status) ?>
        </div>
    </div>
</header>

        <!-- OVERVIEW CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-fade-in-up">
            <div class="bg-gradient-to-br from-red-500 to-red-700 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-red-100 text-sm font-medium">Total Nominations</p>
                <h3 class="text-3xl font-bold text-white mt-2"><?= count($student_nominations) ?></h3>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-blue-100 text-sm font-medium">Voting Status</p>
                <h3 class="text-2xl font-bold text-white mt-2"><?= $has_voted ? 'Voted' : 'Not Voted' ?></h3>
            </div>
            <div class="bg-gradient-to-br from-yellow-500 to-orange-600 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-yellow-100 text-sm font-medium">Voter Turnout</p>
                <h3 class="text-3xl font-bold text-white mt-2"><?= $voter_turnout ?>%</h3>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-purple-100 text-sm font-medium">Total Students Voted</p>
                <h3 class="text-2xl font-bold text-white mt-2"><?= "$voted / $total_students" ?></h3>
            </div>
        </section>

        <!-- My Nominations -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 mb-10 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                My Nominations
            </h3>
            <?php if (empty($student_nominations)): ?>
                <p class="text-gray-300">You have no nominations yet.</p>
                <?php if($canNominate): ?>
                    <a href="nominate.php" class="inline-block mt-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-nlue-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">Nominate Now</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="border-b border-white/20">
                            <tr>
                                <th class="py-3 text-white font-semibold">Position</th>
                                <th class="py-3 text-white font-semibold">Nominee</th>
                                <th class="py-3 text-white font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-200">
                            <?php foreach ($student_nominations as $n): 
                                $statusClass = match($n['status'] ?? 'Pending') {
                                    'Approved' => 'text-green-400 font-medium',
                                    'Pending' => 'text-yellow-400 font-medium',
                                    'Rejected' => 'text-red-400 font-medium',
                                    default => 'text-gray-400'
                                };
                            ?>
                                <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                    <td class="py-3 text-white/90"><?= htmlspecialchars($n['position_name'] ?? 'Unknown Position') ?></td>
                                    <td class="py-3 text-white/90"><?= htmlspecialchars($n['nominee_name'] ?? 'N/A') ?></td>
                                    <td class="py-3 <?= $statusClass ?>"><?= htmlspecialchars($n['status'] ?? 'Pending') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Current Leaders -->
        <?php if (!empty($leading_candidates)): ?>
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 mb-10 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                Current Leaders
            </h3>
            <div class="space-y-3">
                <?php foreach ($leading_candidates as $lc): ?>
                    <div class="flex justify-between items-center bg-white/10 p-3 rounded-xl border border-white/20 hover:bg-white/20 transition-colors">
                        <span class="font-semibold text-sm text-white/90"><?= htmlspecialchars($lc['position_name'] ?? 'N/A') ?>:</span>
                        <span class="text-yellow-400 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                            <?= htmlspecialchars($lc['candidate_name'] ?? 'No Candidate') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </main>
</div>
<script>
// Poll for new notifications every 30 seconds
setInterval(function() {
    fetch('../includes/get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                // Update notification badge
                document.querySelector('.notification-badge').textContent = data.count;
                document.querySelector('.notification-badge').classList.remove('hidden');
            }
        });
}, 30000);
</script>
</body>
</html>
