<?php
require_once __DIR__ . "/../../classes/student.php";
$studentObj = new Student();

$student = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student["name"] = trim(htmlspecialchars($_POST["name"]));
    $student["student_id"] = trim(htmlspecialchars($_POST["student_id"]));
    $student["password"] = trim(htmlspecialchars($_POST["password"]));

    if (empty($student["name"])) {
        $errors["name"] = "Student name is required";
    }
    if (empty($student["student_id"])) {
        $errors["student_id"] = "Student ID is required";
    } elseif ($studentObj->isStudentIdExist($student["student_id"])) {
        $errors["student_id"] = "This Student ID already exists.";
    }
    if (empty($student["password"])) {
        $errors["password"] = "Password is required";
    }

    if (empty($errors)) {
        $studentObj->name = $student["name"];
        $studentObj->student_id = $student["student_id"];
        $studentObj->password = $student["password"];

        if ($studentObj->addStudent()) {
            header("Location: view_student.php");
            exit;
        } else {
            $errors['general'] = "Failed to add student.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Add Student</h1>
    <?php if (!empty($errors['general'])): ?>
        <p class="error"><?= $errors['general'] ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <label for="name">Student Name <span>*</span></label>
        <input type="text" name="name" id="name" value="<?= $student["name"] ?? "" ?>">
        <p class="error"><?= $errors["name"] ?? "" ?></p>

        <label for="student_id">Student ID <span>*</span></label>
        <input type="text" name="student_id" id="student_id" value="<?= $student["student_id"] ?? "" ?>">
        <p class="error"><?= $errors["student_id"] ?? "" ?></p>

        <label for="password">Password <span>*</span></label>
        <input type="password" name="password" id="password">
        <p class="error"><?= $errors["password"] ?? "" ?></p>

        <input type="submit" value="Save Student">
    </form>
    <br>
    <button><a href="view_student.php">View Students</a></button>
</body>
</html>