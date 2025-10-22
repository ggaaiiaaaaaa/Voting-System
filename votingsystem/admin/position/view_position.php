<?php

require_once __DIR__ . "/../../classes/position.php";

$posObj = new Position();
$positions = $posObj->viewPosition();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Positions</title>
</head>
<body>
    <h1>List of Positions</h1>
    <button><a href="add_position.php">Add Position</a></button>
    <table>
        <tr>
            <th>#</th>
            <th>Position Name</th>
            <th>Position Order</th>
            <th>Actions</th>
        </tr>
        <?php if (!empty($positions)): ?>
            <?php $i = 1; foreach ($positions as $pos): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($pos["name"]) ?></td>
                    <td><?= htmlspecialchars($pos["position_order"]) ?></td>
                    <td>
                        <a class="action-btn edit" href="edit_position.php?id=<?= $pos['id'] ?>">Edit</a>
                        <a class="action-btn delete" href="delete_position.php?id=<?= $pos['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this position?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No positions found.</td></tr>
        <?php endif; ?>
    </table>

    <br>
    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
