<?php
// admin/delete_student.php
session_start();
require_once __DIR__ . "/../../classes/student.php";

$studentObj = new Student();

// Validate request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: view_student.php");
    exit;
}

$s_id = intval($_GET['id']);

try {
    // Delete student
    $result = $studentObj->deleteStudent($s_id);

    if ($result) {
        // Optional: log action
        if (isset($_SESSION['user_id'])) {
            $studentObj->logAction($_SESSION['user_id'], "Deleted student", "Student ID: $s_id");
        }
        $_SESSION['success'] = "Student deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete the student. It may not exist.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to view_student.php
header("Location: view_student.php");
exit;
?>
