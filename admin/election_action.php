<?php
session_start();
require_once __DIR__ . "/../classes/election.php";

// ✅ Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$electionObj = new Election();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $success = false;

    switch ($action) {
        case 'start':
            $success = $electionObj->startElection();
            $_SESSION['message'] = $success 
                ? "Election started. Students can now nominate and vote." 
                : "Failed to start the election. It may already be ongoing or ended.";
            break;

        case 'pause':
            $success = $electionObj->pauseElection();
            $_SESSION['message'] = $success 
                ? "Election paused. Students cannot nominate or vote." 
                : "Failed to pause the election. It may not be ongoing.";
            break;

        case 'end':
            $success = $electionObj->endElection();
            $_SESSION['message'] = $success 
                ? "Election ended. Voting is now closed." 
                : "Failed to end the election. It may already be ended.";
            break;

        default:
            $_SESSION['message'] = "Invalid action.";
            break;
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>
