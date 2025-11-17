<?php
session_start();
require_once __DIR__ . "/../classes/notification.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$notifObj = new Notification();
$user_type = $_SESSION['role'] === 'admin' ? 'admin' : 'student';
$count = $notifObj->getUnreadCount($_SESSION['user_id'], $user_type);

echo json_encode(['count' => $count]);
?>