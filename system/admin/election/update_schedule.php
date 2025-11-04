<?php
// election/update_schedule.php
session_start();
require_once __DIR__ . "/../../classes/election.php";
$electionObj = new Election();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: manage_schedule.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
$phase = trim($_POST['phase'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');

if (empty($id) || empty($phase) || empty($start_date) || empty($end_date)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: edit_schedule.php?id=$id");
    exit;
}

// Validate date logic
if (strtotime($end_date) < strtotime($start_date)) {
    $_SESSION['error'] = "End date cannot be earlier than start date.";
    header("Location: edit_schedule.php?id=$id");
    exit;
}

// Determine phase status
$current_time = date('Y-m-d H:i:s');
if ($current_time < $start_date) {
    $status = 'Upcoming';
} elseif ($current_time >= $start_date && $current_time <= $end_date) {
    $status = 'Active';
} else {
    $status = 'Closed';
}

try {
    $result = $electionObj->updateSchedule($id, $phase, $start_date, $end_date, $status);

    if ($result) {
        // Optional: Log the update
        if (isset($_SESSION['user_id'])) {
            $electionObj->logAction($_SESSION['user_id'], "Updated schedule for $phase", "Schedule ID: $id");
        }

        $_SESSION['success'] = "Election phase <b>$phase</b> updated successfully.";
    } else {
        $_SESSION['error'] = "No changes were made or update failed.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: manage_schedule.php");
exit;
?>
