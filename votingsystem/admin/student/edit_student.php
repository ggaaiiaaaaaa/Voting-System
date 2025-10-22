<?php
require_once __DIR__ . "/../../classes/student.php";
$studentObj = new Student();
$student = [];
$errors = [];
$student_id_param = $_GET['id'] ?? null;

if (!$student_id_param) {
    header("Location: view_student.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $studentData = $studentObj->fetchStudent($student_id_param);
    if (!$studentData) {
        exit("No Student Found <a href='view_student.php'>Back</a>");
    }
    $student["name"] = $studentData["name"];
    $student["student_id"] = $studentData["student_id"];
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student["name"] = trim(htmlspecialchars($_POST["name"]));
    $student["student_id"] = trim(htmlspecialchars($_POST["student_id"]));

    if (empty($student["name"])) {
        $errors["name"] = "Student name is required";
    }
    if (empty($student["student_id"])) {
        $errors["student_id"] = "Student ID is required";
    } elseif ($studentObj->isStudentIdExist($student["student_id"], $student_id_param)) {
        $errors["student_id"] = "This Student ID already exists.";
    }

    if (empty($errors)) {
        $studentObj->name = $student["name"];
        $studentObj->student_id = $student["student_id"];
        if ($studentObj->editStudent($student_id_param)) {
            header("Location: view_student.php");
            exit;
        } else {
            $errors['general'] = "Failed to edit student!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Edit Student</h1>
    <?php if (!empty($errors['general'])): ?>
        <p class="error"><?= $errors['general'] ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Student Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name'] ?? '') ?>">
        <span class="error"><?= $errors["name"] ?? "" ?></span>

        <label>Student ID *</label>
        <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>">
        <span class="error"><?= $errors["student_id"] ?? "" ?></span>

        <br><br>
        <input type="submit" value="Save Changes">
    </form>
    <br>
    <a href="view_student.php">← Back to Students</a>
</body>
</html>