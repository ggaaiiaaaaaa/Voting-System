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
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="mb-8">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">📊 Election Results</h2>
            <p class="text-sm text-gray-500">Final votes per position and winner summary.</p>
        </header>

        <?php if (!empty($uniqueResults)): ?>
            <!-- Results Table -->
            <section class="bg-white shadow rounded-xl p-6 mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border border-gray-200">
                        <thead class="bg-red-100 text-red-700">
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
