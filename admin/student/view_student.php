<?php
// admin/view_student.php
session_start();
require_once __DIR__ . "/../../classes/student.php";

$studentObj = new Student();

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

// Fetch all students from database
$students = $studentObj->viewStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Student Management</p>
        </div>

        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="../admin_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🏠 Overview</a></li>
                <li><a href="../election/manage_schedule.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">🗳️ Election Management</a></li>
                <li><a href="view_student.php" class="flex items-center gap-3 px-6 py-2 bg-[#FEEAEA] text-[#D02C4D] font-medium">👥 Students</a></li>
                <li><a href="../teacher/view_teacher.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">👨‍🏫 Teachers</a></li>
                <li><a href="../position/view_position.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📌 Positions</a></li>
                <li><a href="../nomination/view_nomination.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📋 Nominations</a></li>
                <li><a href="../election/view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">📈 Results</a></li>
                <li><a href="../election/view_reports.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📊 Reports</a></li>
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
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Student List</h2>
                <p class="text-sm text-gray-500">Manage all registered students here.</p>
            </div>
            <a href="add_student.php" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-4 py-2 rounded-lg font-medium">+ Add Student</a>

        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($success): ?>
            <div class="mb-6 bg-[#FEEAEA] border-l-4 border-[#D02C4D] text-[#D02C4D] px-4 py-3 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif ($error): ?>
            <div class="mb-6 bg-[#FEEAEA] border-l-4 border-[#D02C4D] text-[#D02C4D] px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- STUDENT TABLE -->
        <section class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
<!-- Update the table headers -->
<thead class="bg-[#FEEAEA] text-[#D02C4D]">
    <tr>
        <th class="px-4 py-3">#</th>
        <th class="px-4 py-3">Student ID</th>
        <th class="px-4 py-3">Full Name</th>
        <th class="px-4 py-3">Email</th>
        <th class="px-4 py-3">Grade & Section</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3 text-center">Actions</th>
    </tr>
</thead>

<!-- Update the table body -->
<tbody class="divide-y divide-gray-100 text-gray-700">
    <?php if (!empty($students)): ?>
        <?php $i = 1; foreach ($students as $student): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><?= $i++ ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($student['student_id']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($student['fullname']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($student['email'] ?? 'N/A') ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($student['grade_section']) ?></td>
                <td class="px-4 py-3">
                    <?php if ($student['status'] === 'Active'): ?>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Active</span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 flex justify-center gap-2">
                    <a href="edit_student.php?id=<?= $student['id'] ?>" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-3 py-1 rounded text-xs font-medium">Edit</a>
                    <a href="delete_student.php?id=<?= $student['id'] ?>" onclick="return confirm('Are you sure you want to delete this student?')" class="bg-[#A0223B] hover:bg-[#D02C4D] text-white px-3 py-1 rounded text-xs font-medium">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center py-6 text-gray-500">No students found.</td>
        </tr>
    <?php endif; ?>
</tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
