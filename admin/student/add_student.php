<?php
// admin/student/add_student.php
session_start();
require_once __DIR__ . "/../../classes/student.php";

$studentObj = new Student();
$errors = [];
$student = [];


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
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Add New Student</h2>
                <p class="text-sm text-gray-300 mt-1">Fill in the form to add a new student record.</p>
            </div>
            <a href="view_student.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">← Back</a>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if (!empty($errors['general'])): ?>
            <div class="mb-6 bg-red-500/20 border-l-4 border-red-600 text-red-400 px-4 py-3 rounded animate-fade-in-up">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>

        <!-- FORM SECTION -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up max-w-xl">
            <form action="" method="POST" class="space-y-6">
                <!-- FULL NAME -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Student Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($student['fullname'] ?? '') ?>" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['name'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['name'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- STUDENT ID -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Student ID <span class="text-red-500">*</span></label>
                    <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['student_id'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['student_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['email'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['email'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['password'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['password'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- GRADE & SECTION -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Grade & Section <span class="text-red-500">*</span></label>
                    <input type="text" name="grade_section" value="<?= htmlspecialchars($student['grade_section'] ?? '') ?>" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['grade_section'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['grade_section'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">Save Student</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>