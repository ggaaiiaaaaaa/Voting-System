<?php
session_start();
require_once __DIR__ . "/../../classes/position.php";
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../classes/student.php";

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../index.php");
    exit;
}

// Instantiate objects
$posObj = new Position();
$electionObj = new Election();
$studentObj = new Student();

// --- Fetch Data ---
$total_students = $studentObj->countStudents();
$total_positions = $posObj->countPositions();
$positions = $posObj->viewPositions();

// Handle alert messages
$success = $error = "";
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Positions</title>
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
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Positions</h2>
                <p class="text-sm text-gray-300 mt-1">Manage all election positions here.</p>
            </div>
            <a href="add_position.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">+ Add Position</a>
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

        <!-- POSITIONS TABLE -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b border-white/20">
                    <tr>
                        <th class="py-3 text-white font-semibold">#</th>
                        <th class="py-3 text-white font-semibold">Position Name</th>
                        <th class="py-3 text-white font-semibold">Position Order</th>
                        <th class="py-3 text-white font-semibold">Max Nominees</th>
                        <th class="py-3 text-white font-semibold">Status</th>
                        <th class="py-3 text-white font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-200">
                    <?php if (!empty($positions)): ?>
                        <?php $i = 1; foreach ($positions as $pos): ?>
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white/90"><?= $i++ ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($pos['position_name']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($pos['position_order']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($pos['max_nominees']) ?></td>
                                <td class="py-3">
                                    <?php if (strtolower($pos['status']) === 'active'): ?>
                                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold border border-green-500/30">Active</span>
                                    <?php else: ?>
                                        <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded-full text-xs font-semibold border border-red-500/30"><?= htmlspecialchars($pos['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 flex justify-center gap-2">
                                    <a href="edit_position.php?id=<?= $pos['id'] ?>" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-1 rounded text-xs font-medium shadow-lg transform hover:scale-105 transition-all duration-300">Edit</a>
                                    <a href="delete_position.php?id=<?= $pos['id'] ?>" onclick="return confirm('Are you sure you want to delete this position?')" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-3 py-1 rounded text-xs font-medium shadow-lg transform hover:scale-105 transition-all duration-300">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <span class="text-gray-400 text-sm">No positions found</span>
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
