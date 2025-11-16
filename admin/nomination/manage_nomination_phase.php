<?php
require_once __DIR__ . "/../../classes/nomination.php";
$nomObj = new Nomination();

// Example: get current logged-in admin/teacher ID
session_start();
$userID = $_SESSION['user_id'] ?? null;

$phase_status = $nomObj->getNominationPhaseStatus();
$message = "";

// Handle phase control
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "start") {
        $nomObj->startNominationPhase($userID);
        $message = "Nomination phase started!";
    } elseif ($action === "pause") {
        $nomObj->pauseNominationPhase($userID);
        $message = "Nomination phase paused!";
    } elseif ($action === "end") {
        $nomObj->endNominationPhase($userID);
        $message = "Nomination phase ended!";
    }

    $phase_status = $nomObj->getNominationPhaseStatus();
}

// Fetch nominations for preview
$nominations = $nomObj->viewNominations();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Nomination Phase</title>
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
                <li><a href="manage_nomination_phase.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">⚡ Manage Phase</a></li>
                <li><a href="../election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📈 Results</a></li>
                <li><a href="../election/audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">Manage Nomination Phase</h2>
        </header>

        <?php if ($message): ?>
            <p class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $message ?></p>
        <?php endif; ?>

        <!-- Phase Controls -->
        <div class="bg-white shadow rounded-xl p-6 mb-6">
            <p class="mb-4">Current Status: <span class="font-semibold text-gray-700"><?= htmlspecialchars($phase_status) ?></span></p>
            <form method="post" class="flex flex-wrap gap-3">
                <button type="submit" name="action" value="start" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-semibold" <?= $phase_status === 'Nomination' ? 'disabled opacity-50' : '' ?>>Start</button>
                <button type="submit" name="action" value="pause" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded-lg font-semibold" <?= $phase_status !== 'Nomination' ? 'disabled opacity-50' : '' ?>>Pause</button>
                <button type="submit" name="action" value="end" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-semibold" <?= $phase_status === 'Closed' ? 'disabled opacity-50' : '' ?>>End</button>
            </form>
        </div>

        <!-- Quick Preview of Nominations -->
        <section class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <h3 class="text-lg font-semibold text-[#D02C4D] mb-4">Current Nominations</h3>
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-[#FEEAEA] text-[#D02C4D]">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Nominee Name</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php if (!empty($nominations)): ?>
                        <?php $i = 1; foreach ($nominations as $n): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><?= $i++ ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($n['position_name']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($n['student_name']) ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                        $statusColor = 'bg-yellow-100 text-yellow-700';
                                        if ($n['status'] === 'Approved') $statusColor = 'bg-green-100 text-green-700';
                                        if ($n['status'] === 'Rejected') $statusColor = 'bg-red-100 text-red-700';
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusColor ?>">
                                        <?= htmlspecialchars($n['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 flex justify-center gap-2">
                                    <a href="approve_nomination.php?id=<?= $n['id'] ?>" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-3 py-1 rounded text-xs font-medium">Approve</a>
                                    <a href="delete_nomination.php?id=<?= $n['id'] ?>" onclick="return confirm('Delete this nomination?')" class="bg-[#A0223B] hover:bg-[#D02C4D] text-white px-3 py-1 rounded text-xs font-medium">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">No nominations yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
``
