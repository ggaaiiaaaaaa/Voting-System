<?php
// admin/teacher/add_teacher.php
session_start();
require_once __DIR__ . "/../../classes/teacher.php";

$teacherObj = new Teacher();
$errors = [];
$teacher = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher['fullname'] = trim($_POST['name'] ?? '');
    $teacher['teacher_id'] = trim($_POST['teacher_id'] ?? '');
    $teacher['password'] = trim($_POST['password'] ?? '');
    $teacher['advisory_section'] = trim($_POST['advisory_section'] ?? '');

    // VALIDATIONS
    if (empty($teacher['fullname'])) $errors['name'] = "Teacher Name is required.";
    if (empty($teacher['teacher_id'])) $errors['teacher_id'] = "Teacher ID is required.";
    if (empty($teacher['password'])) $errors['password'] = "Password is required.";
    if (empty($teacher['advisory_section'])) $errors['advisory_section'] = "Advisory Section is required.";

    // Check for existing Teacher ID
    if (!$errors && $teacherObj->isTeacherIdExist($teacher['teacher_id'])) {
        $errors['teacher_id'] = "Teacher ID already exists.";
    }

    // SAVE TEACHER
    if (empty($errors)) {
        $teacherObj->fullname = $teacher['fullname'];
        $teacherObj->teacher_id = $teacher['teacher_id'];
        $teacherObj->password = $teacher['password'];
        $teacherObj->advisory_section = $teacher['advisory_section'];

        if ($teacherObj->addTeacher()) {
            $_SESSION['success'] = "Teacher '{$teacher['fullname']}' added successfully.";
            header("Location: view_teacher.php");
            exit;
        } else {
            $errors['general'] = "Failed to add teacher. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Teacher</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Add New Teacher</h2>
                <p class="text-sm text-gray-500">Fill in the form to add a new teacher record.</p>
            </div>
            <a href="view_teacher.php" class="bg-[#FEEAEA] hover:bg-[#FFDADA] text-[#D02C4D] px-4 py-2 rounded-lg">← Back</a>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teacher Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($teacher['fullname'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['name'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- TEACHER ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teacher ID <span class="text-red-500">*</span></label>
                    <input type="text" name="teacher_id" value="<?= htmlspecialchars($teacher['teacher_id'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['teacher_id'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['teacher_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- ADVISORY SECTION -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Advisory Section <span class="text-red-500">*</span></label>
                    <input type="text" name="advisory_section" value="<?= htmlspecialchars($teacher['advisory_section'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['advisory_section'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['advisory_section'] ?></p>
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
                    <button type="submit" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-semibold">Save Teacher</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
