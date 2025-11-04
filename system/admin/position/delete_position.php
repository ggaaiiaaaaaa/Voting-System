<?php
// admin/position/delete_position.php
session_start();
require_once __DIR__ . "/../../classes/position.php";

$posObj = new Position();

// ✅ Validate request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid position ID.";
    header("Location: view_position.php");
    exit;
}

$positionId = intval($_GET['id']);

try {
    // ✅ Fetch position first (to get its order before deletion)
    $position = $posObj->getPositionById($positionId);
    if (!$position) {
        $_SESSION['error'] = "Position not found or already deleted.";
        header("Location: view_position.php");
        exit;
    }

    $deletedOrder = $position['position_order'];

    // ✅ Delete the position
    $result = $posObj->deletePosition($positionId);

    if ($result) {
        // ✅ Shift all lower positions up to fill the gap
        $posObj->shiftPositionsUp($deletedOrder);

        // ✅ Log the action (if logging system is present)
        if (isset($_SESSION['user_id'])) {
            $posObj->logAction($_SESSION['user_id'], "Deleted position", "Deleted position '{$position['position_name']}' (Order {$deletedOrder})");
        }

        $_SESSION['success'] = "Position '{$position['position_name']}' deleted successfully. Order numbers have been adjusted automatically.";
    } else {
        $_SESSION['error'] = "Failed to delete the position. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
}

// ✅ Redirect back to the positions page
header("Location: view_position.php");
exit;
?>
