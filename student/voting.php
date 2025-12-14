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
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg"> Vote for Candidates</h2>
                <p class="text-sm text-gray-300 mt-1">Select one candidate per position.</p>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-500/20 backdrop-blur-sm border border-green-500/30 text-green-300 px-6 py-4 rounded-xl shadow-lg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-500/20 backdrop-blur-sm border border-red-500/30 text-red-300 px-6 py-4 rounded-xl shadow-lg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- VOTING FORM -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up max-w-2xl">
            <form method="POST" class="space-y-6">

                <?php foreach ($positions as $pos): ?>
                    <?php $nominations = $nominationsByPosition[$pos['id']]; ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-300 mb-2">
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
                                <label class="flex items-center gap-2 mt-2 text-gray-300">
                                    <input 
                                        type="radio" 
                                        name="votes[<?= $pos['id'] ?>]" 
                                        value="<?= $nom['nomination_id'] ?>" 
                                        <?= $required_html ?>
                                        class="text-blue-500"
                                    >
                                    <span><?= htmlspecialchars($nom['candidate_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 mt-2">No approved candidates for this position yet. Skipping this vote.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- SUBMIT BUTTON -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">
                        Submit Votes
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
