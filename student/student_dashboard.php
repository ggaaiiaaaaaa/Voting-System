<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/position.php";
require_once __DIR__ . "/../classes/student.php";

// ✅ Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$electionObj = new Election();
$posObj = new Position();
$studentObj = new Student();

// --- Fetch Student Data ---
$studentData = $studentObj->getStudentById($student_id);
$first = $studentData['first_name'] ?? '';
$last = $studentData['last_name'] ?? '';
$student_full_name = trim("$first $last") ?: 'Student';

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
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-red-700">Student Panel</h1>
            <p class="text-xs text-gray-500 mt-1">Dashboard Panel</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="student_dashboard.php" class="flex items-center gap-3 px-6 py-2 bg-red-100 text-red-700 font-medium">🏠 Overview</a></li>
                <li>
                    <a href="nominate.php" 
                       class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700 <?= $canNominate ? '' : 'opacity-50 pointer-events-none' ?>">
                       📋 Nominations
                    </a>
                </li>
                <li>
                    <a href="voting.php" 
                       class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700 <?= $canVote ? '' : 'opacity-50 pointer-events-none' ?>">
                       🗳️ Vote
                    </a>
                </li>
                <li><a href="view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📈 Results</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../auth/logout.php" class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
<header class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-semibold text-[#D02C4D]">Dashboard Overview</h2>
        <p class="text-sm text-gray-500">Welcome back, <?= htmlspecialchars($student_full_name) ?>!</p>
    </div>
    <div class="flex items-center gap-4">
        <?php include '../includes/notification_dropdown.php'; ?>
        <div class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium">
            Election Status: <?= htmlspecialchars($schedule_status) ?>
        </div>
    </div>
</header>

        <!-- Quick Overview Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-red-600 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Total Nominations</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= count($student_nominations) ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-blue-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Voting Status</p>
                <h3 class="text-2xl font-bold <?= $has_voted ? 'text-green-600' : 'text-gray-800' ?>">
                    <?= $has_voted ? 'Voted' : 'Not Voted' ?>
                </h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-yellow-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Voter Turnout</p>
                <h3 class="text-2xl font-bold text-blue-600"><?= $voter_turnout ?>%</h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-purple-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Total Students Voted</p>
                <h3 class="text-2xl font-bold"><?= $voted ?> / <?= $total_students ?></h3>
            </div>
        </section>

        <!-- My Nominations -->
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">📋 My Nominations</h3>
            <?php if (empty($student_nominations)): ?>
                <p class="text-gray-500">You have no nominations yet.</p>
                <?php if($canNominate): ?>
                    <a href="nominate.php" class="inline-block mt-2 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Nominate Now</a>
                <?php endif; ?>
            <?php else: ?>
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="border-b font-medium text-gray-600">
                        <tr>
                            <th class="py-2 px-2">Position</th>
                            <th class="px-2">Nominee</th>
                            <th class="px-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($student_nominations as $n): 
                            $statusClass = match($n['status'] ?? 'Pending') {
                                'Approved' => 'text-green-600 font-semibold',
                                'Pending' => 'text-yellow-600 font-semibold',
                                'Rejected' => 'text-red-600 font-semibold',
                                default => 'text-gray-600'
                            };
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-2"><?= htmlspecialchars($n['position_name'] ?? 'Unknown Position') ?></td>
                            <td class="py-2 px-2"><?= htmlspecialchars($n['nominee_name'] ?? 'N/A') ?></td>
                            <td class="px-2 <?= $statusClass ?>"><?= htmlspecialchars($n['status'] ?? 'Pending') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <!-- Current Leaders -->
        <?php if (!empty($leading_candidates)): ?>
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">📈 Current Leaders</h3>
            <table class="w-full text-sm text-left border-collapse">
                <thead class="border-b font-medium text-gray-600">
                    <tr>
                        <th class="py-2 px-2">Position</th>
                        <th class="px-2">Leading Candidate</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php 
                    $colors = ['border-red-600','border-blue-500','border-yellow-500','border-purple-500']; 
                    $i = 0;
                    foreach ($leading_candidates as $lc): ?>
                        <tr class="border-b hover:bg-gray-50 <?= $colors[$i % count($colors)] ?> border-l-4">
                            <td class="py-2 px-2"><?= htmlspecialchars($lc['position_name'] ?? 'N/A') ?></td>
                            <td class="px-2"><?= htmlspecialchars($lc['candidate_name'] ?? 'No Candidate') ?></td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
