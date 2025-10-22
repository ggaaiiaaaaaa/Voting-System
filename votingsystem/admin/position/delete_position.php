<?php

require_once __DIR__ . "/../../classes/position.php";
$posObj = new Position();

if (!isset($_GET['id'])) {
    header("Location: view_position.php");
    exit;
}

$p_id = trim(htmlspecialchars($_GET['id']));
$position = $posObj->fetchPosition($p_id);

if (!$position) {
    echo "<p>Position not found.</p>";
    echo "<a href='view_position.php'>← Back to Positions</a>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["confirm"])) {
        if ($posObj->deletePosition($p_id)) {
            header("Location: view_position.php");
            exit;
        } else {
            $error = "Failed to delete position.";
        }
    } else {
        header("Location: view_position.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Position</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Delete Position</h1>

    <p>Are you sure you want to delete this position?</p>

    <table>
        <tr>
            <th>Position Name</th>
            <td><?= htmlspecialchars($position["name"]) ?></td>
        </tr>
        <tr>
            <th>Position Order</th>
            <td><?= htmlspecialchars($position["position_order"]) ?></td>
        </tr>
    </table>

    <br>

    <?php if (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="submit" name="confirm" value="Yes, Delete" style="background-color:#e74c3c;">
        <input type="submit" name="cancel" value="Cancel" style="background-color:#6c757d;">
    </form>

    <br>
    <a href="view_position.php">← Back to Positions</a>
</body>
</html>
