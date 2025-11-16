<?php
session_start();
require_once __DIR__ . "/../../classes/nomination.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid nomination ID.";
    header("Location: view_nomination.php");
    exit;
}

$nominationId = intval($_GET['id']); // sanitize input
$nominationObj = new Nomination();

// Attempt to delete the nomination
if ($nominationObj->deleteNomination($nominationId)) {
    $_SESSION['success'] = "Nomination deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete nomination. Please try again.";
}

// Redirect back to the nominations page
header("Location: view_nomination.php");
exit;
?>
