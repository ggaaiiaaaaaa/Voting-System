<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/email_config.php";

// Manual PHPMailer includes
require_once PHPMAILER_PATH . 'Exception.php';
require_once PHPMAILER_PATH . 'PHPMailer.php';
require_once PHPMAILER_PATH . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notification {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // ============ SYSTEM NOTIFICATIONS ============
    
    public function createNotification($user_id, $user_type, $title, $message, $type) {
        if (!ENABLE_SYSTEM_NOTIFICATIONS) return false;
        
        try {
            $sql = "INSERT INTO notifications (user_id, user_type, title, message, type, is_read) 
                    VALUES (:user_id, :user_type, :title, :message, :type, 0)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':user_type' => $user_type,
                ':title' => $title,
                ':message' => $message,
                ':type' => $type
            ]);
        } catch (PDOException $e) {
            error_log("Create notification error: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadNotifications($user_id, $user_type) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id AND user_type = :user_type AND is_read = 0 
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':user_type' => $user_type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllNotifications($user_id, $user_type, $limit = 50) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id AND user_type = :user_type 
                ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notification_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $notification_id]);
    }

    public function markAllAsRead($user_id, $user_type) {
        $sql = "UPDATE notifications SET is_read = 1 
                WHERE user_id = :user_id AND user_type = :user_type";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $user_id, ':user_type' => $user_type]);
    }

    public function getUnreadCount($user_id, $user_type) {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = :user_id AND user_type = :user_type AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':user_type' => $user_type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // ============ EMAIL NOTIFICATIONS ============
    
    public function sendEmail($to_email, $subject, $body, $isHTML = true) {
        if (!ENABLE_EMAIL_NOTIFICATIONS) return false;

        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to_email);

            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if (!$isHTML) {
                $mail->Body = strip_tags($body);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email send error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function queueEmail($to_email, $subject, $body) {
        try {
            $sql = "INSERT INTO email_queue (to_email, subject, body, status) 
                    VALUES (:to_email, :subject, :body, 'pending')";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':to_email' => $to_email,
                ':subject' => $subject,
                ':body' => $body
            ]);
        } catch (PDOException $e) {
            error_log("Queue email error: " . $e->getMessage());
            return false;
        }
    }

    // ============ SPECIFIC NOTIFICATION FUNCTIONS ============
    
    public function notifyElectionStarted($student_id, $student_email, $election_name) {
        // System notification
        $this->createNotification(
            $student_id,
            'student',
            'Election Started',
            "The election '{$election_name}' has started! You can now nominate candidates and cast your vote.",
            'election_started'
        );

        // Email notification
        $subject = "Election Started - {$election_name}";
        $body = $this->getEmailTemplate('election_started', [
            'election_name' => $election_name,
            'voting_url' => SYSTEM_URL . '/student/voting.php'
        ]);
        
        return $this->sendEmail($student_email, $subject, $body);
    }

    public function notifyNominationApproved($student_id, $student_email, $position_name) {
        // System notification
        $this->createNotification(
            $student_id,
            'student',
            'Nomination Approved',
            "Your nomination for the position of {$position_name} has been approved!",
            'nomination_approved'
        );

        // Email notification
        $subject = "Nomination Approved - {$position_name}";
        $body = $this->getEmailTemplate('nomination_approved', [
            'position_name' => $position_name,
            'dashboard_url' => SYSTEM_URL . '/student/student_dashboard.php'
        ]);
        
        return $this->sendEmail($student_email, $subject, $body);
    }

    public function notifyElectionEnded($student_id, $student_email, $election_name) {
        // System notification
        $this->createNotification(
            $student_id,
            'student',
            'Election Ended',
            "The election '{$election_name}' has ended. Results are now available!",
            'election_ended'
        );

        // Email notification
        $subject = "Election Ended - Results Available";
        $body = $this->getEmailTemplate('election_ended', [
            'election_name' => $election_name,
            'results_url' => SYSTEM_URL . '/student/view_results.php'
        ]);
        
        return $this->sendEmail($student_email, $subject, $body);
    }

    public function notifyAdminNewNomination($admin_id, $admin_email, $student_name, $position_name) {
        // System notification
        $this->createNotification(
            $admin_id,
            'admin',
            'New Nomination Submitted',
            "{$student_name} has submitted a nomination for {$position_name}.",
            'new_nomination'
        );

        // Email notification
        $subject = "New Nomination Submitted";
        $body = $this->getEmailTemplate('admin_new_nomination', [
            'student_name' => $student_name,
            'position_name' => $position_name,
            'nominations_url' => SYSTEM_URL . '/admin/nomination/view_nomination.php'
        ]);
        
        return $this->sendEmail($admin_email, $subject, $body);
    }

    public function notifyAdminNewVote($admin_id, $admin_email, $student_name) {
        // System notification
        $this->createNotification(
            $admin_id,
            'admin',
            'New Vote Cast',
            "{$student_name} has cast their vote.",
            'new_vote'
        );

        // Email notification  
        $subject = "New Vote Cast";
        $body = $this->getEmailTemplate('admin_new_vote', [
            'student_name' => $student_name,
            'dashboard_url' => SYSTEM_URL . '/admin/admin_dashboard.php'
        ]);
        
        return $this->sendEmail($admin_email, $subject, $body);
    }

    // ============ EMAIL TEMPLATES ============
    
    private function getEmailTemplate($type, $data) {
        $header = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px;">
            <div style="background: #D02C4D; padding: 20px; text-align: center;">
                <h1 style="color: white; margin: 0;">WMSU iElect</h1>
            </div>
            <div style="background: white; padding: 30px; margin-top: 20px;">';
        
        $footer = '
            </div>
            <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
                <p>This is an automated message from WMSU iElect System.</p>
                <p>&copy; ' . date('Y') . ' Western Mindanao State University</p>
            </div>
        </div>';

        $body = '';
        
        switch ($type) {
            case 'election_started':
                $body = "
                    <h2 style='color: #D02C4D;'>Election Has Started!</h2>
                    <p>Dear Student,</p>
                    <p>The election <strong>{$data['election_name']}</strong> has officially started.</p>
                    <p>You can now:</p>
                    <ul>
                        <li>Nominate candidates for various positions</li>
                        <li>Cast your vote for your preferred candidates</li>
                    </ul>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['voting_url']}' 
                           style='background: #D02C4D; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Vote Now
                        </a>
                    </p>
                    <p>Good luck!</p>";
                break;

            case 'nomination_approved':
                $body = "
                    <h2 style='color: #D02C4D;'>Your Nomination Has Been Approved!</h2>
                    <p>Dear Student,</p>
                    <p>Great news! Your nomination for the position of <strong>{$data['position_name']}</strong> 
                       has been approved by the administrator.</p>
                    <p>You are now officially a candidate for this position. Good luck!</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['dashboard_url']}' 
                           style='background: #D02C4D; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            View Dashboard
                        </a>
                    </p>";
                break;

            case 'election_ended':
                $body = "
                    <h2 style='color: #D02C4D;'>Election Results Available</h2>
                    <p>Dear Student,</p>
                    <p>The election <strong>{$data['election_name']}</strong> has officially ended.</p>
                    <p>The results are now available for viewing. Thank you for your participation!</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['results_url']}' 
                           style='background: #D02C4D; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            View Results
                        </a>
                    </p>";
                break;

            case 'admin_new_nomination':
                $body = "
                    <h2 style='color: #D02C4D;'>New Nomination Submitted</h2>
                    <p>Dear Admin,</p>
                    <p><strong>{$data['student_name']}</strong> has submitted a nomination for the position 
                       of <strong>{$data['position_name']}</strong>.</p>
                    <p>Please review and approve or reject this nomination.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['nominations_url']}' 
                           style='background: #D02C4D; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Review Nomination
                        </a>
                    </p>";
                break;

            case 'admin_new_vote':
                $body = "
                    <h2 style='color: #D02C4D;'>New Vote Cast</h2>
                    <p>Dear Admin,</p>
                    <p><strong>{$data['student_name']}</strong> has successfully cast their vote.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['dashboard_url']}' 
                           style='background: #D02C4D; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            View Dashboard
                        </a>
                    </p>";
                break;
        }

        return $header . $body . $footer;
    }
}
?>