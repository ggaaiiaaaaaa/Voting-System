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

// Get report data
$total_students = $studentObj->countStudents();
$stats = $electionObj->getVoterStats();
$voted = $stats['voted'] ?? 0;
$not_voted = $total_students - $voted;
$voter_turnout = $total_students > 0 ? round(($voted / $total_students) * 100, 2) : 0;

// Get vote distribution
$vote_distribution = $electionObj->getVoteDistribution();
if (!is_array($vote_distribution)) $vote_distribution = [];

// Get leading candidates
$leading_candidates = $electionObj->getLeadingCandidates(true);
if (!is_array($leading_candidates)) $leading_candidates = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Election System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-red-700">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Reports Panel</p>
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
            <h2 class="text-3xl font-bold text-red-700">📊 Election Reports</h2>
            <p class="text-sm text-gray-500 mt-1">Comprehensive election analytics and reports</p>
        </header>

        <!-- QUICK STATS -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Voter Turnout</p>
                <h3 class="text-3xl font-bold text-blue-600"><?= $voter_turnout ?>%</h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Voters</p>
                <h3 class="text-3xl font-bold text-green-600"><?= $voted ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-red-500">
                <p class="text-gray-500 text-sm">Not Voted</p>
                <h3 class="text-3xl font-bold text-red-600"><?= $not_voted ?></h3>
            </div>
            <div class="bg-white shadow rounded-xl p-5 border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm">Total Students</p>
                <h3 class="text-3xl font-bold text-purple-600"><?= $total_students ?></h3>
            </div>
        </section>

<!--         <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <span class="text-2xl">👥</span> Voter Report
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Detailed voter turnout analysis</p>
                    </div>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li>✓ Voter participation by grade level</li>
                    <li>✓ Voting timeline analysis</li>
                    <li>✓ Non-voter list</li>
                </ul>
                <a href="voter_report.php" class="block text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition">
                    View Report
                </a>
            </div>

            <div class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <span class="text-2xl">🎯</span> Candidate Report
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Performance analysis per candidate</p>
                    </div>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li>✓ Vote count per candidate</li>
                    <li>✓ Win/Loss margins</li>
                    <li>✓ Comparative analysis</li>
                </ul>
                <a href="candidate_report.php" class="block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition">
                    View Report
                </a>
            </div>

            <div class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <span class="text-2xl">📌</span> Position Report
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Statistics by position</p>
                    </div>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li>✓ Total votes per position</li>
                    <li>✓ Candidate distribution</li>
                    <li>✓ Competition analysis</li>
                </ul>
                <a href="position_report.php" class="block text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-medium transition">
                    View Report
                </a>
            </div>

            <div class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <span class="text-2xl">📄</span> Summary Report
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Complete election overview</p>
                    </div>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li>✓ Executive summary</li>
                    <li>✓ Winners declaration</li>
                    <li>✓ Printable format</li>
                </ul>
                <a href="summary_report.php" class="block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                    View Report
                </a>
            </div>
        </section> -->

        <!-- VISUAL ANALYTICS -->
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-gray-700 mb-6">📈 Visual Analytics</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Turnout Chart -->
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-3">Voter Participation</h4>
                    <canvas id="turnoutChart" height="250"></canvas>
                </div>

                <!-- Position Distribution Chart -->
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-3">Votes by Position</h4>
                    <canvas id="positionChart" height="250"></canvas>
                </div>
            </div>
        </section>

        <!-- LEADING CANDIDATES -->
        <section class="bg-white shadow rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">🏆 Current Leaders</h3>
            <?php if (!empty($leading_candidates)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($leading_candidates as $lc): ?>
                        <div class="bg-gradient-to-r from-red-50 to-red-100 p-4 rounded-lg border border-red-200">
                            <p class="text-xs text-gray-600 font-semibold uppercase"><?= htmlspecialchars($lc['position_name'] ?? 'N/A') ?></p>
                            <p class="text-lg font-bold text-red-700 mt-1"><?= htmlspecialchars($lc['candidate_name'] ?? 'No Candidate') ?></p>
                            <?php if (isset($lc['vote_count'])): ?>
                                <p class="text-sm text-gray-600 mt-1"><?= $lc['vote_count'] ?> votes</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No votes have been cast yet.</p>
            <?php endif; ?>
        </section>

        <!-- EXPORT OPTIONS -->
        <section class="bg-white shadow rounded-xl p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">💾 Export Options</h3>
            <div class="flex flex-wrap gap-4">
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                    🖨️ Print Report
                </button>
                <!-- <a href="export_pdf.php" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                    📄 Export as PDF
                </a>
                <a href="export_excel.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                    📊 Export as Excel
                </a> -->
            </div>
        </section>
    </main>
</div>

<script>
// Turnout Pie Chart
const turnoutCtx = document.getElementById('turnoutChart').getContext('2d');
new Chart(turnoutCtx, {
    type: 'pie',
    data: {
        labels: ['Voted', 'Not Voted'],
        datasets: [{
            data: [<?= $voted ?>, <?= $not_voted ?>],
            backgroundColor: ['#10B981', '#EF4444'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
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

// Position Distribution Bar Chart
<?php
$positions = [];
$voteCounts = [];
foreach ($vote_distribution as $vd) {
    $positions[] = $vd['position_name'] ?? 'Unknown';
    $voteCounts[] = $vd['total_votes'] ?? 0;
}
?>
const positionCtx = document.getElementById('positionChart').getContext('2d');
new Chart(positionCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($positions) ?>,
        datasets: [{
            label: 'Total Votes',
            data: <?= json_encode($voteCounts) ?>,
            backgroundColor: '#DC2626',
            borderRadius: 8,
            barThickness: 80
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
</script>

</body>
</html>