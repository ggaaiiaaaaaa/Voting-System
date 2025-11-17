<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/notification.php';

$db = new Database();
$conn = $db->connect();
$notif = new Notification();

// Get pending emails
$stmt = $conn->prepare("
    SELECT * FROM email_queue 
    WHERE status = 'pending' AND attempts < 3 
    ORDER BY created_at ASC 
    LIMIT 10
");
$stmt->execute();
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($emails as $email) {
    $result = $notif->sendEmail($email['to_email'], $email['subject'], $email['body']);
    
    if ($result) {
        // Mark as sent
        $update = $conn->prepare("
            UPDATE email_queue 
            SET status = 'sent', sent_at = NOW() 
            WHERE id = ?
        ");
        $update->execute([$email['id']]);
    } else {
        // Increment attempts
        $update = $conn->prepare("
            UPDATE email_queue 
            SET attempts = attempts + 1, 
                status = CASE WHEN attempts >= 2 THEN 'failed' ELSE 'pending' END 
            WHERE id = ?
        ");
        $update->execute([$email['id']]);
    }
}

echo "Processed " . count($emails) . " emails\n";
?>