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
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Nomination Overview</h2>
                <p class="text-sm text-gray-300 mt-1">All student nominations with position details.</p>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-500/20 backdrop-blur-sm border border-green-500/30 text-green-300 px-6 py-4 rounded-xl shadow-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif ($error): ?>
            <div class="mb-6 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 px-6 py-4 rounded-xl shadow-lg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- NOMINATION TABLE -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b border-white/20">
                    <tr>
                        <th class="py-3 text-white font-semibold">#</th>
                        <th class="py-3 text-white font-semibold">Position</th>
                        <th class="py-3 text-white font-semibold">Nominator</th>
                        <th class="py-3 text-white font-semibold">Nominee</th>
                        <th class="py-3 text-white font-semibold">Status</th>
                        <th class="py-3 text-white font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php if (!empty($nominations)): ?>
                        <?php $i = 1; foreach ($nominations as $n): ?>
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white/90"><?= $i++ ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($n['position_name']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($n['nominator_name']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($n['nominee_name']) ?></td>
                                <td class="py-3">
                                    <?php if ($n['status'] === 'Approved'): ?>
                                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold border border-green-500/30">Approved</span>
                                    <?php elseif ($n['status'] === 'Rejected'): ?>
                                        <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded-full text-xs font-semibold border border-red-500/30">Rejected</span>
                                    <?php else: ?>
                                        <span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold border border-yellow-500/30">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 flex justify-center gap-2">
                                    <?php if ($n['status'] === 'Pending'): ?>
                                        <a href="approve_nomination.php?id=<?= $n['id'] ?>" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-1 rounded text-xs font-medium shadow-lg transform hover:scale-105 transition-all duration-300">Approve</a>
                                    <?php endif; ?>
                                    <a href="delete_nomination.php?id=<?= $n['id'] ?>" onclick="return confirm('Delete this nomination?')" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-3 py-1 rounded text-xs font-medium shadow-lg transform hover:scale-105 transition-all duration-300">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <span class="text-gray-400 text-sm">No nominations found</span>
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
