<?php
// election/save_schedule.php
session_start();
require_once __DIR__ . "/../../classes/election.php";

$electionObj = new Election();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: manage_schedule.php");
    exit;
}

$id = trim($_POST['id'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');

// Validate input
if (empty($start_date) || empty($end_date)) {
    $_SESSION['error'] = "Start and end dates are required.";
    header("Location: manage_schedule.php");
    exit;
}

$start_timestamp = strtotime($start_date);
$end_timestamp = strtotime($end_date);

if ($end_timestamp < $start_timestamp) {
    $_SESSION['error'] = "End date cannot be earlier than the start date.";
    header("Location: manage_schedule.php");
    exit;
}

$db_start_date = date('Y-m-d H:i:s', $start_timestamp);
$db_end_date = date('Y-m-d H:i:s', $end_timestamp);

try {
    if (!empty($id)) {
        // Update existing schedule
        $updated = $electionObj->updateElection($id, $db_start_date, $db_end_date);

        if ($updated) {
            $_SESSION['success'] = "Election schedule has been updated successfully.";

            // Reset votes/results because the election is now upcoming again
            $electionObj->resetElectionForNewCycle();

            // Log admin action
            if (isset($_SESSION['user_id'])) {
                $electionObj->logAction($_SESSION['user_id'], 'admin', 'Update Election', "Updated election schedule ID: $id and reset votes/results");
            }
        } else {
            $_SESSION['error'] = "Failed to update the schedule. Please try again.";
        }
    } else {
        // Add new schedule
        $added = $electionObj->addElection($db_start_date, $db_end_date);

        if ($added) {
            $_SESSION['success'] = "Election schedule has been set successfully.";

            // Reset votes/results for the new election cycle
            $electionObj->resetElectionForNewCycle();

            if (isset($_SESSION['user_id'])) {
                $electionObj->logAction($_SESSION['user_id'], 'admin', 'Add Election', "Created new election schedule and reset votes/results");
            }
        } else {
            $_SESSION['error'] = "Failed to set the new schedule. An election schedule may already exist.";
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "A database error occurred: " . $e->getMessage();
}

header("Location: manage_schedule.php");
exit;
