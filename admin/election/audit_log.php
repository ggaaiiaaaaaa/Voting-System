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
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Audit Log</h2>
                <p class="text-sm text-gray-300 mt-1">Track user activities and system changes.</p>
            </div>
        </header>

        <!-- AUDIT LOG TABLE -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b border-white/20">
                    <tr>
                        <th class="py-3 text-white font-semibold">#</th>
                        <th class="py-3 text-white font-semibold">User</th>
                        <th class="py-3 text-white font-semibold">Role</th>
                        <th class="py-3 text-white font-semibold">Action</th>
                        <th class="py-3 text-white font-semibold">Details</th>
                        <th class="py-3 text-white font-semibold">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php if (!empty($logs)): ?>
                        <?php $i = 1; foreach ($logs as $log): ?>
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white/90"><?= $i++ ?></td>
                                <td class="py-3 text-white/90 font-medium">User ID: <?= htmlspecialchars($log['user_id']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($log['user_type']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($log['action']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($log['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <span class="text-gray-400 text-sm">No logs found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
