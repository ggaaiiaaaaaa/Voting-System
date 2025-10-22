<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="position/view_position.php">Manage Positions</a></li>
                <li><a href="student/view_student.php">Manage Students</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>