<?php
/**
 * Integrated Nomination Class
 * Updated to match voting.sql schema
 */

require_once __DIR__ . "/../config/database.php";

class Nomination {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // ==================== VIEW NOMINATIONS ====================
    
    public function viewNominationsWithDetails() {
        try {
            $sql = "
                SELECT 
                    n.id,
                    nominator.fullname AS nominator_name,
                    nominator.student_id AS nominator_student_id,
                    nominee.fullname AS nominee_name,
                    nominee.student_id AS nominee_student_id,
                    p.position_name,
                    n.status,
                    n.created_at
                FROM nominations n
                INNER JOIN students AS nominator ON n.nominator_id = nominator.id
                INNER JOIN students AS nominee ON n.nominee_id = nominee.id
                INNER JOIN positions AS p ON n.position_id = p.id
                ORDER BY n.created_at DESC
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("viewNominationsWithDetails Error: " . $e->getMessage());
            return [];
        }
    }

    public function fetchNominations($positionID = null) {
        try {
            $sql = "
                SELECT 
                    n.id,
                    n.nominator_id,
                    nominator.fullname AS nominator_name,
                    n.nominee_id,
                    nominee.fullname AS nominee_name,
                    n.position_id,
                    p.position_name,
                    n.status,
                    n.created_at
                FROM nominations n
                INNER JOIN students AS nominator ON n.nominator_id = nominator.id
                INNER JOIN students AS nominee ON n.nominee_id = nominee.id
                INNER JOIN positions AS p ON n.position_id = p.id
            ";
            
            if ($positionID) {
                $sql .= " WHERE n.position_id = :positionID";
            }
            
            $sql .= " ORDER BY p.position_order ASC, nominee.fullname ASC";

            $stmt = $this->conn->prepare($sql);
            if ($positionID) {
                $stmt->bindParam(":positionID", $positionID, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchNominations Error: " . $e->getMessage());
            return [];
        }
    }

    public function fetchNomination($id) {
        try {
            $sql = "
                SELECT 
                    n.id,
                    n.nominator_id,
                    nominator.fullname AS nominator_name,
                    n.nominee_id,
                    nominee.fullname AS nominee_name,
                    n.position_id,
                    p.position_name,
                    n.status,
                    n.created_at,
                    n.updated_at
                FROM nominations n
                INNER JOIN students AS nominator ON n.nominator_id = nominator.id
                INNER JOIN students AS nominee ON n.nominee_id = nominee.id
                INNER JOIN positions AS p ON n.position_id = p.id
                WHERE n.id = :id
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchNomination Error: " . $e->getMessage());
            return null;
        }
    }

    // ==================== NOMINATION ACTIONS ====================
    
    public function approveNomination($nomID, $userID = null) {
        try {
            // Get nomination details before approving
            $nomination = $this->fetchNomination($nomID);
            
            if (!$nomination) {
                return false;
            }
            
            $stmt = $this->conn->prepare("UPDATE nominations SET status = 'Approved' WHERE id = ?");
            $success = $stmt->execute([$nomID]);
            
            if ($success) {
                // Log action
                if ($userID) {
                    $this->logAction($userID, 'admin', "Approved nomination", "Nomination ID: $nomID");
                }
                
                // Send system notification
                $this->notifyNominationApproved(
                    $nomination['nominee_id'],
                    $nomination['position_name']
                );
                
                // Send email notification
                $this->sendEmailNominationApproved(
                    $nomination['nominee_id'],
                    $nomination['position_name']
                );
            }
            
            return $success;
        } catch (PDOException $e) {
            error_log("approveNomination Error: " . $e->getMessage());
            return false;
        }
    }

    public function rejectNomination($nomID, $userID = null) {
        try {
            // Get nomination details before rejecting
            $nomination = $this->fetchNomination($nomID);
            
            if (!$nomination) {
                return false;
            }
            
            $stmt = $this->conn->prepare("UPDATE nominations SET status = 'Rejected' WHERE id = ?");
            $success = $stmt->execute([$nomID]);
            
            if ($success) {
                // Log action
                if ($userID) {
                    $this->logAction($userID, 'admin', "Rejected nomination", "Nomination ID: $nomID");
                }
                
                // Send system notification
                $this->notifyNominationRejected(
                    $nomination['nominee_id'],
                    $nomination['position_name']
                );
                
                // Send email notification
                $this->sendEmailNominationRejected(
                    $nomination['nominee_id'],
                    $nomination['position_name']
                );
            }
            
            return $success;
        } catch (PDOException $e) {
            error_log("rejectNomination Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteNomination($nomID, $userID = null) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM nominations WHERE id = ?");
            $success = $stmt->execute([$nomID]);
            
            if ($success && $userID) {
                $this->logAction($userID, 'admin', "Deleted nomination", "Nomination ID: $nomID");
            }
            
            return $success;
        } catch (PDOException $e) {
            error_log("deleteNomination Error: " . $e->getMessage());
            return false;
        }
    }

    // ==================== STATISTICS ====================
    
    public function countNominationsByStatus($status) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM nominations WHERE status = ?");
            $stmt->execute([$status]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("countNominationsByStatus Error: " . $e->getMessage());
            return 0;
        }
    }

    public function countPendingNominations() {
        return $this->countNominationsByStatus('Pending');
    }

    public function countApprovedNominations() {
        return $this->countNominationsByStatus('Approved');
    }

    public function countRejectedNominations() {
        return $this->countNominationsByStatus('Rejected');
    }

    public function getNominationsByPosition($position_id) {
        return $this->fetchNominations($position_id);
    }

    // ==================== VALIDATION ====================
    
    public function isAlreadyNominated($nominator_id, $position_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id FROM nominations 
                WHERE nominator_id = ? AND position_id = ?
            ");
            $stmt->execute([$nominator_id, $position_id]);
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("isAlreadyNominated Error: " . $e->getMessage());
            return false;
        }
    }

    public function isStudentNominated($nominee_id, $position_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id FROM nominations 
                WHERE nominee_id = ? AND position_id = ? AND status = 'Approved'
            ");
            $stmt->execute([$nominee_id, $position_id]);
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("isStudentNominated Error: " . $e->getMessage());
            return false;
        }
    }

    // ==================== AUDIT LOG ====================
    
    public function logAction($userID, $userType, $action, $details = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO audit_log (user_id, user_type, action, details, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$userID, $userType, $action, $details]);
        } catch (PDOException $e) {
            error_log("logAction Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== NOTIFICATIONS ====================
    
    private function notifyNominationApproved($nominee_id, $position_name) {
        try {
            $title = 'Nomination Approved';
            $message = "Your nomination for the position of {$position_name} has been approved! You are now officially a candidate.";
            $type = 'nomination_approved';
            
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, user_type, title, message, type, is_read, created_at)
                VALUES (?, 'student', ?, ?, ?, 0, NOW())
            ");
            $stmt->execute([$nominee_id, $title, $message, $type]);
            
            return true;
        } catch (PDOException $e) {
            error_log("notifyNominationApproved Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function notifyNominationRejected($nominee_id, $position_name) {
        try {
            $title = 'Nomination Not Approved';
            $message = "Your nomination for the position of {$position_name} was not approved.";
            $type = 'nomination_rejected';
            
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, user_type, title, message, type, is_read, created_at)
                VALUES (?, 'student', ?, ?, ?, 0, NOW())
            ");
            $stmt->execute([$nominee_id, $title, $message, $type]);
            
            return true;
        } catch (PDOException $e) {
            error_log("notifyNominationRejected Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== EMAIL NOTIFICATIONS ====================
    
    private function sendEmailNominationApproved($nominee_id, $position_name) {
        try {
            // Check if email notifications are enabled
            if (!defined('ENABLE_EMAIL_NOTIFICATIONS') || !ENABLE_EMAIL_NOTIFICATIONS) {
                return false;
            }
            
            // Get nominee email
            $stmt = $this->conn->prepare("SELECT email, fullname FROM students WHERE id = ?");
            $stmt->execute([$nominee_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student || !$student['email']) {
                return false;
            }
            
            // Load Notification class for email sending
            require_once __DIR__ . '/notification.php';
            $notificationSystem = new Notification();
            
            // Send email
            return $notificationSystem->notifyNominationApproved(
                $nominee_id,
                $student['email'],
                $position_name
            );
            
        } catch (Exception $e) {
            error_log("sendEmailNominationApproved Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendEmailNominationRejected($nominee_id, $position_name) {
        try {
            // Check if email notifications are enabled
            if (!defined('ENABLE_EMAIL_NOTIFICATIONS') || !ENABLE_EMAIL_NOTIFICATIONS) {
                return false;
            }
            
            // Get nominee email
            $stmt = $this->conn->prepare("SELECT email, fullname FROM students WHERE id = ?");
            $stmt->execute([$nominee_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student || !$student['email']) {
                return false;
            }
            
            // Load Notification class for email sending
            require_once __DIR__ . '/notification.php';
            $notificationSystem = new Notification();
            
            // Send email
            return $notificationSystem->notifyNominationRejected(
                $nominee_id,
                $student['email'],
                $position_name
            );
            
        } catch (Exception $e) {
            error_log("sendEmailNominationRejected Error: " . $e->getMessage());
            return false;
        }
    }
}
?>