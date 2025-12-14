<?php
session_start();
require_once __DIR__ . "/../../classes/election.php";

$electionObj = new Election();
$success = $error = "";

// Handle alert messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch the current election schedule
$schedule = $electionObj->fetchSchedule();

$currentStatus = $electionObj->getAdminControlledStatus();

$election_schedule = null;
if ($schedule) {
    $election_schedule = [
        'id' => $schedule['id'],
        'start' => date('M d, Y H:i', strtotime($schedule['start_date'])),
        'end' => date('M d, Y H:i', strtotime($schedule['end_date'])),
        'status' => $currentStatus
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Management</title>
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
        <h2 class="text-3xl font-bold text-white drop-shadow-lg">Election Management</h2>
        <p class="text-sm text-gray-300 mt-1">Set and control the election schedule.</p>
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

        <!-- CURRENT ELECTION SCHEDULE -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 mb-10 border border-white/20 animate-fade-in-up">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Current Election Schedule
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b border-white/20">
                        <tr>
                            <th class="py-3 text-white font-semibold">Start Date</th>
                            <th class="py-3 text-white font-semibold">End Date</th>
                            <th class="py-3 text-white font-semibold">Status</th>
                            <th class="text-center py-3 text-white font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-200">
                        <?php if (!$election_schedule): ?>
                            <tr><td colspan="4" class="text-center py-4 text-gray-400">No election schedule has been set.</td></tr>
                        <?php else: ?>
                            <tr class="border-b border-white/10 hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white/90"><?= htmlspecialchars($election_schedule['start']) ?></td>
                                <td class="py-3 text-white/90"><?= htmlspecialchars($election_schedule['end']) ?></td>
                                <td class="py-3">
                                    <span class="px-3 py-1 text-xs rounded-full font-medium
                                        <?= $election_schedule['status'] === 'Active' ? 'bg-green-500 text-white' : ($election_schedule['status'] === 'Upcoming' ? 'bg-blue-500 text-white' : 'bg-gray-400 text-white') ?>">
                                        <?= htmlspecialchars($election_schedule['status']) ?>
                                    </span>
                                </td>
<td class="text-center py-3 space-x-3">
    <?php if ($election_schedule['status'] === 'Upcoming'): ?>
        <span class="text-gray-400 font-medium">Waiting to Start</span>
    <?php elseif ($election_schedule['status'] === 'Ongoing'): ?>
        <form method="POST" action="../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="pause">
            <button type="submit" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-4 py-2 rounded-lg hover:from-yellow-600 hover:to-orange-600 font-semibold shadow-md transform hover:scale-105 transition-all duration-200"
                    onclick="return confirm('Pause the election?')">Pause</button>
        </form>
        <form method="POST" action="../../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="end">
            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg hover:from-red-600 hover:to-red-700 font-semibold shadow-md transform hover:scale-105 transition-all duration-200"
                    onclick="return confirm('End the election now?')">End</button>
        </form>
    <?php else: ?>
        <span class="text-gray-400 font-medium">No Actions</span>
    <?php endif; ?>

    <!-- Edit / Delete -->
    <a href="delete_schedule.php?id=<?= urlencode($election_schedule['id']) ?>" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg hover:from-red-600 hover:to-red-700 font-semibold shadow-md transform hover:scale-105 transition-all duration-200"
       onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a>
</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

<!-- ADD/EDIT ELECTION SCHEDULE -->
<section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <?= $schedule ? 'Update Schedule' : 'Add New Schedule' ?>
    </h3>
    <form method="POST" action="save_schedule.php" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <!-- Only include ID if updating; remove for adding -->
        <?php if ($schedule): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($schedule['id']) ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
            <input type="datetime-local" name="start_date" required
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-all duration-200"
                   value="<?= $schedule ? date('Y-m-d\TH:i', strtotime($schedule['start_date'])) : '' ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
            <input type="datetime-local" name="end_date" required
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-all duration-200"
                   value="<?= $schedule ? date('Y-m-d\TH:i', strtotime($schedule['end_date'])) : '' ?>">
        </div>

        <div class="md:col-span-1 flex justify-end">
            <button type="submit"
                    class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">
                <?= $schedule ? 'Update Schedule' : 'Add Schedule' ?>
            </button>
        </div>
    </form>
</section>

    </main>
</div>
</body>
</html>
