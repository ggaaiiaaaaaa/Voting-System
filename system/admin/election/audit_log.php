<?php
require_once __DIR__ . "/../../classes/election.php";

$electionObj = new Election();
$logs = $electionObj->fetchAuditLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Audit Log</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">System Activity Logs</p>
        </div>

        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🏠 Overview</a></li>
                <li><a href="manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🗳️ Election Management</a></li>
                <li><a href="../student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👥 Students</a></li>
                <li><a href="../teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📌 Positions</a></li>
                <li><a href="../nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📋 Nominations</a></li>
                <li><a href="view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📈 Results</a></li>
                <li><a href="audit_log.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">⚙️ System Controls</a></li>
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
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Audit Log</h2>
                <p class="text-sm text-gray-500">Track user activities and system changes.</p>
            </div>
        </header>

        <!-- AUDIT LOG TABLE -->
        <section class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-[#FEEAEA] text-[#D02C4D]">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Details</th>
                        <th class="px-4 py-3">Timestamp</th>
                    </tr>
                </thead>
<tbody class="divide-y divide-gray-100 text-gray-700">
    <?php if (!empty($logs)): ?>
        <?php $i = 1; foreach ($logs as $log): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><?= $i++ ?></td>
                <td class="px-4 py-3 font-medium text-gray-800">User ID: <?= htmlspecialchars($log['user_id']) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($log['user_type']) ?></td>
                <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($log['action']) ?></td>
                <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($log['timestamp']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="text-center py-6 text-gray-500">No logs found.</td>
        </tr>
    <?php endif; ?>
</tbody>

            </table>
        </section>
    </main>
</div>
</body>
</html>
