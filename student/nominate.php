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
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Nominate a Student</h2>
                <p class="text-sm text-gray-300 mt-1">Select a student (including yourself) and a position to nominate for.</p>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-500/20 backdrop-blur-sm border border-green-500/30 text-green-300 px-6 py-4 rounded-xl shadow-lg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 px-6 py-4 rounded-xl shadow-lg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- NOMINATION FORM -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up max-w-2xl">
            <form method="POST" class="space-y-6">

                <!-- SELECT STUDENT (Nominee) -->
                <div>
                    <label for="nominee_id" class="block font-semibold text-gray-300 mb-2">Select Student to Nominate:</label>
                    <select id="nominee_id" name="nominee_id" required class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                    <label for="position_id" class="block font-semibold text-gray-300 mb-2">Select Position:</label>
                    <select id="position_id" name="position_id" required class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">
                        Submit Nomination
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
