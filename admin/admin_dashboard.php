<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/position.php";
require_once __DIR__ . "/../classes/student.php";
require_once __DIR__ . "/../classes/teacher.php";

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Instantiate objects
$electionObj = new Election();
$posObj = new Position();
$studentObj = new Student();

// --- Fetch Data ---
$total_students = $studentObj->countStudents();
$total_positions = $posObj->countPositions();

$schedule = $electionObj->fetchSchedule();
$currentElection = $electionObj->fetchCurrentElection();

// Initialize $status with a default value
$status = 'No Election Scheduled';
$election_schedule = null;

if ($schedule) {
    if ($currentElection && !empty($currentElection['status'])) {
        $status = $currentElection['status'];
    } else {
        $now = date('Y-m-d H:i:s');
        if ($now < $schedule['start_date']) $status = 'Upcoming';
        elseif ($now >= $schedule['start_date'] && $now <= $schedule['end_date']) $status = 'Active';
        else $status = 'Completed';
    }

    $election_schedule = [
        'id' => $schedule['id'],
        'start' => date('M d, Y H:i', strtotime($schedule['start_date'])),
        'end' => date('M d, Y H:i', strtotime($schedule['end_date'])),
        'status' => $status
    ];
}

// Badge color
$badgeColor = match($status) {
    'Ongoing' => 'bg-green-600',
    'Paused' => 'bg-yellow-500',
    'Ended' => 'bg-gray-600',
    'Upcoming' => 'bg-blue-600',
    'Active' => 'bg-green-500',
    default => 'bg-red-600',
};

// Button states
$startDisabled = ($status === 'Ongoing') ? 'disabled opacity-50 cursor-not-allowed' : '';
$pauseDisabled = ($status !== 'Ongoing') ? 'disabled opacity-50 cursor-not-allowed' : '';
$endDisabled = ($status === 'Ended') ? 'disabled opacity-50 cursor-not-allowed' : '';

// Nomination summary
$nominations = $electionObj->getNominationSummary();

// Voter stats
$stats = $electionObj->getVoterStats();
$voted = $stats['voted'] ?? 0;
$voter_turnout = $total_students > 0 ? round(($voted / $total_students) * 100, 2) : 0;

// Leading candidates per position
$leading_candidates = $electionObj->getLeadingCandidates(true);
if (!is_array($leading_candidates)) $leading_candidates = [];

// Get vote distribution data for chart
$vote_distribution = $electionObj->getVoteDistribution();
if (!is_array($vote_distribution)) $vote_distribution = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        .animate-fade-in-up {
            animation: fadeInUp 1s ease-out;
        }
        .animate-fade-in-up:nth-child(1) { animation-delay: 0.1s; }
        .animate-fade-in-up:nth-child(2) { animation-delay: 0.2s; }
        .animate-fade-in-up:nth-child(3) { animation-delay: 0.3s; }
        .animate-fade-in-up:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
<header class="flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
    <div>
        <h2 class="text-3xl font-bold text-white drop-shadow-lg">Dashboard Overview</h2>
        <p class="text-sm text-gray-300 mt-1">Welcome back, Admin!</p>
    </div>
    <div class="flex items-center gap-4">
        <?php include '../includes/notification_dropdown.php'; ?>
        <div class="<?= $badgeColor ?> text-white px-6 py-3 rounded-full font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
            <?= htmlspecialchars($status) ?>
        </div>
    </div>
</header>

        <!-- OVERVIEW CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-fade-in-up">
            <div class="bg-gradient-to-br from-red-500 to-red-700 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-red-100 text-sm font-medium">Total Students</p>
                <h3 class="text-3xl font-bold text-white mt-2"><?= $total_students ?></h3>
            </div>
            <div class="bg-gradient-to-br from-yellow-500 to-orange-600 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 8v5z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-yellow-100 text-sm font-medium">Total Positions</p>
                <h3 class="text-3xl font-bold text-white mt-2"><?= $total_positions ?></h3>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 shadow-2xl rounded-2xl p-6 border border-white/20 hover:shadow-3xl hover:scale-105 transition-all duration-300 transform">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-purple-100 text-sm font-medium">Election Status</p>
                <h3 class="text-2xl font-bold text-white mt-2"><?= htmlspecialchars($status) ?></h3>
            </div>
        </section>

        <!-- ELECTION MANAGEMENT -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 mb-10 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Election Management
            </h3>
            <form method="POST" action="../admin/election_action.php" class="flex flex-wrap gap-4 mb-6">
                <button name="action" value="start" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 <?= $startDisabled ?>">Start Election</button>
                <!-- <button name="action" value="pause" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 <?= $pauseDisabled ?>">Pause</button> -->
                <button name="action" value="end" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 <?= $endDisabled ?>">End Election</button>
            </form>
            <div class="flex flex-wrap gap-4">
                <a href="../admin/election/manage_schedule.php" class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Manage Schedule
                </a>
                <a href="../admin/nomination/view_nomination.php" class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View Nominations
                </a>
                <a href="../admin/election/view_results.php" class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    View Results
                </a>
            </div>
        </section>

        <!-- NOMINATIONS & VOTING SUMMARY -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

            <!-- Nomination Overview -->
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Nomination Overview
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="border-b border-white/20">
                            <tr>
                                <th class="py-3 text-white font-semibold">Position</th>
                                <th class="py-3 text-white font-semibold">Nominees</th>
                                <th class="py-3 text-white font-semibold">Pending</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-200">
                            <?php foreach ($nominations as $n): ?>
                                <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                    <td class="py-3 text-white/90"><?= htmlspecialchars($n['position_name']) ?></td>
                                    <td class="py-3 text-green-400 font-medium"><?= $n['total_nominees'] ?></td>
                                    <td class="py-3 text-yellow-400 font-medium"><?= $n['pending'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Voting Summary with Stats -->
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Voting Summary
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white/10 rounded-xl p-4 text-center border border-white/20">
                        <div class="bg-blue-500/20 p-3 rounded-full w-fit mx-auto mb-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <p class="text-white/70 text-sm mb-1">Voter Turnout</p>
                        <h3 class="text-3xl font-bold text-blue-400"><?= $voter_turnout ?>%</h3>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4 text-center border border-white/20">
                        <div class="bg-green-500/20 p-3 rounded-full w-fit mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-white/70 text-sm mb-1">Voters / Total</p>
                        <h3 class="text-2xl font-bold text-green-400"><?= "$voted / $total_students" ?></h3>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4 text-center border border-white/20">
                        <div class="bg-red-500/20 p-3 rounded-full w-fit mx-auto mb-3">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <p class="text-white/70 text-sm mb-1">Not Voted</p>
                        <h3 class="text-2xl font-bold text-red-400"><?= $total_students - $voted ?></h3>
                    </div>
                </div>

                <!-- Leading Candidates -->
                <div class="mb-4">
                    <p class="text-white/90 text-sm mb-4 font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        Leading Candidates
                    </p>
                    <div class="space-y-3">
                        <?php if (!empty($leading_candidates)): ?>
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
                        <?php else: ?>
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <span class="text-gray-400 text-sm">No votes yet</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- VOTING STATISTICS GRAPH -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 mb-10 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Voting Statistics Graphs
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <h4 class="text-white font-semibold mb-4">Voter Turnout</h4>
                    <canvas id="voterTurnoutChart" width="50" height="50"></canvas>
                </div>
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <h4 class="text-white font-semibold mb-4">Votes by Position</h4>
                    <canvas id="votesByPositionChart" width="50" height="50"></canvas>
                </div>
            </div>
        </section>

        <!-- SYSTEM CONTROLS -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                System Controls
            </h3>
            <div class="flex flex-wrap gap-4">
                <a href="../admin/election/export_results.php" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Results
                </a>
                <a href="../admin/election/audit_log.php" class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Audit Log
                </a>
            </div>
        </section>
    </main>
</div>

<script>
// Voter Turnout Doughnut Chart
const voterTurnoutCtx = document.getElementById('voterTurnoutChart').getContext('2d');
new Chart(voterTurnoutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Voted', 'Not Voted'],
        datasets: [{
            data: [<?= $voted ?>, <?= $total_students - $voted ?>],
            backgroundColor: ['#3B82F6', '#E5E7EB'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Votes by Position Bar Chart
<?php
$positions = [];
$voteCounts = [];
foreach ($vote_distribution as $vd) {
    $positions[] = $vd['position_name'] ?? 'Unknown';
    $voteCounts[] = $vd['total_votes'] ?? 0;
}
?>
const votesByPositionCtx = document.getElementById('votesByPositionChart').getContext('2d');
new Chart(votesByPositionCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($positions) ?>,
        datasets: [{
            label: 'Total Votes',
            data: <?= json_encode($voteCounts) ?>,
            backgroundColor: '#DC2626',
            borderRadius: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

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