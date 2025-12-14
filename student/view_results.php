<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/student.php";

// Redirect if not logged in or not student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$studentObj = new Student();
$electionObj = new Election();

// --- Check admin-controlled election status ---
$election_status = $electionObj->getAdminControlledStatus(); // Ongoing, Paused, Ended, Upcoming

if ($election_status !== 'Ended') {
    $_SESSION['error'] = "Election results are not available at this time. Current status: $election_status.";
    header("Location: student_dashboard.php");
    exit;
}

// Fetch results using fixed fetchResults()
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
    // Get max votes for this position
    $votes = array_column($cands, 'votes');
    $maxVotes = max($votes);

    // Only candidates with max votes are winners
    foreach ($cands as &$r) {
        $r['status'] = ($r['votes'] == $maxVotes) ? 'Winner' : 'Loser';
    }
}
unset($cands, $r); // break reference

// Prepare chart data
$chartData = [];
foreach ($uniqueResults as $pos => $cands) {
    $chartData[$pos] = [
        'labels' => array_column($cands, 'candidate_name'),
        'votes'  => array_column($cands, 'votes')
    ];
}


// Get student full name for sidebar
$studentData = $studentObj->getStudentById($student_id);
$student_full_name = '';
if (!empty($studentData)) {
    $first = $studentData['first_name'] ?? '';
    $last = $studentData['last_name'] ?? '';
    $student_full_name = trim("$first $last");
}
if (empty($student_full_name)) $student_full_name = 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg"> Election Results</h2>
                <p class="text-sm text-gray-300 mt-1">Final votes per position and winner summary.</p>
            </div>
            <div class="flex gap-4">
                <a href="../admin/election/export_results.php" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">📤 Export Results</a>
                <button onclick="window.print()" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">🖨️ Print Results</button>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if (isset($success) && $success): ?>
            <div class="mb-6 bg-green-500/20 backdrop-blur-sm border border-green-500/30 text-green-300 px-6 py-4 rounded-xl shadow-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif (isset($error) && $error): ?>
            <div class="mb-6 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 px-6 py-4 rounded-xl shadow-lgmb-6 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 px-6 py-4 rounded-xl shadow-lg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($uniqueResults)): ?>
            <!-- RESULTS TABLE -->
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b border-white/20">
                        <tr>
                            <th class="py-3 text-white font-semibold">Position</th>
                            <th class="py-3 text-white font-semibold">Candidate</th>
                            <th class="py-3 text-white font-semibold">Votes</th>
                            <th class="py-3 text-white font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-200">
                        <?php foreach ($uniqueResults as $pos => $cands): ?>
                            <?php foreach ($cands as $r): ?>
                                <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                    <td class="py-3 text-white/90"><?= htmlspecialchars($r['position_name']) ?></td>
                                    <td class="py-3 text-white/90"><?= htmlspecialchars($r['candidate_name']) ?></td>
                                    <td class="py-3 text-white/90 font-semibold"><?= htmlspecialchars($r['votes']) ?></td>
                                    <td class="py-3">
                                        <?php if ($r['status'] === 'Winner'): ?>
                                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold border border-green-500/30">Winner</span>
                                        <?php else: ?>
                                            <span class="bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold border border-gray-500/30">Loser</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php else: ?>
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <span class="text-gray-400 text-sm">No results available</span>
                </div>
            </section>
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
