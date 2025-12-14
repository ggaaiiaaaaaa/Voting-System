<?php
session_start();
require_once __DIR__ . "/../../classes/position.php";

$posObj = new Position();
$errors = [];
$position = [];
$maxOrder = $posObj->getMaxOrder(); // ✅ Get current max order

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position['position_name'] = trim($_POST['position_name']);
    $position['position_order'] = trim($_POST['position_order']);
    $position['max_nominees'] = trim($_POST['max_nominees']);
    $position['status'] = 'Active'; // Default to Active

    // ✅ Validation
    if (empty($position['position_name'])) $errors['position_name'] = "Position Name is required.";
    if (empty($position['position_order'])) $errors['position_order'] = "Position Order is required.";
    if (empty($position['max_nominees'])) $errors['max_nominees'] = "Max Nominees is required.";

    if (empty($errors)) {
        // ✅ Duplicate name check
        if ($posObj->isNameExist($position['position_name'])) {
            $errors['general'] = "Position name already exists.";
        } else {
            try {
                // ✅ If position order exists, shift others down
                $posObj->shiftPositionsDown($position['position_order']);

                // ✅ Assign data
                $posObj->position_name = $position['position_name'];
                $posObj->position_order = $position['position_order'];
                $posObj->max_nominees = $position['max_nominees'];
                $posObj->status = $position['status'];

                // ✅ Add position
                if ($posObj->addPosition()) {
                    $_SESSION['success'] = "Position added successfully and reordered automatically.";
                    header("Location: view_position.php");
                    exit;
                } else {
                    $errors['general'] = "Failed to add position. Please try again.";
                }
            } catch (Exception $e) {
                $errors['general'] = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Position</title>
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
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Add New Position</h2>
                <p class="text-sm text-gray-300 mt-1">Fill in the form to add a new position record.</p>
            </div>
            <a href="view_position.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">← Back</a>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if (!empty($errors['general'])): ?>
            <div class="mb-6 bg-red-500/20 border-l-4 border-red-600 text-red-400 px-4 py-3 rounded animate-fade-in-up">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>

        <!-- FORM SECTION -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up max-w-xl">
            <form action="" method="POST" class="space-y-6">
                <!-- POSITION NAME -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Position Name <span class="text-red-500">*</span></label>
                    <input type="text" name="position_name" value="<?= htmlspecialchars($position['position_name'] ?? '') ?>" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['position_name'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['position_name'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- POSITION ORDER -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Position Order <span class="text-red-500">*</span></label>
                    <select name="position_order" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Select Order --</option>
                        <?php for ($i = 1; $i <= $maxOrder + 1; $i++): ?>
                            <option value="<?= $i ?>" <?= (($position["position_order"] ?? null) == $i) ? "selected" : "" ?>>
                                <?= $posObj->numberToWords($i) ?> (Order <?= $i ?>)
                            </option>
                        <?php endfor; ?>
                    </select>
                    <?php if (!empty($errors['position_order'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['position_order'] ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-300 mt-1">If this order already exists, the lower positions will be shifted down automatically.</p>
                </div>

                <!-- MAX NOMINEES -->
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Max Nominees <span class="text-red-500">*</span></label>
                    <input type="number" name="max_nominees" value="<?= htmlspecialchars($position['max_nominees'] ?? '') ?>" min="1" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <?php if (!empty($errors['max_nominees'])): ?>
                        <p class="text-red-400 text-sm mt-1"><?= $errors['max_nominees'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transform hover:scale-105 transition-all duration-300">Save Position</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
