<?php
// election/delete_schedule.php
session_start();
require_once __DIR__ . "/../../classes/election.php";

$electionObj = new Election();

// Ensure valid request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: manage_schedule.php");
    exit;
}

$id = intval($_GET['id']);

try {
    // Delete election record
    $result = $electionObj->deleteElection($id); // <-- Make sure this exists in Election.php

    if ($result) {
        // Log admin action
        if (isset($_SESSION['user_id'])) {
            $electionObj->logAction($_SESSION['user_id'], "admin", "Deleted election", "Election ID: $id deleted.");
        }

        $_SESSION['success'] = "Election deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete the election. It may not exist.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to schedule management
header("Location: manage_schedule.php");
exit;
?>
