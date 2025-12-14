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
                
                $_SESSION['message'] = "✅ Election started successfully! All students have been notified.";
            } else {
                $_SESSION['message'] = "❌ Failed to start election. Please check the election schedule.";
            }
            break;

        case 'pause':
            $success = $electionObj->pauseElection();
            if ($success) {
                require_once __DIR__ . "/../classes/notification.php";
                require_once __DIR__ . "/../classes/student.php";
                
                $notifObj = new Notification();
                $studentObj = new Student();
                
                $schedule = $electionObj->fetchSchedule();
                $election_name = $schedule['name'] ?? 'Official Election';
                
                // Notify all students about pause
                $students = $studentObj->getAllStudentEmails();
                foreach ($students as $student) {
                    $notifObj->createNotification(
                        $student['id'],
                        'student',
                        'Election Paused',
                        "The election '{$election_name}' has been temporarily paused by the administrator. You will be notified when it resumes.",
                        'election_paused'
                    );
                    
                    // Send email notification
                    $notifObj->sendEmail(
                        $student['email'],
                        'Election Paused - ' . $election_name,
                        $notifObj->getEmailTemplate('election_paused', [
                            'election_name' => $election_name,
                            'dashboard_url' => SYSTEM_URL . '/student/student_dashboard.php'
                        ])
                    );
                }
                
                $_SESSION['message'] = "⏸️ Election paused successfully! Students cannot vote or nominate during this time.";
            } else {
                $_SESSION['message'] = "❌ Failed to pause election.";
            }
            break;

        case 'resume':
            $success = $electionObj->resumeElection();
            if ($success) {
                require_once __DIR__ . "/../classes/notification.php";
                require_once __DIR__ . "/../classes/student.php";
                
                $notifObj = new Notification();
                $studentObj = new Student();
                
                $schedule = $electionObj->fetchSchedule();
                $election_name = $schedule['name'] ?? 'Official Election';
                
                // Notify all students about resume
                $students = $studentObj->getAllStudentEmails();
                foreach ($students as $student) {
                    $notifObj->createNotification(
                        $student['id'],
                        'student',
                        'Election Resumed',
                        "The election '{$election_name}' has resumed! You can now continue voting and nominating.",
                        'election_resumed'
                    );
                    
                    // Send email notification
                    $notifObj->sendEmail(
                        $student['email'],
                        'Election Resumed - ' . $election_name,
                        $notifObj->getEmailTemplate('election_resumed', [
                            'election_name' => $election_name,
                            'voting_url' => SYSTEM_URL . '/student/voting.php'
                        ])
                    );
                }
                
                $_SESSION['message'] = "▶️ Election resumed successfully! Students can now continue voting.";
            } else {
                $_SESSION['message'] = "❌ Failed to resume election.";
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
                
                $_SESSION['message'] = "🏁 Election ended successfully! Results have been calculated and students notified.";
            } else {
                $_SESSION['message'] = "❌ Failed to end election.";
            }
            break;

        default:
            $_SESSION['message'] = "❌ Invalid action.";
            break;
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>