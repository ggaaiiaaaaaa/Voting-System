<?php
require_once __DIR__ . "/../../classes/nomination.php";
$nomObj = new Nomination();

if (!isset($_GET['id'])) {
    header("Location: view_nomination.php");
    exit;
}

$nom_id = trim(htmlspecialchars($_GET['id']));
$nomData = $nomObj->fetchNomination($nom_id);

if (!$nomData) {
    exit("<p>Nomination not found. <a href='view_nomination.php'>Back</a></p>");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

// After: $nomObj->approveNomination($nom_id);
if ($action === "approve") {
    $nomObj->approveNomination($nom_id);
    
    require_once __DIR__ . "/../../classes/notification.php";
    require_once __DIR__ . "/../../classes/student.php";
    
    $notifObj = new Notification();
    $studentObj = new Student();
    
    // Get student email
    $student_email = $studentObj->getStudentEmailById($nomData['nominee_id']);
    
    // Get position name
    $position_name = $nomData['position_name'];
    
    // Send notification
    if ($student_email) {
        $notifObj->notifyNominationApproved(
            $nomData['nominee_id'],
            $student_email,
            $position_name
        );
    }
    
    $message = "Nomination approved and student notified!";
} elseif ($action === "reject") {
        $nomObj->rejectNomination($nom_id);
        $message = "Nomination rejected!";
    }

    // Refresh nomination data
    $nomData = $nomObj->fetchNomination($nom_id);
        
    header("Location: view_nomination.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Nomination</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <!-- HEADER -->
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Approve Nomination</h2>
                <p class="text-sm text-gray-300 mt-1">Review and approve or reject a student nomination.</p>
            </div>
            <a href="view_nomination.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">← Back</a>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($message): ?>
            <div class="mb-6 bg-green-500/20 border-l-4 border-green-600 text-green-400 px-4 py-3 rounded animate-fade-in-up">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- FORM SECTION -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up max-w-xl">
            <div class="space-y-4">
                <p class="text-white"><strong>Nominator:</strong> <?= htmlspecialchars($nomData['nominator_name']) ?></p>
                <p class="text-white"><strong>Nominee:</strong> <?= htmlspecialchars($nomData['nominee_name']) ?></p>
                <p class="text-white"><strong>Position:</strong> <?= htmlspecialchars($nomData['position_name']) ?></p>
                <p class="text-white"><strong>Status:</strong>
                    <span class="px-2 py-1 text-xs rounded-full <?= $nomData['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($nomData['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                        <?= htmlspecialchars($nomData['status']) ?>
                    </span>
                </p>
            </div>

            <form method="post" class="flex gap-3 mt-6">
                <button type="submit" name="action" value="approve" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">Approve</button>
                <button type="submit" name="action" value="reject" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">Reject</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
