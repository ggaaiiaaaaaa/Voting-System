<?php
// admin/teacher/delete_teacher.php
session_start();
require_once __DIR__ . "/../../classes/teacher.php";

$teacherObj = new Teacher();

// Validate request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: view_teacher.php");
    exit;
}

$t_id = intval($_GET['id']);

try {
    // Delete teacher
    $result = $teacherObj->deleteTeacher($t_id);

    if ($result) {
        // Optional: log action if logging exists
        if (isset($_SESSION['user_id'])) {
            $teacherObj->logAction($_SESSION['user_id'], "Deleted teacher", "Teacher ID: $t_id");
        }
        $_SESSION['success'] = "Teacher deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete the teacher. It may not exist.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to teacher list
header("Location: view_teacher.php");
exit;
?>
