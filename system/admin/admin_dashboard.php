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
$teacherObj = new Teacher();

// --- Fetch Data ---
$total_students = $studentObj->countStudents();
$total_teachers = $teacherObj->countTeachers();
$total_positions = $posObj->countPositions();

$schedule = $electionObj->fetchSchedule();
$currentElection = $electionObj->fetchCurrentElection(); // Admin-controlled status

// Initialize $status with a default value
$status = 'No Election Scheduled';
$election_schedule = null;

if ($schedule) {
    // Use admin-controlled status if exists
    if ($currentElection && !empty($currentElection['status'])) {
        $status = $currentElection['status']; // Ongoing, Paused, Ended
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
    default => 'bg-red-600', // For 'No Election Scheduled'
};


// Button states based on admin-controlled election
$startDisabled = ($status === 'Ongoing') ? 'disabled opacity-50 cursor-not-allowed' : '';
$pauseDisabled = ($status !== 'Ongoing') ? 'disabled opacity-50 cursor-not-allowed' : '';
$endDisabled = ($status === 'Ended') ? 'disabled opacity-50 cursor-not-allowed' : '';

// Nomination summary
$nominations = $electionObj->getNominationSummary();

$total_votes = $electionObj->countTotalVotes(); // will be 0
$voted = $electionObj->countVoters();
$voter_turnout = 0; // will show 0% for upcoming election
$leadingArray = []; // no leading candidates yet


$leadingByPosition = [];
if (is_array($leadingArray)) {
    foreach ($leadingArray as $candidate) {
        $positionName = $candidate['position_name'];
        $candidateName = $candidate['candidate_name'];
        $leadingByPosition[$positionName][] = $candidateName;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-red-700">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Dashboard Panel</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 bg-red-100 text-red-700 font-medium">🏠 Overview</a></li>
                <li><a href="../admin/election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🗳️ Election Management</a></li>
                <li><a href="../admin/student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👥 Students</a></li>
                <li><a href="../admin/teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../admin/position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📌 Positions</a></li>
                <li><a href="../admin/nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📋 Nominations</a></li>
                <li><a href="../admin/election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📈 Results</a></li>
                <li><a href="../admin/election/audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../auth/logout.php" class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Dashboard Overview</h2>
                <p class="text-sm text-gray-500">Welcome back, Admin!</p>
            </div>
            <div class="<?= $badgeColor ?> text-white px-4 py-2 rounded-lg font-medium">
                <?= htmlspecialchars($status) ?>
            </div>
        </header>

        <!-- OVERVIEW CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-red-600 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Total Students</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $total_students ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-blue-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Total Teachers</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $total_teachers ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-yellow-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Total Positions</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $total_positions ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-purple-500 hover:shadow-lg transition">
                <p class="text-gray-500 text-sm">Election Status</p>
                <h3 class="text-2xl font-bold text-red-600"><?= htmlspecialchars($status) ?></h3>
            </div>
        </section>

        <!-- ELECTION MANAGEMENT -->
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">🗳️ Election Management</h3>
            <form method="POST" action="../admin/election_action.php" class="flex flex-wrap gap-3 mb-5">
                <button name="action" value="start" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium <?= $startDisabled ?>">Start</button>
                <button name="action" value="pause" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium <?= $pauseDisabled ?>">Pause</button>
                <button name="action" value="end" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium <?= $endDisabled ?>">End</button>
            </form>
            <div class="flex flex-wrap gap-3">
                <a href="../admin/election/manage_schedule.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Manage Schedule</a>
                <a href="../admin/nomination/view_nomination.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">View Nominations</a>
                <a href="../admin/election/view_results.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">View Results</a>
            </div>
        </section>

<!-- NOMINATIONS & VOTING SUMMARY -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

    <!-- Nomination Overview -->
    <section class="bg-white shadow rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">📋 Nomination Overview</h3>
        <table class="w-full text-sm text-left">
            <thead class="border-b font-medium text-gray-600">
                <tr>
                    <th class="py-2">Position</th>
                    <th>Nominees</th>
                    <th>Pending</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($nominations as $n): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2"><?= htmlspecialchars($n['position_name']) ?></td>
                        <td><?= $n['total_nominees'] ?></td>
                        <td><?= $n['pending'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Voting Summary -->
    <section class="bg-white shadow rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">📈 Voting Summary</h3>

        <?php
        // Fetch voter stats for admin view
        $stats = $electionObj->getVoterStats();
        $total_students = $studentObj->countStudents();
        $voted = $stats['voted'] ?? 0;
        $voter_turnout = $total_students > 0 ? round(($voted / $total_students) * 100, 2) : 0;

        // Leading candidates per position
        $leading_candidates = $electionObj->getLeadingCandidates(true);
        if (!is_array($leading_candidates)) $leading_candidates = [];
        ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Voter Turnout</p>
                <h3 class="text-3xl font-bold text-blue-600"><?= $voter_turnout ?>%</h3>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Voters / Total</p>
                <h3 class="text-3xl font-bold"><?= "$voted / $total_students" ?></h3>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Leading Candidates</p>
                <div class="text-red-600 font-bold">
                    <?php if (!empty($leading_candidates)): ?>
                        <?php foreach ($leading_candidates as $lc): ?>
                            <div class="mb-2">
                                <span class="font-semibold"><?= htmlspecialchars($lc['position_name'] ?? 'N/A') ?>:</span>
                                <?= htmlspecialchars($lc['candidate_name'] ?? 'No Candidate') ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span>No votes yet</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>


        <!-- SYSTEM CONTROLS -->
        <section class="bg-white shadow rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">⚙️ System Controls</h3>
            <div class="flex flex-wrap gap-4">
                <a href="../admin/election/export_results.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Export Results</a>
                <a href="../admin/election/audit_log.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Audit Log</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
