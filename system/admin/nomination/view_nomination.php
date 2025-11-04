<?php
session_start();
require_once __DIR__ . "/../../classes/nomination.php";
require_once __DIR__ . "/../../classes/student.php";
require_once __DIR__ . "/../../classes/position.php";

$nominationObj = new Nomination();
$studentObj = new Student();
$positionObj = new Position();

// Fetch all nominations with details
$nominations = $nominationObj->viewNominationsWithDetails();

// Handle alert messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Nominations</title>
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
                <li><a href="view_nomination.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">📋 Nominations</a></li>
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
        <!-- HEADER -->
        <header class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Nomination Overview</h2>
                <p class="text-sm text-gray-500">All student nominations with position details.</p>
            </div>
        </header>

        <!-- ALERTS -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-600 text-green-700 px-4 py-3 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif ($error): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-600 text-red-700 px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- NOMINATION TABLE -->
        <section class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-[#FEEAEA] text-[#D02C4D]">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Nominator</th>
                        <th class="px-4 py-3">Nominee</th>
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
                                <td class="px-4 py-3"><?= htmlspecialchars($n['nominator_name']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($n['nominee_name']) ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                        $statusColor = 'bg-yellow-100 text-yellow-700';
                                        if ($n['status'] === 'Approved') $statusColor = 'bg-green-100 text-green-700';
                                        elseif ($n['status'] === 'Rejected') $statusColor = 'bg-red-100 text-red-700';
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusColor ?>">
                                        <?= htmlspecialchars($n['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 flex justify-center gap-2">
                                    <?php if ($n['status'] === 'Pending'): ?>
                                        <a href="approve_nomination.php?id=<?= $n['id'] ?>" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-3 py-1 rounded text-xs font-medium">Approve</a>
                                    <?php endif; ?>
                                    <a href="delete_nomination.php?id=<?= $n['id'] ?>" onclick="return confirm('Delete this nomination?')" class="bg-[#A0223B] hover:bg-[#D02C4D] text-white px-3 py-1 rounded text-xs font-medium">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-500">No nominations yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
