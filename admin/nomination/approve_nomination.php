<?php
require_once __DIR__ . "/../../classes/nomination.php";
$nomObj = new Nomination();

if (!isset($_GET['id'])) {
    header("Location: view_nomination.php");
    exit;
}

$nom_id = trim(htmlspecialchars($_GET['id']));
$nomData = $nomObj->fetchNomination($nom_id);

if (!$nomData) {
    exit("<p>Nomination not found. <a href='view_nomination.php'>Back</a></p>");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "approve") {
        $nomObj->approveNomination($nom_id);
        $message = "Nomination approved!";
    } elseif ($action === "reject") {
        $nomObj->rejectNomination($nom_id);
        $message = "Nomination rejected!";
    }

    // Refresh nomination data
    $nomData = $nomObj->fetchNomination($nom_id);
        
    header("Location: view_nomination.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Nomination</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Nomination Management</p>
        </div>

        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🏠 Overview</a></li>
                <li><a href="../election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🗳️ Election Management</a></li>
                <li><a href="../student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👥 Students</a></li>
                <li><a href="../teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📌 Positions</a></li>
                <li><a href="view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📋 Nominations</a></li>
                <li><a href="manage_nomination_phase.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚡ Manage Phase</a></li>
                <li><a href="../election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📈 Results</a></li>
                <li><a href="../election/audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>

        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="mb-6">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">Approve Nomination</h2>
            <p class="text-sm text-gray-500">Review and approve or reject a student nomination.</p>
        </header>

        <div class="bg-white shadow rounded-xl p-6 max-w-3xl">
            <?php if ($message): ?>
                <p class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <p class="mb-2"><strong>Nominator:</strong> <?= htmlspecialchars($nomData['nominator_name']) ?></p>
            <p class="mb-2"><strong>Nominee:</strong> <?= htmlspecialchars($nomData['nominee_name']) ?></p>
            <p class="mb-2"><strong>Position:</strong> <?= htmlspecialchars($nomData['position_name']) ?></p>
            <p class="mb-4"><strong>Status:</strong> 
                <span class="px-2 py-1 text-xs rounded-full <?= $nomData['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($nomData['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                    <?= htmlspecialchars($nomData['status']) ?>
                </span>
            </p>

            <form method="post" class="flex gap-3">
                <button type="submit" name="action" value="approve" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-5 py-2 rounded-lg font-semibold">Approve</button>
                <button type="submit" name="action" value="reject" class="bg-[#A0223B] hover:bg-[#D02C4D] text-white px-5 py-2 rounded-lg font-semibold">Reject</button>
                <a href="view_nomination.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg font-semibold">Back</a>
            </form>
        </div>
    </main>
</div>
</body>
</html>
