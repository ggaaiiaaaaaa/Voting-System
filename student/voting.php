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

$election_status = $electionObj->getAdminControlledStatus();

if ($election_status !== 'Ongoing') {
    if ($election_status === 'Ended') {
        header("Location: view_results.php");
        exit;
    } else {
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
$showModal = false;
$showConfirmationModal = false;
$voteDetails = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['votes']) && !isset($_POST['confirm_vote'])) {
    $votes = $_POST['votes']; // Array: position_id => nomination_id

    // Validate all required positions have been voted for
    $voted_position_ids = array_keys($votes);
    $missing_votes = array_diff($required_position_ids, $voted_position_ids);

    if (!empty($missing_votes)) {
        $_SESSION['error'] = "Please vote for all required positions before submitting.";
        header("Location: voting.php");
        exit;
    }

    // Prepare vote details for confirmation modal
    foreach ($votes as $position_id => $nomination_id) {
        $position = $posObj->fetchPosition($position_id);
        $nominations = $nominationsByPosition[$position_id];
        
        foreach ($nominations as $nom) {
            if ($nom['nomination_id'] == $nomination_id) {
                $voteDetails[] = [
                    'position' => $position['position_name'],
                    'candidate' => $nom['candidate_name'],
                    'position_id' => $position_id,
                    'nomination_id' => $nomination_id
                ];
                break;
            }
        }
    }

    $showConfirmationModal = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['votes']) && isset($_POST['confirm_vote'])) {
    $votes = $_POST['votes']; // Array: position_id => nomination_id

    // Validate all required positions have been voted for
    $voted_position_ids = array_keys($votes);
    $missing_votes = array_diff($required_position_ids, $voted_position_ids);

    if (!empty($missing_votes)) {
        $_SESSION['error'] = "Please vote for all required positions before submitting.";
        header("Location: voting.php");
        exit;
    }

    // Prepare vote details for modal
    foreach ($votes as $position_id => $nomination_id) {
        $position = $posObj->fetchPosition($position_id);
        $nominations = $nominationsByPosition[$position_id];
        
        foreach ($nominations as $nom) {
            if ($nom['nomination_id'] == $nomination_id) {
                $voteDetails[] = [
                    'position' => $position['position_name'],
                    'candidate' => $nom['candidate_name']
                ];
                break;
            }
        }
    }

    // Submit all votes
    $result = $electionObj->submitVote($student_id, $votes);

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
        
        // Show modal instead of redirecting immediately
        $showModal = true;
        $_SESSION['vote_success'] = true;
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
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            animation: fadeIn 0.3s ease-in;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.4s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #4ade80;
            stroke-miterlimit: 10;
            margin: 10px auto;
            box-shadow: inset 0px 0px 0px #4ade80;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
        }

        .checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #4ade80;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }

        @keyframes scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }

        @keyframes fill {
            100% { box-shadow: inset 0px 0px 0px 30px #4ade80; }
        }

        .vote-item {
            animation: slideInRight 0.5s ease-out backwards;
        }

        .vote-item:nth-child(1) { animation-delay: 0.1s; }
        .vote-item:nth-child(2) { animation-delay: 0.2s; }
        .vote-item:nth-child(3) { animation-delay: 0.3s; }
        .vote-item:nth-child(4) { animation-delay: 0.4s; }
        .vote-item:nth-child(5) { animation-delay: 0.5s; }
        .vote-item:nth-child(6) { animation-delay: 0.6s; }

        @keyframes slideInRight {
            from {
                transform: translateX(50px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">🗳️ Vote for Candidates</h2>
                <p class="text-sm text-gray-300 mt-1">Select one candidate per position.</p>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
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
                                <label class="flex items-center gap-2 mt-2 text-gray-300 hover:bg-white/5 p-2 rounded-lg transition-colors cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="votes[<?= $pos['id'] ?>]" 
                                        value="<?= $nom['nomination_id'] ?>" 
                                        <?= $required_html ?>
                                        class="text-blue-500 w-4 h-4"
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

<!-- CONFIRMATION MODAL -->
<?php if ($showConfirmationModal): ?>
<div id="confirmationModal" class="modal show">
    <div class="modal-content">
        <h2 class="text-3xl font-bold text-white text-center mb-2">
            Confirm Your Votes
        </h2>
        <p class="text-white/80 text-center mb-6">
            Please review your selections before submitting.
        </p>

        <!-- Vote Summary -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mb-6 border border-white/20">
            <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Your Votes:
            </h3>
            <div class="space-y-3">
                <?php foreach ($voteDetails as $vote): ?>
                    <div class="vote-item flex items-start gap-3 bg-white/5 p-3 rounded-lg border border-white/10">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white/60 font-medium"><?= htmlspecialchars($vote['position']) ?></p>
                            <p class="text-white font-semibold"><?= htmlspecialchars($vote['candidate']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <button
                onclick="closeConfirmationModal()"
                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
                Edit Votes
            </button>
            <form method="POST" class="flex-1">
                <?php foreach ($voteDetails as $vote): ?>
                    <input type="hidden" name="votes[<?= $vote['position_id'] ?>]" value="<?= $vote['nomination_id'] ?>">
                <?php endforeach; ?>
                <input type="hidden" name="confirm_vote" value="1">
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
                    Confirm & Submit Votes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}
</script>
<?php endif; ?>

<!-- SUCCESS MODAL -->
<?php if ($showModal): ?>
<div id="successModal" class="modal show">
    <div class="modal-content">
        <!-- Success Checkmark Animation -->
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>

        <h2 class="text-3xl font-bold text-white text-center mb-2">
             Vote Submitted Successfully!
        </h2>
        <p class="text-white/80 text-center mb-6">
            Thank you for participating in the election!
        </p>

        <!-- Vote Summary -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mb-6 border border-white/20">
            <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Your Votes:
            </h3>
            <div class="space-y-3">
                <?php foreach ($voteDetails as $vote): ?>
                    <div class="vote-item flex items-start gap-3 bg-white/5 p-3 rounded-lg border border-white/10">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white/60 font-medium"><?= htmlspecialchars($vote['position']) ?></p>
                            <p class="text-white font-semibold"><?= htmlspecialchars($vote['candidate']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Redirect Message -->
        <div class="text-center mb-4">
            <p class="text-white/80 text-sm">
                Redirecting to dashboard in <span id="countdown" class="font-bold text-white">5</span> seconds...
            </p>
        </div>

        <!-- Action Button -->
        <button 
            onclick="redirectNow()"
            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">
            Go to Dashboard Now
        </button>
    </div>
</div>

<script>
let countdown = 5;
const countdownEl = document.getElementById('countdown');
const modal = document.getElementById('successModal');

const timer = setInterval(() => {
    countdown--;
    countdownEl.textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(timer);
        redirectNow();
    }
}, 1000);

function redirectNow() {
    clearInterval(timer);
    window.location.href = 'student_dashboard.php?vote_success=1';
}

// Prevent closing modal by clicking outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        e.preventDefault();
    }
});

// Prevent back button during redirect
window.history.pushState(null, "", window.location.href);
window.onpopstate = function() {
    window.history.pushState(null, "", window.location.href);
};
</script>
<?php endif; ?>

</body>
</html>