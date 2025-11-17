<?php
session_start();
require_once __DIR__ . "/../../classes/position.php";

$posObj = new Position();
$errors = [];
$position = [];

// ✅ Fetch position data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = trim($_GET['id']);
    $positionData = $posObj->fetchPosition($id);

    if (!$positionData) {
        $_SESSION['error'] = "Position not found.";
        header("Location: view_position.php");
        exit;
    }

    $position = $positionData;
    $maxOrder = $posObj->getMaxOrder(); // Get max order for dropdown
}

// ✅ Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = trim($_GET['id']);
    $position['position_name'] = trim($_POST['position_name']);
    $position['position_order'] = trim($_POST['position_order']);
    $position['max_nominees'] = trim($_POST['max_nominees']);
    $position['status'] = trim($_POST['status']);

    // ✅ Basic validation
    if (!$position['position_name']) $errors['position_name'] = "Position Name is required.";
    if (!$position['position_order']) $errors['position_order'] = "Position Order is required.";
    if (!$position['max_nominees']) $errors['max_nominees'] = "Max Nominees is required.";
    if (!$position['status']) $errors['status'] = "Status is required.";

    if (empty($errors)) {
        try {
            $currentData = $posObj->fetchPosition($id);
            $currentOrder = $currentData['position_order'];
            $newOrder = (int)$position['position_order'];

            // ✅ If the order changed, reorder other positions
            if ($currentOrder != $newOrder) {
                $posObj->reorderPositionsOnEdit($id, $newOrder);
            }

            // ✅ Update position
            $posObj->position_name = $position['position_name'];
            $posObj->position_order = $newOrder;
            $posObj->max_nominees = $position['max_nominees'];
            $posObj->status = $position['status'];

            if ($posObj->editPosition($id)) {
                $_SESSION['success'] = "Position updated successfully.";
                header("Location: view_position.php");
                exit;
            } else {
                $errors['general'] = "Failed to update position. Please try again.";
            }
        } catch (Exception $e) {
            $errors['general'] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Position</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex min-h-screen">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-[#D02C4D]">Edit Position</h2>
                <p class="text-sm text-gray-500">Modify the details of this position below.</p>
            </div>
            <a href="view_position.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">← Back</a>
        </div>

        <section class="bg-white shadow rounded-xl p-6 max-w-xl">
            <?php if (!empty($errors["general"])): ?>
                <div class="bg-[#FEEAEA] border-l-4 border-[#D02C4D] text-[#D02C4D] px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($errors["general"]) ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="space-y-6">
                <!-- POSITION NAME -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position Name <span class="text-red-500">*</span></label>
                    <input type="text" name="position_name" value="<?= htmlspecialchars($position["position_name"] ?? '') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors["position_name"])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors["position_name"]) ?></p>
                    <?php endif; ?>
                </div>

                <!-- POSITION ORDER -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position Order <span class="text-red-500">*</span></label>
                    <select name="position_order" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                        <option value="">-- Select Order --</option>
                        <?php
                        if (isset($maxOrder)):
                            for ($i = 1; $i <= $maxOrder + 1; $i++): ?>
                                <option value="<?= $i ?>" <?= (($position["position_order"] ?? '') == $i) ? 'selected' : '' ?>>
                                    <?= $posObj->numberToWords($i) ?> (Order <?= $i ?>)
                                </option>
                            <?php endfor;
                        endif;
                        ?>
                    </select>
                    <?php if (!empty($errors["position_order"])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors["position_order"]) ?></p>
                    <?php endif; ?>
                </div>

                <!-- MAX NOMINEES -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Nominees <span class="text-red-500">*</span></label>
                    <input type="number" name="max_nominees" value="<?= htmlspecialchars($position["max_nominees"] ?? '') ?>" min="1" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                    <?php if (!empty($errors["max_nominees"])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors["max_nominees"]) ?></p>
                    <?php endif; ?>
                </div>

                <!-- STATUS -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                        <option value="Active" <?= (($position["status"] ?? '') === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= (($position["status"] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <?php if (!empty($errors["status"])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors["status"]) ?></p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT -->
                <div class="pt-4">
                    <button type="submit" class="bg-[#D02C4D] hover:bg-[#A0223B] text-white px-6 py-2 rounded-lg font-semibold">Save Changes</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
