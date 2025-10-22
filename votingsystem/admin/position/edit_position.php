<?php

require_once __DIR__ . "/../../classes/position.php";
$posObj = new Position();

$position = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $p_id = trim(htmlspecialchars($_GET['id']));
    $positionData = $posObj->fetchPosition($p_id);
    
    if (!$positionData) {
        exit("No Position Found <a href='view_position.php'>Back</a>");
    }
    
    $position["name"] = $positionData["name"];
    $position["order"] = $positionData["position_order"];
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_id = trim(htmlspecialchars($_GET['id'] ?? ""));
    $position["name"] = trim(htmlspecialchars($_POST["name"]));
    $position["order"] = trim(htmlspecialchars($_POST["order"]));

    if (empty($position["name"])) {
        $errors["name"] = "Position name is required";
    }

    if (empty($position["order"])) {
        $errors["order"] = "Position order is required";
    } elseif (!is_numeric($position["order"])) {
        $errors["order"] = "Order must be a number";
    }

    if (empty($errors) && $posObj->isPositionExist($position["name"], $position["order"], $p_id)) {
        $errors["name"] = "This position name or order already exists.";
    }

    if (empty($errors)) {
        $posObj->name = $position["name"];
        $posObj->position_order = $position["order"];
        if ($posObj->editPosition($p_id)) {
            header("Location: view_position.php");
            exit;
        } else {
            echo "Failed to edit position!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Position</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Edit Position</h1>
    <form method="post">
        <label>Position Name *</label>
        <input type="text" name="name" value="<?= $position['name'] ?? '' ?>">
        <span class="error"><?= $errors["name"] ?? "" ?></span>

        <label>Position Order *</label>
        <input type="text" name="order" value="<?= $position['order'] ?? '' ?>">
        <span class="error"><?= $errors["order"] ?? "" ?></span>

        <br><br>
        <input type="submit" value="Save Changes">
    </form>
    <br>
    <a href="view_position.php">← Back to Positions</a>
</body>
</html>
