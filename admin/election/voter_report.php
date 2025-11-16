<?php
session_start();
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../classes/student.php";

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$electionObj = new Election();
$studentObj = new Student();

// Get voter statistics
$stats = $electionObj->getVoterStats();
$total_students = $studentObj->countStudents();
$voted = $stats['voted'] ?? 0;
$not_voted = $total_students - $voted;
$voter_turnout = $total_students > 0 ? round(($voted / $total_students) * 100, 2) : 0;

// Get voters list (students who voted)
$voters_list = $electionObj->getVotersList();
if (!is_array($voters_list)) $voters_list = [];

// Get non-voters list (students who haven't voted)
$non_voters_list = $studentObj->getNonVoters();
if (!is_array($non_voters_list)) $non_voters_list = [];

// Get voting timeline (votes per hour/day)
$voting_timeline = $electionObj->getVotingTimeline();
if (!is_array($voting_timeline)) $voting_timeline = [];

// Get voter statistics by grade level
$voters_by_grade = $studentObj->getVotersByGrade();
if (!is_array($voters_by_grade)) $voters_by_grade = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Report - Election System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-red-700">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Voter Report</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../../admin/admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🏠 Overview</a></li>
                <li><a href="../../admin/election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🗳️ Election Management</a></li>
                <li><a href="../../admin/student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👥 Students</a></li>
                <li><a href="../../admin/teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../../admin/position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📌 Positions</a></li>
                <li><a href="../../admin/nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📋 Nominations</a></li>
                <li><a href="../../admin/election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📈 Results</a></li>
                <li><a href="view_reports.php" class="flex items-center gap-3 px-6 py-2 bg-red-100 text-red-700 font-medium">📊 Reports</a></li>
                <li><a href="../../admin/election/audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-blue-700">👥 Voter Report</h2>
                    <p class="text-sm text-gray-500 mt-1">Comprehensive voter turnout analysis</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">🖨️ Print</button>
                    <a href="export_voter_pdf.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">📄 Export PDF</a>
                </div>
            </div>
        </header>

        <!-- QUICK STATS -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow rounded-xl p-6">
                <p class="text-blue-100 text-sm mb-1">Total Students</p>
                <h3 class="text-4xl font-bold"><?= $total_students ?></h3>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white shadow rounded-xl p-6">
                <p class="text-green-100 text-sm mb-1">Students Voted</p>
                <h3 class="text-4xl font-bold"><?= $voted ?></h3>
            </div>
            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white shadow rounded-xl p-6">
                <p class="text-red-100 text-sm mb-1">Not Voted</p>
                <h3 class="text-4xl font-bold"><?= $not_voted ?></h3>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow rounded-xl p-6">
                <p class="text-purple-100 text-sm mb-1">Turnout Rate</p>
                <h3 class="text-4xl font-bold"><?= $voter_turnout ?>%</h3>
            </div>
        </section>

        <!-- VISUAL ANALYTICS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <!-- Turnout Chart -->
            <section class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Voter Turnout Distribution</h3>
                <canvas id="turnoutChart" height="250"></canvas>
            </section>

            <!-- Grade Level Chart -->
            <section class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Turnout by Grade Level</h3>
                <canvas id="gradeChart" height="250"></canvas>
            </section>
        </div>

        <!-- Voting Timeline -->
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">📅 Voting Timeline</h3>
            <canvas id="timelineChart" height="150"></canvas>
        </section>

        <!-- DETAILED TABLES -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <!-- Voters List -->
            <section class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">✅ Students Who Voted (<?= count($voters_list) ?>)</h3>
                <div class="max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left p-3 font-semibold text-gray-600">Student ID</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Name</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Grade</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Voted At</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php foreach ($voters_list as $voter): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3"><?= htmlspecialchars($voter['student_id'] ?? 'N/A') ?></td>
                                    <td class="p-3"><?= htmlspecialchars($voter['name'] ?? 'N/A') ?></td>
                                    <td class="p-3"><?= htmlspecialchars($voter['grade_level'] ?? 'N/A') ?></td>
                                    <td class="p-3 text-xs"><?= isset($voter['voted_at']) ? date('M d, Y H:i', strtotime($voter['voted_at'])) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($voters_list)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">No voters yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Non-Voters List -->
            <section class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">❌ Students Who Haven't Voted (<?= count($non_voters_list) ?>)</h3>
                <div class="max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left p-3 font-semibold text-gray-600">Student ID</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Name</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Grade</th>
                                <th class="text-left p-3 font-semibold text-gray-600">Section</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php foreach ($non_voters_list as $non_voter): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3"><?= htmlspecialchars($non_voter['student_id'] ?? 'N/A') ?></td>
                                    <td class="p-3"><?= htmlspecialchars($non_voter['firstname'] . ' ' . $non_voter['lastname']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($non_voter['grade_level'] ?? 'N/A') ?></td>
                                    <td class="p-3"><?= htmlspecialchars($non_voter['section'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($non_voters_list)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">All students have voted!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Grade Level Statistics -->
        <section class="bg-white shadow rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">📚 Turnout by Grade Level</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3 font-semibold text-gray-600">Grade Level</th>
                            <th class="text-center p-3 font-semibold text-gray-600">Total Students</th>
                            <th class="text-center p-3 font-semibold text-gray-600">Voted</th>
                            <th class="text-center p-3 font-semibold text-gray-600">Not Voted</th>
                            <th class="text-center p-3 font-semibold text-gray-600">Turnout %</th>
                            <th class="text-left p-3 font-semibold text-gray-600">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($voters_by_grade as $grade): ?>
                            <?php 
                                $grade_turnout = $grade['total'] > 0 ? round(($grade['voted'] / $grade['total']) * 100, 1) : 0;
                            ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-medium">Grade <?= htmlspecialchars($grade['grade_level']) ?></td>
                                <td class="p-3 text-center"><?= $grade['total'] ?></td>
                                <td class="p-3 text-center text-green-600 font-semibold"><?= $grade['voted'] ?></td>
                                <td class="p-3 text-center text-red-600 font-semibold"><?= $grade['not_voted'] ?></td>
                                <td class="p-3 text-center font-bold"><?= $grade_turnout ?>%</td>
                                <td class="p-3">
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-blue-600 h-4 rounded-full" style="width: <?= $grade_turnout ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script>
// Turnout Doughnut Chart
const turnoutCtx = document.getElementById('turnoutChart').getContext('2d');
new Chart(turnoutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Voted', 'Not Voted'],
        datasets: [{
            data: [<?= $voted ?>, <?= $not_voted ?>],
            backgroundColor: ['#10B981', '#EF4444'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = <?= $total_students ?>;
                        const value = context.parsed;
                        const percentage = ((value / total) * 100).toFixed(1);
                        return context.label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Grade Level Bar Chart
<?php
$grade_labels = [];
$grade_voted = [];
$grade_not_voted = [];
foreach ($voters_by_grade as $g) {
    $grade_labels[] = 'Grade ' . $g['grade_level'];
    $grade_voted[] = $g['voted'];
    $grade_not_voted[] = $g['not_voted'];
}
?>
const gradeCtx = document.getElementById('gradeChart').getContext('2d');
new Chart(gradeCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($grade_labels) ?>,
        datasets: [
            {
                label: 'Voted',
                data: <?= json_encode($grade_voted) ?>,
                backgroundColor: '#10B981',
                borderRadius: 5
            },
            {
                label: 'Not Voted',
                data: <?= json_encode($grade_not_voted) ?>,
                backgroundColor: '#EF4444',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { 
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { stacked: false }
        }
    }
});

// Timeline Chart
<?php
$timeline_labels = [];
$timeline_votes = [];
foreach ($voting_timeline as $t) {
    $timeline_labels[] = isset($t['time_period']) ? $t['time_period'] : 'N/A';
    $timeline_votes[] = $t['vote_count'] ?? 0;
}
?>
const timelineCtx = document.getElementById('timelineChart').getContext('2d');
new Chart(timelineCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($timeline_labels) ?>,
        datasets: [{
            label: 'Votes Cast',
            data: <?= json_encode($timeline_votes) ?>,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: { 
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>

</body>
</html>