<?php
session_start();
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../classes/student.php"; // Optional if you want admin info

$electionObj = new Election();

// --- Check election status ---
// If you want admin to see results even before election ends, you can skip this
$election_status = $electionObj->getAdminControlledStatus(); // Ongoing, Paused, Ended, Upcoming
if ($election_status !== 'Ended') {
    $_SESSION['error'] = "Election results are not available at this time. Current status: $election_status.";
    header("Location: ../admin_dashboard.php");
    exit;
}

// Fetch results
$results = $electionObj->fetchResults();

// Deduplicate results by position & candidate
$uniqueResults = [];
foreach ($results as $r) {
    $pos = $r['position_name'];
    $cand = $r['candidate_name'];
    if (!isset($uniqueResults[$pos][$cand])) {
        $uniqueResults[$pos][$cand] = $r;
    }
}

// Determine winners per position
foreach ($uniqueResults as $pos => &$cands) {
    $votes = array_column($cands, 'votes');
    $maxVotes = max($votes);

    foreach ($cands as &$r) {
        $r['status'] = ($r['votes'] == $maxVotes) ? 'Winner' : 'Loser';
    }
}
unset($cands, $r);

// Prepare chart data
$chartData = [];
foreach ($uniqueResults as $pos => $cands) {
    $chartData[$pos] = [
        'labels' => array_column($cands, 'candidate_name'),
        'votes'  => array_column($cands, 'votes')
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Admin Panel</h1>
            <p class="text-xs text-gray-500 mt-1">Election Results</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🏠 Overview</a></li>
                <li><a href="../election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🗳️ Election Management</a></li>
                <li><a href="../student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👥 Students</a></li>
                <li><a href="../teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📌 Positions</a></li>
                <li><a href="../nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📋 Nominations</a></li>
                <li><a href="view_results.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">📈 Results</a></li>
                <li><a href="../election/view_reports.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📊 Reports</a></li>
                <li><a href="audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="mb-8">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">📊 Election Results</h2>
            <p class="text-sm text-gray-500">Final votes per position and winner summary.</p>
            <a href="export_results.php" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-4 py-2 rounded-lg font-medium mt-2 inline-block">📤 Export Results</a>
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                    🖨️ Print Results
                </button>
        </header>

        <?php if (!empty($uniqueResults)): ?>
            <!-- Results Table -->
            <section class="bg-white shadow rounded-xl p-6 mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border border-gray-200">
                        <thead class="bg-[#FEEAEA] text-[#D02C4D]">
                            <tr>
                                <th class="px-4 py-3">Position</th>
                                <th class="px-4 py-3">Candidate</th>
                                <th class="px-4 py-3">Votes</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <?php foreach ($uniqueResults as $pos => $cands): ?>
                                <?php foreach ($cands as $r): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3"><?= htmlspecialchars($r['position_name']) ?></td>
                                        <td class="px-4 py-3"><?= htmlspecialchars($r['candidate_name']) ?></td>
                                        <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($r['votes']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold 
                                                <?= $r['status'] === 'Winner' 
                                                    ? 'bg-green-100 text-green-700' 
                                                    : 'bg-gray-100 text-gray-600' ?>">
                                                <?= htmlspecialchars($r['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php else: ?>
            <p class="text-gray-500">No results available.</p>
        <?php endif; ?>
    </main>
</div>

<?php if (!empty($uniqueResults)): ?>
<script>
// Chart JS rendering
const chartData = <?= json_encode($chartData) ?>;
for (const [pos, data] of Object.entries(chartData)) {
    const ctx = document.getElementById("chart_" + md5(pos));
    if (!ctx) continue;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Votes',
                data: data.votes,
                backgroundColor: '#D02C4D',
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
}

// Simple MD5 alternative for chart IDs
function md5(str){
    return Array.from(new TextEncoder().encode(str))
        .reduce((hash,b)=>{hash=((hash<<5)-hash)+b;return hash&hash;},0);
}
</script>
<?php endif; ?>
</body>
</html>
