<?php
session_start();
require_once __DIR__ . "/../classes/notification.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$notifObj = new Notification();
$user_type = $_SESSION['role'] === 'admin' ? 'admin' : 'student';
$notifObj->markAllAsRead($_SESSION['user_id'], $user_type);

$redirect = $_SERVER['HTTP_REFERER'] ?? '../' . $user_type . '/dashboard.php';
header("Location: $redirect");
exit;
?>