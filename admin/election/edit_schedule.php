<?php
// election/edit_schedule.php
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../classes/election.php";

$electionObj = new Election();
$success = $error = "";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Election phase not found.";
    header("Location: manage_schedule.php");
    exit;
}

$id = $_GET['id'];
$phaseData = $electionObj->conn->prepare("SELECT * FROM election_phases WHERE id = ?");
$phaseData->execute([$id]);
$phase = $phaseData->fetch(PDO::FETCH_ASSOC);

if (!$phase) {
    $_SESSION['error'] = "Election phase not found.";
    header("Location: manage_schedule.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phase_name = trim($_POST['phase']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Determine status based on dates
    $now = date('Y-m-d H:i:s');
    if ($now < $start_date) $status = 'Upcoming';
    elseif ($now >= $start_date && $now <= $end_date) $status = 'Active';
    else $status = 'Completed';

    $updated = $electionObj->updateSchedule($id, $phase_name, $start_date, $end_date, $status);
    if ($updated) {
        $_SESSION['success'] = "Election phase updated successfully.";
        header("Location: manage_schedule.php");
        exit;
    } else {
        $error = "Failed to update election phase.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Election Phase</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f8f9fa] font-sans">
<div class="flex min-h-screen">

<?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Edit Election Phase</h2>
                <p class="text-sm text-gray-500">Update the details of the election phase.</p>
            </div>
            <a href="manage_schedule.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">⬅ Back</a>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($error): ?>
            <div class="mb-6 bg-[#FEEAEA] border-l-4 border-[#D02C4D] text-[#D02C4D] px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- EDIT FORM -->
        <section class="bg-white shadow rounded-xl p-6">
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Phase Name</label>
                    <input type="text" name="phase" required value="<?= htmlspecialchars($phase['phase']) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-[#D02C4D] focus:border-[#D02C4D]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Start Date</label>
                    <input type="date" name="start_date" required value="<?= htmlspecialchars($phase['start_date']) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-[#D02C4D] focus:border-[#D02C4D]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">End Date</label>
                    <input type="date" name="end_date" required value="<?= htmlspecialchars($phase['end_date']) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-[#D02C4D] focus:border-[#D02C4D]">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex justify-end mt-2">
                    <button type="submit" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-medium">Update Phase</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
