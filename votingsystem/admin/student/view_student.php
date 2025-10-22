<?php
require_once __DIR__ . "/../../classes/student.php";
$studentObj = new Student();
$students = $studentObj->viewStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Students</title>    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>List of Students</h1>
    <button><a href="add_student.php">Add Student</a></button>
    <table>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <th>Actions</th>
        </tr>
        <?php if (!empty($students)): ?>
            <?php $i = 1; foreach ($students as $student): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($student["name"]) ?></td>
                    <td><?= htmlspecialchars($student["student_id"]) ?></td>
                    <td>
                        <a class="action-btn edit" href="edit_student.php?id=<?= $student['id'] ?>">Edit</a>
                        <a class="action-btn delete" href="delete_student.php?id=<?= $student['id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No students found.</td></tr>
        <?php endif; ?>
    </table>
    <br>
    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>