<?php
require_once __DIR__ . "/../../classes/position.php";
$posObj = new Position();

$position = [
    "position_name" => "",
    "position_order" => ""
];
$errors = [];

$maxOrder = $posObj->getMaxOrder();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $position["position_name"] = trim(htmlspecialchars($_POST["position_name"] ?? ""));
    $position["position_order"] = trim(htmlspecialchars($_POST["position_order"] ?? ""));

    if (empty($position["position_name"])) {
        $errors["position_name"] = "Position name is required";
    }

    if (empty($position["position_order"])) {
        $errors["position_order"] = "Position order is required";
    }
    if (empty($errors) && $posObj->isNameExist($position["position_name"])) {
        $errors["position_name"] = "This position name already exists.";
    }

    if (empty($errors)) {
        if ($posObj->isPositionOrderExist($position["position_order"])) {
            $posObj->overridePositionOrder($position["position_order"]);
        }
        
        $posObj->name = $position["position_name"];
        $posObj->position_order = $position["position_order"];

        if ($posObj->addPosition()) {
            header("Location: view_position.php");
            exit;
        } else {
            $errors["general"] = "Failed to add the position. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Position</title>
    <style>
        label { display: block; margin-top: 10px; }
        span, .error { color: red; font-size: 0.9em; }
        input[type="text"], select { width: 200px; padding: 5px; }
        input[type="submit"] { margin-top: 15px; }
    </style>
</head>
<body>
    <h1>Add Position</h1>

    <?php if (!empty($errors["general"])): ?>
        <p class="error"><?= $errors["general"] ?></p>
    <?php endif; ?>

    <form action="add_position.php" method="post">
        <div>
            <label for="position_name">Position Name <span>*</span></label>
            <input type="text" name="position_name" id="position_name" value="<?= htmlspecialchars($position["position_name"]) ?>">
            <p class="error"><?= $errors["position_name"] ?? "" ?></p>
        </div>

        <div>
            <label for="position_order">Position Order <span>*</span></label>
            <select name="position_order" id="position_order">
                <option value="">-- Select Order --</option>
                <?php for ($i = 1; $i <= $maxOrder; $i++): ?>
                    <option value="<?= $i ?>" <?= ($position["position_order"] == $i) ? "selected" : "" ?>>
                        <?= $posObj->numberToWords($i) ?> (Order <?= $i ?>)
                    </option>
                <?php endfor; ?>
            </select>
            <p class="error"><?= $errors["position_order"] ?? ""?></p>
        </div>

        <input type="submit" value="Save Position">
    </form>

    <br>
    <a href="view_position.php"><button>View Positions</button></a>
</body>
</html>