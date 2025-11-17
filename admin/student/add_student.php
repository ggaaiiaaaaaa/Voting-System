<?php
// admin/student/add_student.php
session_start();
require_once __DIR__ . "/../../classes/student.php";
require_once __DIR__ . "/../../classes/teacher.php";

$studentObj = new Student();
$teacherObj = new Teacher();
$errors = [];
$student = [];

// Fetch all advisory sections from teachers table
$advisorySections = $teacherObj->getAllAdvisorySections();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student['fullname'] = trim($_POST['name'] ?? '');
    $student['student_id'] = trim($_POST['student_id'] ?? '');
    $student['email'] = trim($_POST['email'] ?? '');
    $student['password'] = trim($_POST['password'] ?? '');
    $student['grade_section'] = trim($_POST['grade_section'] ?? '');

    // VALIDATIONS
    if (empty($student['fullname'])) $errors['name'] = "Student Name is required.";
    if (empty($student['student_id'])) $errors['student_id'] = "Student ID is required.";
    if (empty($student['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($student['password'])) $errors['password'] = "Password is required.";
    if (empty($student['grade_section'])) $errors['grade_section'] = "Grade & Section is required.";

    // Check for existing Student ID
    if (!$errors && $studentObj->isStudentIdExist($student['student_id'])) {
        $errors['student_id'] = "Student ID already exists.";
    }

    // Check for existing Email
    if (!$errors && $studentObj->isEmailExist($student['email'])) {
        $errors['email'] = "Email already exists.";
    }

    // SAVE STUDENT
    if (empty($errors)) {
        $studentObj->fullname = $student['fullname'];
        $studentObj->student_id = $student['student_id'];
        $studentObj->email = $student['email'];
        $studentObj->password = $student['password'];
        $studentObj->grade_section = $student['grade_section'];
        $studentObj->status = 'Active';

        if ($studentObj->addStudent()) {
            $_SESSION['success'] = "Student '{$student['fullname']}' added successfully.";
            header("Location: view_student.php");
            exit;
        } else {
            $errors['general'] = "Failed to add student. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#D02C4D]">Election Admin</h1>
            <p class="text-xs text-gray-500 mt-1">Manage Students</p>
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
                <li><a href="../election/audit_log.php" class="flex items-center gap-3 px-6 py-2 hover:bg-[#FEEAEA] text-gray-700">⚙️ System Controls</a></li>
            </ul>
        </nav>

        <div class="border-t p-4">
            <a href="../../auth/logout.php" class="block text-center bg-[#D02C4D] text-white py-2 rounded hover:bg-[#A0223B] font-semibold">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Add New Student</h2>
                <p class="text-sm text-gray-500">Fill in the form to add a new student record.</p>
            </div>
            <a href="view_student.php" class="bg-[#FEEAEA] hover:bg-[#FFDADA] text-[#D02C4D] px-4 py-2 rounded-lg">← Back</a>
        </div>

        <section class="bg-white shadow rounded-xl p-6 max-w-xl">
            <?php if (!empty($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <!-- FULL NAME -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($student['fullname'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['name'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- STUDENT ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student ID <span class="text-red-500">*</span></label>
                    <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['student_id'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['student_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['email'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['email'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- GRADE & SECTION -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade & Section <span class="text-red-500">*</span></label>
                    <select name="grade_section" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                        <option value="">-- Select Grade & Section --</option>
                        <?php foreach ($advisorySections as $section): ?>
                            <option value="<?= htmlspecialchars($section) ?>" 
                                <?= (isset($student['grade_section']) && $student['grade_section'] === $section) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['grade_section'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['grade_section'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['password'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['password'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <button type="submit" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-semibold">Save Student</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>