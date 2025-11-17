<?php
// admin/teacher/edit_teacher.php
session_start();
require_once __DIR__ . "/../../classes/teacher.php";
$teacherObj = new Teacher();

$teacher = [];
$errors = [];
$teacher_id_param = $_GET['id'] ?? null;

if (!$teacher_id_param) {
    header("Location: view_teacher.php");
    exit;
}

// FETCH DATA
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $teacherData = $teacherObj->fetchTeacher($teacher_id_param);

    if (!$teacherData || !is_array($teacherData)) {
        exit("<p>No Teacher Found. <a href='view_teacher.php'>Back</a></p>");
    }

    // ✅ Use 'fullname' instead of 'name'
    $teacher["fullname"] = $teacherData["fullname"] ?? '';
    $teacher["teacher_id"] = $teacherData["teacher_id"] ?? '';
    $teacher["advisory_section"] = $teacherData["advisory_section"] ?? '';
    $teacher["status"] = $teacherData["status"] ?? 'Inactive';
}

// UPDATE TEACHER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $teacher["fullname"] = trim($_POST["fullname"] ?? '');
    $teacher["teacher_id"] = trim($_POST["teacher_id"] ?? '');
    $teacher["advisory_section"] = trim($_POST["advisory_section"] ?? '');
    $teacher["status"] = $_POST["status"] ?? 'Inactive';

    // VALIDATION
    if (empty($teacher["fullname"])) $errors["fullname"] = "Teacher Name is required.";
    if (empty($teacher["teacher_id"])) {
        $errors["teacher_id"] = "Teacher ID is required.";
    } elseif ($teacherObj->isTeacherIdExist($teacher["teacher_id"], $teacher_id_param)) {
        $errors["teacher_id"] = "This Teacher ID already exists.";
    }
    if (empty($teacher["advisory_section"])) $errors["advisory_section"] = "Advisory Section is required.";

    if (empty($errors)) {
        $teacherObj->fullname = $teacher["fullname"];
        $teacherObj->teacher_id = $teacher["teacher_id"];
        $teacherObj->advisory_section = $teacher["advisory_section"];
        $teacherObj->status = $teacher["status"];

        if ($teacherObj->editTeacher($teacher_id_param)) {
            $_SESSION['success'] = "Teacher '{$teacher['fullname']}' updated successfully.";
            header("Location: view_teacher.php");
            exit;
        } else {
            $errors["general"] = "Failed to update teacher.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Edit Teacher</h2>
                <p class="text-sm text-gray-500">Update the teacher information below.</p>
            </div>
            <a href="view_teacher.php" class="bg-[#FEEAEA] hover:bg-[#FFDADA] text-[#D02C4D] px-4 py-2 rounded-lg">← Back</a>
        </div>

        <section class="bg-white shadow rounded-xl p-6 max-w-xl">
            <?php if (!empty($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <!-- TEACHER FULLNAME -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teacher Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($teacher['fullname'] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors['fullname'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['fullname'] ?></p>
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

                <!-- STATUS -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                        <option value="Active" <?= ($teacher['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($teacher['status'] === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <button type="submit" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-semibold">Save Changes</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
