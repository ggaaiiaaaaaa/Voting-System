<?php
session_start();
require_once __DIR__ . "/../classes/election.php";
require_once __DIR__ . "/../classes/position.php";

// ✅ Redirect if not logged in or not student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$electionObj = new Election();
$posObj = new Position();

$election_status = $electionObj->getAdminControlledStatus(); // Ongoing, Paused, Ended, Upcoming

if ($election_status !== 'Ongoing') {
    if ($election_status === 'Ended') {
        // Redirect to results if election ended
        header("Location: view_results.php");
        exit;
    } else {
        // Pause or Upcoming: show message and block voting
        $_SESSION['error'] = "Voting is not allowed at this time. Election status: $election_status.";
        header("Location: student_dashboard.php");
        exit;
    }
}

// ✅ Block re-entry if already voted
if ($electionObj->hasStudentVoted($student_id)) {
    $_SESSION['error'] = "You have already finished voting. You cannot access the voting page again.";
    header("Location: student_dashboard.php?redirected=1");
    exit;
}

// --- Fetch positions ---
$positions = $posObj->viewPositions();
if (!is_array($positions)) {
    $positions = [];
}

// --- Fetch nominations by position and remove duplicates ---
$nominationsByPosition = [];
$required_position_ids = [];

foreach ($positions as $pos) {
    $nominations = $electionObj->getApprovedNominationsByPosition($pos['id']);

    // Deduplicate candidates by candidate_id
    $unique_nominations = [];
    $seen_candidates = [];
    foreach ($nominations as $nom) {
        if (!in_array($nom['candidate_id'], $seen_candidates)) {
            $unique_nominations[] = $nom;
            $seen_candidates[] = $nom['candidate_id'];
        }
    }

    $nominationsByPosition[$pos['id']] = $unique_nominations;

    if (!empty($unique_nominations)) {
        $required_position_ids[] = $pos['id'];
    }
}

// --- Handle vote submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['votes'])) {
    $votes = $_POST['votes']; // Array: position_id => nomination_id

    // Validate all required positions have been voted for
    $voted_position_ids = array_keys($votes);
    $missing_votes = array_diff($required_position_ids, $voted_position_ids);

    if (!empty($missing_votes)) {
        $_SESSION['error'] = "Please vote for all required positions before submitting.";
        header("Location: voting.php");
        exit;
    }

    // Submit all votes
    $result = $electionObj->submitVote($student_id, $votes);

// After successful vote
if (isset($result['success']) && $result['success']) {
    require_once __DIR__ . "/../classes/notification.php";
    require_once __DIR__ . "/../classes/user.php";
    require_once __DIR__ . "/../classes/student.php";
    
    $notifObj = new Notification();
    $userObj = new User();
    $studentObj = new Student();
    
    // Get student info
    $studentData = $studentObj->fetchStudent($student_id);
    $student_name = $studentData['fullname'];
    
    // Get admin email and ID
    $admin_email = $userObj->getAdminEmail();
    $admin_id = $userObj->getAdminIdByEmail($admin_email);
    
    // Notify admin
    if ($admin_email && $admin_id) {
        $notifObj->notifyAdminNewVote(
            $admin_id,
            $admin_email,
            $student_name
        );
    }
    
    $_SESSION['success'] = "Your votes have been successfully submitted!";
} else {
        $_SESSION['error'] = $result['error'] ?? "An unexpected error occurred during vote submission.";
        header("Location: voting.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote for Candidates</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg fixed h-screen flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-red-700">Student Panel</h1>
            <p class="text-xs text-gray-500 mt-1">Voting Panel</p>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            <ul class="space-y-1">
                <li><a href="student_dashboard.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">🏠 Overview</a></li>
                <li><a href="nominate.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📋 My Nominations</a></li>
                <li><a href="voting.php" class="flex items-center gap-3 px-6 py-2 bg-red-100 text-red-700 font-medium">🗳️ Vote</a></li>
                <li><a href="view_results.php" class="flex items-center gap-3 px-6 py-2 hover:bg-red-100 text-gray-700">📈 Results</a></li>
            </ul>
        </nav>
        <div class="border-t p-4">
            <a href="../auth/logout.php" class="block text-center bg-red-500 text-white py-2 rounded hover:bg-red-600 font-semibold">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
        <header class="mb-8">
            <h2 class="text-2xl font-semibold text-[#D02C4D]">🗳️ Vote for Candidates</h2>
            <p class="text-sm text-gray-500">Select one candidate per position.</p>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php foreach ($positions as $pos): ?>
                <?php $nominations = $nominationsByPosition[$pos['id']]; ?>
                <section class="bg-white shadow rounded-xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700">
                        <?= htmlspecialchars($pos['position_name']) ?>
                        <?php if (!empty($nominations)): ?>
                            <span class="text-sm text-red-500 font-normal">(Required)</span>
                        <?php endif; ?>
                    </h3>

                    <?php if (!empty($nominations)): ?>
                        <?php 
                        $is_required = in_array($pos['id'], $required_position_ids);
                        $required_html = $is_required ? 'required' : '';
                        ?>
                        <?php foreach ($nominations as $nom): ?>
                            <label class="flex items-center gap-2 mt-2">
                                <input 
                                    type="radio" 
                                    name="votes[<?= $pos['id'] ?>]" 
                                    value="<?= $nom['nomination_id'] ?>" 
                                    <?= $required_html ?>
                                >
                                <span><?= htmlspecialchars($nom['candidate_name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 mt-2">No approved candidates for this position yet. Skipping this vote.</p>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

            <button 
                type="submit" 
                class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 font-medium"
            >
                Submit Votes
            </button>
        </form>
    </main>
</div>
</body>
</html>
