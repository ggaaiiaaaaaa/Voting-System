<?php
// admin/teacher/view_teacher.php
session_start();
require_once __DIR__ . "/../../classes/teacher.php";

$teacherObj = new Teacher();

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

// Fetch all teachers from database
$teachers = $teacherObj->viewTeachers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Teachers</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Teacher List</h2>
                <p class="text-sm text-gray-500">Manage all registered teachers here.</p>
            </div>
            <a href="add_teacher.php" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-4 py-2 rounded-lg font-medium">+ Add Teacher</a>
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

        <!-- TEACHER TABLE -->
        <section class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-[#FEEAEA] text-[#D02C4D]">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Teacher ID</th>
                        <th class="px-4 py-3">Full Name</th>
                        <th class="px-4 py-3">Advisory Section</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php if (!empty($teachers)): ?>
                        <?php $i = 1; foreach ($teachers as $teacher): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><?= $i++ ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($teacher['fullname']) ?></td>
                                <td class="px-4 py-3">
                                    <?= htmlspecialchars($teacher['advisory_section'] ?? '—') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (strtolower($teacher['status']) === 'active'): ?>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Active</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 flex justify-center gap-2">
                                    <a href="edit_teacher.php?id=<?= $teacher['id'] ?>" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-3 py-1 rounded text-xs font-medium">Edit</a>
                                    <a href="delete_teacher.php?id=<?= $teacher['id'] ?>" onclick="return confirm('Are you sure you want to delete this teacher?')" class="bg-[#A0223B] hover:bg-[#D02C4D] text-white px-3 py-1 rounded text-xs font-medium">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-500">No teachers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
