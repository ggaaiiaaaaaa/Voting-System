<?php
session_start();
require_once __DIR__ . "/../classes/notification.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$notifObj = new Notification();
$notifObj->markAsRead($_GET['id']);

// Redirect back to previous page
$redirect = $_SERVER['HTTP_REFERER'] ?? '../' . ($_SESSION['role'] === 'admin' ? 'admin' : 'student') . '/dashboard.php';
header("Location: $redirect");
exit;
?>