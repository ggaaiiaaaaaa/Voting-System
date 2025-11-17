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
    if ($success) {
        require_once __DIR__ . "/../classes/notification.php";
        require_once __DIR__ . "/../classes/student.php";
        
        $notifObj = new Notification();
        $studentObj = new Student();
        
        // Get election name
        $schedule = $electionObj->fetchSchedule();
        $election_name = $schedule['name'] ?? 'Official Election';
        
        // Notify all students
        $students = $studentObj->getAllStudentEmails();
        foreach ($students as $student) {
            $notifObj->notifyElectionStarted(
                $student['id'],
                $student['email'],
                $election_name
            );
        }
        
        $_SESSION['message'] = "Election started and all students notified!";
    }
    break;

case 'end':
    $success = $electionObj->endElection();
    if ($success) {
        require_once __DIR__ . "/../classes/notification.php";
        require_once __DIR__ . "/../classes/student.php";
        
        $notifObj = new Notification();
        $studentObj = new Student();
        
        // Get election name
        $schedule = $electionObj->fetchSchedule();
        $election_name = $schedule['name'] ?? 'Official Election';
        
        // Notify all students
        $students = $studentObj->getAllStudentEmails();
        foreach ($students as $student) {
            $notifObj->notifyElectionEnded(
                $student['id'],
                $student['email'],
                $election_name
            );
        }
        
        $_SESSION['message'] = "Election ended and results notification sent!";
    }
    break;

        default:
            $_SESSION['message'] = "Invalid action.";
            break;
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>
