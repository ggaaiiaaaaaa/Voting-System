<?php
session_start();
if (!isset($_SESSION['student_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../classes/student.php";
$studentObj = new Student();
$student = $studentObj->fetchStudent($_SESSION['student_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome, <?= htmlspecialchars($student['name']) ?></h1>
        <p>This is your student dashboard. Voting functionality will be implemented here.</p>
        <a href="../auth/logout.php">Logout</a>
    </div>
</body>
</html>