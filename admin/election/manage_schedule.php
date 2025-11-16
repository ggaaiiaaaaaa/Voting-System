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
<body class="bg-[#f8f9fa] font-sans">
<div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Dashboard Panel</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🏠 Overview</a></li>
                <li><a href="manage_schedule.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">🗳️ Election Management</a></li>
                <li><a href="../student/view_student.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👥 Students</a></li>
                <li><a href="../teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📌 Positions</a></li>
                <li><a href="../nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📋 Nominations</a></li>
                <li><a href="view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📈 Results</a></li>
                <li><a href="../election/view_reports.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📊 Reports</a></li>
                <li><a href="audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Election Management</h2>
                <p class="text-sm text-gray-500">Set and control the election schedule.</p>
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                    🖨️ Print Schedule
                </button>
            </div>
            <a href="../admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">⬅ Back</a>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif ($error): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- CURRENT ELECTION SCHEDULE -->
        <section class="bg-white shadow rounded-xl p-6 mb-10">
            <h3 class="text-lg font-semibold text-[#D02C4D] mb-4">📅 Current Election Schedule</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b font-medium text-gray-600">
                        <tr>
                            <th class="py-2 px-4">Start Date</th>
                            <th class="py-2 px-4">End Date</th>
                            <th class="py-2 px-4">Status</th>
                            <th class="text-center py-2 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (!$election_schedule): ?>
                            <tr><td colspan="4" class="text-center py-4 text-gray-500">No election schedule has been set.</td></tr>
                        <?php else: ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?= htmlspecialchars($election_schedule['start']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($election_schedule['end']) ?></td>
                                <td class="py-2 px-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $election_schedule['status'] === 'Active' ? 'bg-green-500 text-white' : ($election_schedule['status'] === 'Upcoming' ? 'bg-blue-500 text-white' : 'bg-gray-400 text-white') ?>">
                                        <?= htmlspecialchars($election_schedule['status']) ?>
                                    </span>
                                </td>
<td class="text-center py-2 px-4 space-x-2">
    <?php if ($election_schedule['status'] === 'Upcoming'): ?>
        <span class="text-gray-500">Waiting to Start</span>
    <?php elseif ($election_schedule['status'] === 'Ongoing'): ?>
        <form method="POST" action="../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="pause">
            <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 font-semibold"
                    onclick="return confirm('Pause the election?')">Pause</button>
        </form>
        <form method="POST" action="../../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="end">
            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 font-semibold"
                    onclick="return confirm('End the election now?')">End</button>
        </form>
    <?php elseif ($election_schedule['status'] === 'Paused'): ?>
        <form method="POST" action="../../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="start">
            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 font-semibold"
                    onclick="return confirm('Resume the election?')">Resume</button>
        </form>
        <form method="POST" action="../admin/election_action.php" class="inline">
            <input type="hidden" name="action" value="end">
            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 font-semibold"
                    onclick="return confirm('End the election now?')">End</button>
        </form>
    <?php else: ?>
        <span class="text-gray-500">No Actions</span>
    <?php endif; ?>

                                    <!-- Edit / Delete -->
                                    <a href="edit_schedule.php?id=<?= urlencode($election_schedule['id']) ?>" class="text-blue-600 hover:underline ml-2">Edit</a>
                                    <a href="delete_schedule.php?id=<?= urlencode($election_schedule['id']) ?>" class="text-red-600 hover:underline ml-2"
                                       onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

<!-- ADD/EDIT ELECTION SCHEDULE -->
<section class="bg-white shadow rounded-xl p-6">
    <h3 class="text-lg font-semibold text-[#D02C4D] mb-4">
        <?= $schedule ? '✏️ Update Schedule' : '➕ Add New Schedule' ?>
    </h3>
    <form method="POST" action="save_schedule.php" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <!-- Only include ID if updating; remove for adding -->
        <?php if ($schedule): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($schedule['id']) ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Start Date</label>
            <input type="datetime-local" name="start_date" required
                   class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-[#D02C4D] focus:border-[#D02C4D]"
                   value="<?= $schedule ? date('Y-m-d\TH:i', strtotime($schedule['start_date'])) : '' ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">End Date</label>
            <input type="datetime-local" name="end_date" required
                   class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-[#D02C4D] focus:border-[#D02C4D]"
                   value="<?= $schedule ? date('Y-m-d\TH:i', strtotime($schedule['end_date'])) : '' ?>">
        </div>

        <div class="md:col-span-1 flex justify-end">
            <button type="submit"
                    class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-medium w-full md:w-auto">
                <?= $schedule ? 'Update Schedule' : 'Add Schedule' ?>
            </button>
        </div>
    </form>
</section>

    </main>
</div>
</body>
</html>
