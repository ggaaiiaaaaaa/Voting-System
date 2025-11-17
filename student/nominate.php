<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/position.php";
require_once __DIR__ . "/../classes/student.php";

// Redirect if not logged in or not student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$electionObj = new Election();
$posObj = new Position();
$studentObj = new Student();

// Fetch all positions
$positions = $posObj->viewPositions();

// Fetch student nominations to disable already nominated positions
// This determines which positions the current student has already nominated FOR.
$student_nominations = $electionObj->getStudentNominations($student_id);
$alreadyNominated = array_column($student_nominations, 'position_id');

// Fetch all students (include self now)
$students = $studentObj->viewAllStudents();
// --- Check admin-controlled election status ---
$election_status = $electionObj->getAdminControlledStatus(); // Ongoing, Paused, Ended, Upcoming

if ($election_status !== 'Ongoing') {
    if ($election_status === 'Ended') {
        // Redirect to results if election ended
        header("Location: view_results.php");
        exit;
    } else {
        // Pause or Upcoming: show message and block form
        $_SESSION['error'] = "Nominations are not allowed at this time. Election status: $election_status.";
        header("Location: student_dashboard.php");
        exit;
    }
}

// Handle nomination submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_id = $_POST['position_id'];
    $nominee_id = $_POST['nominee_id'];

    // --- NEW SERVER-SIDE VALIDATION ---
    // Check if the student has already nominated for this specific position
    if (in_array($position_id, $alreadyNominated)) {
        $_SESSION['error'] = "Nomination failed. You have already submitted a nomination for the selected position.";
        header("Location: nominate.php");
        exit;
    }
    // --- END NEW VALIDATION ---

    $result = $electionObj->submitNomination($student_id, $nominee_id, $position_id);

// After: $result = $electionObj->submitNomination(...)
// After successful nomination
if ($result) {
    require_once __DIR__ . "/../classes/notification.php";
    require_once __DIR__ . "/../classes/user.php";
    
    $notifObj = new Notification();
    $userObj = new User();
    $studentObj = new Student();
    
    // Get student info
    $studentData = $studentObj->fetchStudent($student_id);
    $student_name = $studentData['fullname'];
    
    // Get position name
    $positionData = $posObj->fetchPosition($position_id);
    $position_name = $positionData['position_name'];
    
    // Get admin email and ID
    $admin_email = $userObj->getAdminEmail();
    $admin_id = $userObj->getAdminIdByEmail($admin_email);
    
    // Notify admin (both system and email)
    if ($admin_email && $admin_id) {
        $notifObj->notifyAdminNewNomination(
            $admin_id,
            $admin_email,
            $student_name,
            $position_name
        );
    }
    
    $_SESSION['success'] = "Nomination submitted successfully!";
} else {
        // This 'else' catches failures from the Election class (e.g., database error)
        $_SESSION['error'] = "Nomination failed. An internal error occurred.";
    }

    header("Location: nominate.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nominate a Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="mb-8">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">Nominate a Student</h2>
            <p class="text-sm text-gray-500">Select a student (including yourself) and a position to nominate for.</p>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- NOMINATION FORM -->
        <section class="bg-white p-8 rounded-xl shadow-md max-w-2xl">
            <form method="POST" class="space-y-6">

                <!-- SELECT STUDENT (Nominee) -->
                <div>
                    <label for="nominee_id" class="block font-semibold text-gray-700 mb-2">Select Student to Nominate:</label>
                    <select id="nominee_id" name="nominee_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                        <option value="">-- Choose a Student --</option>
                        <?php foreach ($students as $stud): ?>
                            <option value="<?= htmlspecialchars($stud['id']) ?>" <?= $stud['id'] == $student_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stud['name']) ?> 
                                <?= $stud['id'] == $student_id ? '(You)' : '' ?> 
                                (<?= htmlspecialchars($stud['grade'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- SELECT POSITION -->
                <div>
                    <label for="position_id" class="block font-semibold text-gray-700 mb-2">Select Position:</label>
                    <select id="position_id" name="position_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                        <option value="">-- Choose a Position --</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?= $pos['id'] ?>" <?= in_array($pos['id'], $alreadyNominated) ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($pos['position_name']) ?>
                                <?= in_array($pos['id'], $alreadyNominated) ? '(Already Nominated by You)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (count($alreadyNominated) === count($positions) && count($positions) > 0): ?>
                        <p class="text-sm text-gray-500 mt-2">You have nominated for all available positions.</p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 font-semibold">
                        Submit Nomination
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
