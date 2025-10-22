<?php
require_once __DIR__ . "/../../classes/student.php";
$studentObj = new Student();

if (!isset($_GET['id'])) {
    header("Location: view_student.php");
    exit;
}

$s_id = trim(htmlspecialchars($_GET['id']));
if ($studentObj->deleteStudent($s_id)) {
    header("Location: view_student.php");
    exit;
} else {
    echo "<p>Failed to delete student.</p>";
    echo "<a href='view_student.php'>← Back to Students</a>";
}
?>